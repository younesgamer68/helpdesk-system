<?php

namespace App\Livewire\Tickets;

use App\Ai\Agents\SupportReplyAgent;
use App\Mail\AgentRepliedToTicket;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\InternalNoteAdded;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketPriorityChanged;
use App\Notifications\TicketReassigned;
use App\Notifications\TicketStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app.sidebar')]
class TicketDetails extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public $state = '';

    public $senderId = null;

    public $keepOpen = false;

    public $aiSuggestion = '';

    public $aiTone = 'professional';

    public $showAiSuggestion = false;

    public $aiLoading = false;

    // AI Summary properties
    public $aiSummary = '';

    public $summaryLoading = false;

    public $showSummary = true;

    public $agentSearch = '';

    #[Validate('required|string|max:5000')]
    public $message = '';

    public $internalNote = '';

    #[Validate(['attachments.*' => 'nullable|file|max:10240'])] // 10MB Max for admins
    public $attachments = [];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket->load(['assignedTo:id,name,email', 'category:id,name,description', 'replies.user']);
        $this->state = $ticket->status;
    }

    #[Computed]
    public function agents(): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = User::query()
            ->where('company_id', '=', $user->company_id)
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc');

        if (! empty($this->agentSearch)) {
            $query->where('name', 'like', '%'.$this->agentSearch.'%');
        }

        return $query->get(['id', 'name', 'email']);
    }

    private function logAction($action, $description)
    {
        TicketLog::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }

    public function resolve()
    {
        if ($this->ticket->status === 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket marked as resolved!', type: 'success');

            return;
        }

        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, str_replace('_', ' ', ucfirst($oldStatus)), 'Resolved'));
        }

        $this->logAction('status_changed', 'Ticket resolved.');

        $this->dispatch('show-toast', message: 'Ticket marked as resolved!', type: 'success');
    }

    public function unresolve()
    {
        if ($this->ticket->status !== 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket reopened!', type: 'success');

            return;
        }

        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => 'open',
            'resolved_at' => null,
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, 'Resolved', 'Open'));
        }

        $this->logAction('status_changed', 'Ticket unresolved.');

        $this->dispatch('show-toast', message: 'Ticket reopened!', type: 'success');
    }

    public function assign($agentId)
    {
        $agent = $this->agents()->where('id', '=', $agentId)->first();

        if ($this->ticket->assigned_to === $agentId) {
            $this->dispatch('show-toast', message: "Ticket is already assigned to {$agent->name}!", type: 'error');

            return;
        }

        $oldAgentId = $this->ticket->assigned_to;
        $this->ticket->update(['assigned_to' => $agentId]);
        $this->ticket->refresh();
        $this->ticket->load('assignedTo');

        if ($agentId !== Auth::id()) {
            $agent->notify(new TicketAssigned($this->ticket));
        }

        if ($oldAgentId && $oldAgentId !== $agentId) {
            /** @var \App\Models\User|null $oldAgent */
            $oldAgent = User::query()->find($oldAgentId, ['id', 'name', 'email']);
            if ($oldAgent) {
                $oldAgent->notify(new TicketReassigned($this->ticket));
            }
        }

        $this->logAction('assigned', "Assigned to {$agent->name}.");

        $this->dispatch('show-toast', message: "Ticket assigned to {$agent->name}!", type: 'success');
    }

    public function changePriority($priority)
    {
        $normalizedPriority = strtolower($priority);

        if ($this->ticket->priority === $normalizedPriority) {
            $this->dispatch('show-toast', message: "Ticket is already prioritized as {$priority}!", type: 'error');

            return;
        }

        if (! in_array($normalizedPriority, ['low', 'medium', 'high', 'urgent'])) {
            $this->dispatch('show-toast', message: 'Invalid priority level!', type: 'error');

            return;
        }

        $oldPriority = $this->ticket->priority;
        $this->ticket->update(['priority' => $normalizedPriority]);
        $this->ticket->refresh();

        if ($this->ticket->assignedTo && $this->ticket->assigned_to !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketPriorityChanged($this->ticket, ucfirst($oldPriority), ucfirst($normalizedPriority)));
        }

        $this->logAction('priority_changed', 'Priority changed to '.ucfirst($normalizedPriority).'.');

        $this->dispatch('show-toast', message: 'Priority changed to '.ucfirst($normalizedPriority).'!', type: 'success');
    }

    public function changeStatus($status)
    {
        $dbStatus = str_replace(' ', '_', strtolower($status));

        if ($this->ticket->status === $dbStatus) {
            $this->dispatch('show-toast', message: "Ticket is already in '".str_replace('_', ' ', $status)."' status!", type: 'error');

            return;
        }

        $validStatuses = ['pending', 'open', 'in_progress', 'resolved', 'closed'];

        if (! in_array($dbStatus, $validStatuses)) {
            $this->dispatch('show-toast', message: 'Invalid status!', type: 'error');

            return;
        }

        $oldStatus = $this->ticket->status;
        $this->ticket->update(['status' => $dbStatus]);
        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, str_replace('_', ' ', ucfirst($oldStatus)), str_replace('_', ' ', ucfirst($dbStatus))));
        }

        $this->logAction('status_changed', 'Status changed to '.str_replace('_', ' ', ucfirst($dbStatus)).'.');

        $this->dispatch('show-toast', message: 'Status changed to '.str_replace('_', ' ', ucfirst($dbStatus)).'!', type: 'success');
    }

    public function closeTicket()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Ticket is already closed!', type: 'error');

            return;
        }

        $oldStatus = $this->ticket->status;
        $this->ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, str_replace('_', ' ', ucfirst($oldStatus)), 'Closed'));
        }

        $this->logAction('status_changed', 'Ticket closed.');

        $this->dispatch('show-toast', message: 'Ticket closed successfully!', type: 'success');
    }

    public function addReply()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Cannot reply to a closed ticket!', type: 'error');

            return;
        }

        $this->validate();

        $attachmentPaths = [];

        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                $path = $attachment->store('ticket-attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ];
            }
        }

        $userId = $this->senderId ?: Auth::id();

        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $userId,
            'customer_name' => $this->ticket->customer_name,
            'message' => clean($this->message),
            'is_internal' => false,
            'is_technician' => false,
            'attachments' => empty($attachmentPaths) ? null : $attachmentPaths,
        ]);

        // Notify customer with tracking link when agent replies to a verified ticket
        if ($this->ticket->verified && $this->ticket->verification_token && $this->ticket->customer_email) {
            $this->ticket->load('company');
            Mail::to($this->ticket->customer_email)->send(new AgentRepliedToTicket($this->ticket, $reply));
        }

        // TRIGGER 2: Agent sends a reply
        if ($this->ticket->status !== 'closed' && ! $this->keepOpen) {
            $this->ticket->update(['status' => 'in_progress']);
            $this->state = 'in_progress';
        }

        $this->reset(['message', 'attachments']);
        $this->dispatch('resetEditor');

        // Notify assigned agent about the new reply if they aren't the sender
        if ($this->ticket->assigned_to && $this->ticket->assigned_to !== Auth::id() && $this->ticket->assignedTo) {
            $this->ticket->assignedTo->notify(new \App\Notifications\ClientReplied($this->ticket));
        }

        $this->logAction('reply_added', 'Added a reply.');

        $this->dispatch('show-toast', message: 'Reply added successfully!', type: 'success');
    }

    public function startAiSuggestion()
    {
        if ($this->ticket->status === 'closed') {
            return;
        }

        $this->showAiSuggestion = true;
        $this->aiLoading = true;
        $this->aiSuggestion = '';
        $this->js('$wire.generateAiSuggestion()');
    }

    public function generateAiSuggestion()
    {
        if ($this->ticket->status === 'closed') {
            return;
        }

        $this->aiLoading = true;
        $this->showAiSuggestion = true;

        $context = "Company name: Example Helpdesk\n";
        $context .= 'Ticket category: '.($this->ticket->category->name ?? 'None')."\n";
        $context .= 'Ticket priority: '.$this->ticket->priority."\n";
        $context .= 'Customer name: '.($this->ticket->customer->name ?? 'Unknown')."\n";
        $context .= "Original ticket description:\n".$this->ticket->description."\n\n";
        $context .= "Full conversation history:\n";

        foreach ($this->ticket->replies as $reply) {
            $sender = $reply->is_admin_reply ? 'Agent' : 'Customer';
            $context .= "{$sender}: ".strip_tags($reply->message)."\n";
        }

        $context .= "\n\nPlease rewrite the suggested reply according to this tone:\n";
        switch ($this->aiTone) {
            case 'friendly':
                $context .= 'Reply in a warm, friendly and approachable tone.';
                break;
            case 'formal':
                $context .= 'Reply in a formal and official tone.';
                break;
            case 'professional':
            default:
                $context .= 'Reply in a professional and clear tone.';
                break;
        }

        try {
            $agent = new SupportReplyAgent;
            $result = $agent->prompt($context);

            $this->aiSuggestion = (string) $result;
        } catch (\Exception $e) {
            $this->aiSuggestion = 'Failed to generate suggestion: '.$e->getMessage();
        }

        $this->aiLoading = false;
    }

    public function regenerateWithTone($tone)
    {
        if ($this->ticket->status === 'closed') {
            return;
        }

        $this->aiTone = $tone;
        $this->aiLoading = true;
        // Do not clear $this->aiSuggestion here, so Alpine can fade it out.
        // We defer to let Alpine pick up the aiLoading = true state,
        // and then we generate the suggestion.
        $this->js('$wire.generateAiSuggestion()');
    }

    // AI Summary methods
    public function generateAiSummary()
    {
        $this->summaryLoading = true;

        // Load replies with user relationship to avoid lazy loading
        $replies = $this->ticket->replies()->with('user:id,name')->orderBy('created_at', 'desc')->get();
        $replyCount = $replies->count();

        $prompt = "You are a helpdesk assistant summarizing a support ticket for an agent.\n\n";
        $prompt .= "Ticket Information:\n";
        $prompt .= '- Subject: '.$this->ticket->subject."\n";
        $prompt .= '- Customer: '.($this->ticket->customer->name ?? $this->ticket->customer_name ?? 'Unknown')."\n";
        $prompt .= '- Category: '.($this->ticket->category->name ?? 'None')."\n";
        $prompt .= '- Priority: '.$this->ticket->priority."\n";
        $prompt .= '- Status: '.str_replace('_', ' ', ucfirst($this->ticket->status))."\n\n";
        $prompt .= "Original Description:\n".$this->ticket->description."\n\n";

        if ($replyCount > 0) {
            $prompt .= "Reply History (most recent first):\n";
            foreach ($replies as $reply) {
                // Safely access user without triggering lazy loading
                $senderName = null;
                if ($reply->user_id && $reply->relationLoaded('user') && $reply->user) {
                    $senderName = $reply->user->name;
                }
                $sender = $reply->is_internal ? 'Internal Note' : ($reply->user_id ? ($senderName ?? 'Agent') : ($reply->customer_name ?? 'Customer'));
                $senderType = $reply->is_internal ? 'Note' : ($reply->user_id ? 'Agent' : 'Customer');
                $prompt .= "[{$senderType}: {$sender}]: ".strip_tags($reply->message)."\n";
            }
        } else {
            $prompt .= "No replies yet.\n";
        }

        $prompt .= "\n\nPlease provide a concise summary covering exactly three points:\n";
        $prompt .= "1. Issue: What is the customer asking about? (1-2 sentences)\n";
        $prompt .= "2. Progress: What has been tried or discussed so far? (1-2 sentences)\n";
        $prompt .= "3. Next Step: What needs to happen next to resolve this ticket? (1-2 sentences)\n\n";
        $prompt .= "Format your response exactly as:\n";
        $prompt .= "Issue: <text>\n";
        $prompt .= "Progress: <text>\n";
        $prompt .= "Next Step: <text>\n";
        $prompt .= 'Do not include any preamble or extra text.';

        try {
            $agent = new SupportReplyAgent;
            $result = $agent->prompt($prompt);
            $this->aiSummary = (string) $result;
        } catch (\Exception $e) {
            $this->aiSummary = 'Issue: Unable to generate summary.\nProgress: -\nNext Step: -';
        }

        $this->summaryLoading = false;
    }

    public function toggleSummary()
    {
        $this->showSummary = ! $this->showSummary;
    }

    public function regenerateSummary()
    {
        $this->aiSummary = '';
        $this->summaryLoading = true;
        // Dispatch event to clear displayedSummary in Alpine
        $this->dispatch('clearSummaryDisplay');
        // Defer to let Alpine pick up the summaryLoading = true state,
        // and then generate the summary.
        $this->js('$wire.generateAiSummary()');
    }

    public function useAiSuggestion()
    {
        $this->dispatch('loadAiSuggestion', content: $this->aiSuggestion);
        $this->showAiSuggestion = false;
    }

    public function dismissAiSuggestion()
    {
        $this->showAiSuggestion = false;
        $this->aiSuggestion = '';
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    public function addInternalNote()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Cannot add notes to a closed ticket!', type: 'error');

            return;
        }

        $this->validate([
            'internalNote' => 'required|string|max:5000',
        ]);

        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'customer_name' => $this->ticket->customer_name,
            'message' => $this->internalNote,
            'is_internal' => true,
            'is_technician' => false,
            'attachments' => null,
        ]);

        $this->reset(['internalNote']);

        // Notify assigned agent about internal note
        if ($this->ticket->assigned_to && $this->ticket->assigned_to !== Auth::id() && $this->ticket->assignedTo) {
            $this->ticket->assignedTo->notify(new InternalNoteAdded($this->ticket));
        }

        $this->dispatch('show-toast', message: 'Internal note added successfully!', type: 'success');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.tickets.ticket-details', [
            'ticket' => $this->ticket,
            'state' => $this->state,
            'agents' => $this->agents(),
            'replies' => TicketReply::query()
                ->where('ticket_id', '=', $this->ticket->id)
                ->where('is_internal', '=', false)
                ->with('user:id,name')
                ->orderBy('created_at', 'asc')
                ->get(),
            'internal_notes' => TicketReply::query()
                ->where('ticket_id', '=', $this->ticket->id)
                ->where('is_internal', '=', true)
                ->with('user:id,name')
                ->orderBy('created_at', 'asc')
                ->get(),
            'logs' => $this->ticket->logs()
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }
}
