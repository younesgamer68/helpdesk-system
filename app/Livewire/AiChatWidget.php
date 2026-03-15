<?php

namespace App\Livewire;

use App\Ai\Agents\HelpdeskAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AiChatWidget extends Component
{
    public string $message = '';

    public array $messages = [];

    public bool $isTyping = false;

    public ?string $conversationId = null;

    /** @var array<int, array{id: string, title: string, date: string}> */
    public array $conversations = [];

    public bool $chatting = false;

    public function mount(): void
    {
        $this->conversationId = Session::get('chat_conversation_id');
        $this->loadConversationList();

        if ($this->conversationId) {
            $this->loadMessages();
        }

        $this->chatting = false;
    }

    public function loadConversationList(): void
    {
        $ids = Session::get('chat_conversation_ids', []);

        if (empty($ids)) {
            $this->conversations = [];

            return;
        }

        $this->conversations = DB::table('agent_conversations')
            ->whereIn('id', $ids)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conv) {
                $firstMessageContent = DB::table('agent_conversation_messages')
                    ->where('conversation_id', $conv->id)
                    ->orderBy('created_at', 'asc')
                    ->value('content');

                $preview = $firstMessageContent ? \Illuminate\Support\Str::limit((string) $firstMessageContent, 60) : 'Welcome to the Helpdesk System!';

                return [
                    'id' => $conv->id,
                    'title' => $conv->title ?: 'Conversation',
                    'preview' => $preview,
                    'date' => \Carbon\Carbon::parse($conv->created_at)->format('M j \a\t g:i A'),
                    'short_date' => \Carbon\Carbon::parse($conv->updated_at)->format('M j'),
                ];
            })
            ->all();
    }

    public function loadMessages(): void
    {
        if (! $this->conversationId) {
            return;
        }

        $history = DB::table('agent_conversation_messages')
            ->where('conversation_id', $this->conversationId)
            ->orderBy('created_at', 'asc')->get();

        $this->messages = [];
        foreach ($history as $msg) {
            $role = $msg->role === 'user' ? 'user' : 'ai';
            $this->messages[] = [
                'role' => $role,
                'content' => $msg->content,
            ];
        }

        if (empty($this->messages)) {
            $this->messages[] = [
                'role' => 'ai',
                'content' => "Welcome to the Helpdesk System! 🚀\nHow can I assist you today?",
            ];
        }
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        Session::forget('chat_conversation_id');
        $this->messages = [
            [
                'role' => 'ai',
                'content' => "Welcome to the Helpdesk System! 🚀\nHow can I assist you today?",
            ],
        ];
        $this->chatting = true;
        $this->dispatch('scroll-to-bottom');
    }

    public function selectConversation(string $id): void
    {
        $this->conversationId = $id;
        Session::put('chat_conversation_id', $id);
        Session::save();
        $this->loadMessages();
        $this->chatting = true;
        $this->dispatch('scroll-to-bottom');
    }

    public function backToHome(): void
    {
        $this->chatting = false;
        $this->loadConversationList();
    }

    public function deleteConversation(string $id): void
    {
        // Remove from database
        DB::table('agent_conversation_messages')->where('conversation_id', $id)->delete();
        DB::table('agent_conversations')->where('id', $id)->delete();

        // Remove from session
        $ids = Session::get('chat_conversation_ids', []);
        $ids = array_filter($ids, fn ($convId) => $convId !== $id);
        Session::put('chat_conversation_ids', array_values($ids));

        // If the deleted conversation is currently active, clear state
        if ($this->conversationId === $id) {
            $this->conversationId = null;
            Session::forget('chat_conversation_id');
            $this->chatting = false;
        }

        Session::save();
        $this->loadConversationList();
    }

    public function sendMessage(): void
    {
        if (trim($this->message) === '') {
            return;
        }

        $userMessage = $this->message;
        $this->message = '';

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        $this->isTyping = true;
        $this->dispatch('scroll-to-bottom');
        $this->dispatch('trigger-ai-response', message: $userMessage);
    }

    public function setQuickReply(string $text): void
    {
        $this->message = $text;
        $this->sendMessage();
    }

    #[\Livewire\Attributes\On('trigger-ai-response')]
    public function triggerAiResponse(string $message): void
    {
        try {
            $agent = new HelpdeskAgent;
            $participant = auth()->user() ?? (object) ['id' => request()->session()->getId()];

            if (! $this->conversationId) {
                // Ensure the conversation exists immediately in the DB so rate limiting
                // doesn't cause the user's initial message to disappear from history.
                $this->conversationId = (string) \Illuminate\Support\Str::uuid7();

                DB::table('agent_conversations')->insert([
                    'id' => $this->conversationId,
                    'user_id' => $participant->id ?? null,
                    'title' => \Illuminate\Support\Str::limit($message, 50),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Also manually store their initial message so it shows up in history preview
                DB::table('agent_conversation_messages')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid7(),
                    'conversation_id' => $this->conversationId,
                    'user_id' => $participant->id ?? null,
                    'agent' => HelpdeskAgent::class,
                    'role' => 'user',
                    'content' => $message,
                    'attachments' => '[]',
                    'tool_calls' => '[]',
                    'tool_results' => '[]',
                    'usage' => '[]',
                    'meta' => '[]',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Session::put('chat_conversation_id', $this->conversationId);
                $ids = Session::get('chat_conversation_ids', []);
                if (! in_array($this->conversationId, $ids)) {
                    $ids[] = $this->conversationId;
                    Session::put('chat_conversation_ids', $ids);
                }
                Session::save();
            }

            // Always use continue() now since we guarantee the conversation ID exists
            $response = $agent->continue($this->conversationId, $participant)->prompt($message);

            $this->messages[] = ['role' => 'ai', 'content' => trim($response->text)];
            $this->loadMessages();

        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();

            if (str_contains(strtolower($errorMsg), 'rate limit') || str_contains(strtolower($errorMsg), '429')) {
                $userMsg = "I'm receiving too many requests right now. Please wait a moment and try again.";
            } else {
                $userMsg = 'An internal error occurred: '.trim($errorMsg);
            }

            $this->messages[] = [
                'role' => 'ai',
                'content' => $userMsg,
            ];
        }

        $this->isTyping = false;
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.ai-chat-widget');
    }
}
