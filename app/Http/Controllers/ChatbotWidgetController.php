<?php

namespace App\Http\Controllers;

use App\Ai\Agents\HelpdeskAgent;
use App\Models\ChatbotConversation;
use App\Models\ChatbotFaq;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\KbArticle;
use App\Models\WidgetSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotWidgetController extends Controller
{
    public function snippet(Request $request, string $company, string $key)
    {
        $company = Company::query()->where('slug', $company)->firstOrFail();

        $widgetSetting = WidgetSetting::query()
            ->where('company_id', $company->id)
            ->where('widget_key', $key)
            ->firstOrFail();

        $aiSettings = CompanyAiSettings::query()
            ->where('company_id', $company->id)
            ->first();

        if (! $aiSettings || ! $aiSettings->ai_chatbot_enabled) {
            abort(404);
        }

        $protocol = config('app.env') === 'local' ? 'http' : 'https';
        $domain = config('app.domain');
        $widgetUrl = "{$protocol}://{$company->slug}.{$domain}/chatbot-widget/{$widgetSetting->widget_key}";

        $js = view('chatbot.widget-js', [
            'widgetUrl' => $widgetUrl,
        ])->render();

        $cacheControl = config('app.env') === 'local'
            ? 'no-store, no-cache, must-revalidate, max-age=0'
            : 'public, max-age=300';

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => $cacheControl,
        ]);
    }

    public function show(string $company, string $key)
    {
        $company = Company::query()->where('slug', $company)->firstOrFail();

        $widgetSetting = WidgetSetting::query()
            ->where('company_id', $company->id)
            ->where('widget_key', $key)
            ->firstOrFail();

        $aiSettings = CompanyAiSettings::query()
            ->where('company_id', $company->id)
            ->first();

        if (! $aiSettings || ! $aiSettings->ai_chatbot_enabled) {
            abort(404);
        }

        return view('chatbot.widget', [
            'company' => $company,
            'widgetSetting' => $widgetSetting,
            'aiSettings' => $aiSettings,
        ]);
    }

    public function message(Request $request, string $company, string $key): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $company = Company::query()->where('slug', $company)->firstOrFail();

        $widgetSetting = WidgetSetting::query()
            ->where('company_id', $company->id)
            ->where('widget_key', $key)
            ->firstOrFail();

        $aiSettings = CompanyAiSettings::query()
            ->where('company_id', $company->id)
            ->first();

        if (! $aiSettings || ! $aiSettings->ai_chatbot_enabled) {
            abort(404);
        }

        $faqs = ChatbotFaq::query()
            ->where('company_id', $company->id)
            ->latest()
            ->get(['question', 'answer']);

        $faqContext = $faqs->map(function ($faq): string {
            return "Q: {$faq->question}\nA: {$faq->answer}";
        })->implode("\n\n");

        $kbArticles = KbArticle::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(20)
            ->get(['title', 'body']);

        $kbContext = $kbArticles->map(function ($article): string {
            $body = strip_tags((string) $article->body);
            $body = mb_substr($body, 0, 500);

            return "Article: {$article->title}\n{$body}";
        })->implode("\n\n");

        $customerMessage = trim((string) $validated['message']);

        $prompt = implode("\n\n", [
            "Company: {$company->name}",
            "Greeting: {$aiSettings->chatbot_greeting}",
            'You are a public support chatbot. Respond in plain text only. Keep replies short: maximum 2-3 sentences.',
            'If the answer is not in context, be honest and say you can connect the customer to the ticket form.',
            'FAQ Context:',
            $faqContext !== '' ? $faqContext : 'No FAQs available.',
            'Knowledge Base Context:',
            $kbContext !== '' ? $kbContext : 'No knowledge base articles available.',
            "Customer message: {$customerMessage}",
        ]);

        try {
            $response = (new HelpdeskAgent)->prompt($prompt);
            $reply = trim((string) $response->text);
        } catch (\Throwable $exception) {
            $reply = 'I could not find that right now. I can connect you to our ticket form so a human agent can help.';
        }

        if ($reply === '') {
            $reply = 'I could not find that right now. I can connect you to our ticket form so a human agent can help.';
        }

        $sessionId = $request->header('X-Chatbot-Session', (string) Str::uuid());

        $conversation = ChatbotConversation::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('session_id', $sessionId)
            ->first();

        $sessionKey = "chatbot_unanswered_turns_{$company->id}_{$widgetSetting->widget_key}_{$sessionId}";
        $fallbackThreshold = max(1, (int) round((float) $aiSettings->chatbot_fallback_threshold));

        $previousBotMessage = null;
        if ($conversation && is_array($conversation->messages) && count($conversation->messages) > 0) {
            $conversationMessages = $conversation->messages;
            $lastMessage = end($conversationMessages);
            if (is_array($lastMessage) && ($lastMessage['role'] ?? null) === 'bot') {
                $previousBotMessage = (string) ($lastMessage['text'] ?? '');
            }
        }

        $sessionIntent = Str::lower($customerMessage);
        if ($this->isEscalationIntent($sessionIntent, $previousBotMessage)) {
            $escalationUrl = $this->buildEscalationUrl($company, $widgetSetting, $aiSettings);
            $reply = 'Absolutely. I will connect you to our support ticket form now.';

            $messages = $conversation ? $conversation->messages : [];
            $messages = is_array($messages) ? $messages : [];
            $messages[] = ['role' => 'user', 'text' => $customerMessage, 'at' => now()->toIso8601String()];
            $messages[] = ['role' => 'bot', 'text' => $reply, 'at' => now()->toIso8601String()];

            ChatbotConversation::withoutGlobalScopes()->updateOrCreate(
                ['company_id' => $company->id, 'session_id' => $sessionId],
                ['messages' => $messages, 'outcome' => 'escalated'],
            );

            session([$sessionKey => $fallbackThreshold]);

            return response()->json([
                'reply' => $reply,
                'show_ticket_form' => true,
                'escalation_url' => $escalationUrl,
                'session_id' => $sessionId,
            ]);
        }

        $looksUnanswered = $this->isUnansweredTurn($customerMessage, $reply, $faqs->pluck('question')->all());
        $unansweredTurns = $looksUnanswered ? ((int) session($sessionKey, 0) + 1) : 0;

        session([$sessionKey => $unansweredTurns]);

        $escalationUrl = null;
        if ($unansweredTurns >= $fallbackThreshold) {
            $escalationUrl = $this->buildEscalationUrl($company, $widgetSetting, $aiSettings);
        }

        $messages = $conversation ? $conversation->messages : [];
        $messages = is_array($messages) ? $messages : [];
        $messages[] = ['role' => 'user', 'text' => $customerMessage, 'at' => now()->toIso8601String()];
        $messages[] = ['role' => 'bot', 'text' => $reply, 'at' => now()->toIso8601String()];

        $outcome = $this->resolveOutcome(
            customerMessage: $customerMessage,
            reply: $reply,
            currentOutcome: is_string($conversation?->outcome) ? $conversation->outcome : null,
            shouldEscalate: $unansweredTurns >= $fallbackThreshold,
        );

        ChatbotConversation::withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'session_id' => $sessionId],
            ['messages' => $messages, 'outcome' => $outcome],
        );

        return response()->json([
            'reply' => $reply,
            'show_ticket_form' => $unansweredTurns >= $fallbackThreshold,
            'escalation_url' => $escalationUrl,
            'session_id' => $sessionId,
        ]);
    }

    private function buildEscalationUrl(Company $company, WidgetSetting $widgetSetting, CompanyAiSettings $aiSettings): string
    {
        if ($aiSettings->escalation_url_type === 'custom_url' && $aiSettings->custom_escalation_url) {
            return $aiSettings->custom_escalation_url;
        }

        $protocol = config('app.env') === 'local' ? 'http' : 'https';
        $domain = config('app.domain');

        return "{$protocol}://{$company->slug}.{$domain}/widget/{$widgetSetting->widget_key}";
    }

    private function isUnansweredTurn(string $message, string $reply, array $faqQuestions): bool
    {
        $needle = Str::lower($message);
        $matchedFaq = collect($faqQuestions)
            ->contains(fn ($question) => Str::contains(Str::lower((string) $question), $needle));

        if ($matchedFaq) {
            return false;
        }

        $fallbackSignals = [
            'not sure',
            'i do not know',
            'i don\'t know',
            'cannot find',
            'connect you to',
            'ticket form',
            'human agent',
        ];

        return Str::contains(Str::lower($reply), $fallbackSignals);
    }

    private function resolveOutcome(string $customerMessage, string $reply, ?string $currentOutcome, bool $shouldEscalate): string
    {
        if ($currentOutcome === 'escalated') {
            return 'escalated';
        }

        if ($shouldEscalate) {
            return 'escalated';
        }

        if ($this->looksResolvedTurn($customerMessage, $reply)) {
            return 'resolved';
        }

        return 'active';
    }

    private function looksResolvedTurn(string $message, string $reply): bool
    {
        $normalized = Str::lower(trim($message));

        $resolutionSignals = [
            'thanks',
            'thank you',
            'that helped',
            'that works',
            'solved',
            'resolved',
            'perfect',
            'great',
            'ok thanks',
        ];

        if (Str::contains($normalized, $resolutionSignals)) {
            return true;
        }

        $replyText = Str::lower(trim($reply));

        $botClosureSignals = [
            'glad i could help',
            'happy to help',
            'let me know if you need anything else',
        ];

        return Str::contains($replyText, $botClosureSignals);
    }

    private function isEscalationIntent(string $message, ?string $previousBotMessage = null): bool
    {
        $strongTerms = [
            'connect me',
            'human',
            'agent',
            'support ticket',
            'ticket form',
            'escalate',
        ];

        if (Str::contains($message, $strongTerms)) {
            return true;
        }

        $affirmations = ['yes', 'yeah', 'yep', 'sure', 'ok', 'okay', 'please'];
        if (! Str::contains($message, $affirmations)) {
            return false;
        }

        if (! $previousBotMessage) {
            return false;
        }

        $previous = Str::lower($previousBotMessage);

        return Str::contains($previous, ['ticket form', 'support team', 'human agent', 'connect you']);
    }
}
