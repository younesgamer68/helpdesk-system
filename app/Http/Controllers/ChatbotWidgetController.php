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
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\RateLimitedException;

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

        $customerMessage = trim((string) $validated['message']);
        $sessionId = $request->header('X-Chatbot-Session', (string) Str::uuid());

        $conversation = ChatbotConversation::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('session_id', $sessionId)
            ->first();

        $previousBotMessage = $this->getPreviousBotMessage($conversation);
        $sessionKey = "chatbot_unanswered_turns_{$company->id}_{$widgetSetting->widget_key}_{$sessionId}";
        $fallbackThreshold = max(1, (int) round((float) $aiSettings->chatbot_fallback_threshold));

        // 1. Escalation intent — immediate ticket form
        if ($this->isEscalationIntent(Str::lower($customerMessage), $previousBotMessage)) {
            return $this->handleEscalation(
                $company, $widgetSetting, $aiSettings, $conversation,
                $customerMessage, $sessionId, $sessionKey, $fallbackThreshold,
            );
        }

        // 2. Greeting / small-talk — respond naturally, no AI call
        if ($this->isGreeting($customerMessage)) {
            return $this->respondAndSave(
                reply: "Hello! Welcome to {$company->name} support. How can I help you today?",
                showTicketForm: false,
                company: $company,
                widgetSetting: $widgetSetting,
                aiSettings: $aiSettings,
                conversation: $conversation,
                customerMessage: $customerMessage,
                sessionId: $sessionId,
                unansweredTurns: (int) session($sessionKey, 0),
            );
        }

        // 3. Thanks / gratitude — acknowledge warmly, mark resolved
        if ($this->isThanksMessage($customerMessage)) {
            session([$sessionKey => 0]);

            return $this->respondAndSave(
                reply: "You're welcome! Feel free to ask if you need anything else.",
                showTicketForm: false,
                company: $company,
                widgetSetting: $widgetSetting,
                aiSettings: $aiSettings,
                conversation: $conversation,
                customerMessage: $customerMessage,
                sessionId: $sessionId,
                unansweredTurns: 0,
                forceOutcome: 'resolved',
            );
        }

        // 4. Support question — query AI with KB context
        return $this->handleSupportQuestion(
            $company, $widgetSetting, $aiSettings, $conversation,
            $customerMessage, $sessionId, $sessionKey, $fallbackThreshold,
        );
    }

    private function handleEscalation(
        Company $company,
        WidgetSetting $widgetSetting,
        CompanyAiSettings $aiSettings,
        ?ChatbotConversation $conversation,
        string $customerMessage,
        string $sessionId,
        string $sessionKey,
        int $fallbackThreshold,
    ): JsonResponse {
        $escalationUrl = $this->buildEscalationUrl($company, $widgetSetting, $aiSettings);
        $reply = 'Absolutely. I will connect you to our support ticket form now.';

        $messages = $this->appendMessages($conversation, $customerMessage, $reply);

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

    private function handleSupportQuestion(
        Company $company,
        WidgetSetting $widgetSetting,
        CompanyAiSettings $aiSettings,
        ?ChatbotConversation $conversation,
        string $customerMessage,
        string $sessionId,
        string $sessionKey,
        int $fallbackThreshold,
    ): JsonResponse {
        $kbArticles = KbArticle::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->get(['title', 'body', 'slug']);

        $kbContext = $kbArticles->map(function ($article) use ($company): string {
            $body = strip_tags((string) $article->body);
            $articleUrl = route('kb.public.article', [
                'company' => $company->slug,
                'article' => (string) $article->slug,
            ]);

            return "Article: {$article->title}\nURL: {$articleUrl}\n{$body}";
        })->implode("\n\n---\n\n");

        $prompt = $this->buildSupportPrompt($company, $kbContext, $customerMessage);
        $providers = $this->resolveProviderFailoverChain();

        $isUnsure = false;

        try {
            $response = (new HelpdeskAgent)->prompt($prompt, provider: $providers);
            $reply = trim((string) $response->text);

            if ($reply === '') {
                $reply = "I wasn't able to find an answer to that. Could you try describing your issue in more detail?";
                $isUnsure = true;
            }
        } catch (RateLimitedException) {
            $reply = $this->buildLocalFallbackReply($company, $customerMessage);
            $isUnsure = true;
        } catch (\Throwable) {
            $reply = 'Something went wrong on our end. Please try again shortly, or I can connect you to our support team.';
            $isUnsure = true;
        }

        $unansweredTurns = $isUnsure
            ? ((int) session($sessionKey, 0) + 1)
            : 0;

        session([$sessionKey => $unansweredTurns]);

        $showTicketForm = $unansweredTurns >= $fallbackThreshold;

        return $this->respondAndSave(
            reply: $reply,
            showTicketForm: $showTicketForm,
            company: $company,
            widgetSetting: $widgetSetting,
            aiSettings: $aiSettings,
            conversation: $conversation,
            customerMessage: $customerMessage,
            sessionId: $sessionId,
            unansweredTurns: $unansweredTurns,
        );
    }

    private function respondAndSave(
        string $reply,
        bool $showTicketForm,
        Company $company,
        WidgetSetting $widgetSetting,
        CompanyAiSettings $aiSettings,
        ?ChatbotConversation $conversation,
        string $customerMessage,
        string $sessionId,
        int $unansweredTurns,
        ?string $forceOutcome = null,
    ): JsonResponse {
        $escalationUrl = $showTicketForm
            ? $this->buildEscalationUrl($company, $widgetSetting, $aiSettings)
            : null;

        $messages = $this->appendMessages($conversation, $customerMessage, $reply);

        $outcome = $forceOutcome ?? $this->resolveOutcome(
            customerMessage: $customerMessage,
            reply: $reply,
            currentOutcome: is_string($conversation?->outcome) ? $conversation->outcome : null,
            shouldEscalate: $showTicketForm,
        );

        ChatbotConversation::withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'session_id' => $sessionId],
            ['messages' => $messages, 'outcome' => $outcome],
        );

        return response()->json([
            'reply' => $reply,
            'show_ticket_form' => $showTicketForm,
            'escalation_url' => $escalationUrl,
            'session_id' => $sessionId,
        ]);
    }

    private function buildSupportPrompt(Company $company, string $kbContext, string $message): string
    {
        return implode("\n\n", array_filter([
            "Company: {$company->name}",
            'INSTRUCTIONS:',
            "- You are the friendly support assistant for {$company->name}. Answer in plain text only, max 2-3 sentences.",
            '- Your knowledge comes EXCLUSIVELY from the Knowledge Base articles provided below.',
            '- If the Knowledge Base covers the question, answer accurately from that context. You may reference article titles.',
            '- If the question is vague or unclear, ask a specific clarifying question to understand what the customer needs.',
            '- If the Knowledge Base does NOT cover the question but it is still related to the company, say you do not have information on that specific topic and suggest they describe it differently or ask about something else.',
            "- If the question is completely UNRELATED to {$company->name} (e.g. math homework, trivia, personal questions, coding help), politely decline: say you're here to help with {$company->name} topics only.",
            '- If you reference an article, include a REAL clickable markdown link [Article Title](https://...) using the exact URL provided in the Knowledge Base context. Never invent or shorten URLs.',
            '- NEVER suggest submitting a ticket, contacting support, or speaking to a human agent.',
            '- NEVER mention your knowledge base, articles, or any limitations about your data source.',
            $kbContext !== '' ? "Knowledge Base:\n{$kbContext}" : "Knowledge Base:\n(No articles available yet.)",
            "Customer message: {$message}",
        ]));
    }

    private function isGreeting(string $message): bool
    {
        $clean = Str::lower(trim((string) preg_replace('/[^\w\s]/u', '', $message)));

        $greetings = [
            'hi', 'hello', 'hey', 'howdy', 'hiya',
            'good morning', 'good afternoon', 'good evening', 'good day',
            'sup', 'whats up', 'yo', 'greetings',
            'hi there', 'hello there', 'hey there',
        ];

        return in_array($clean, $greetings, true);
    }

    private function buildLocalFallbackReply(Company $company, string $customerMessage): string
    {
        $faqs = ChatbotFaq::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->get(['question', 'answer']);

        if ($this->isVaguePrompt($customerMessage)) {
            return 'I can help with that. Could you share a bit more detail so I can give the right answer? For example: billing, password reset, VPN access, or ticket status.';
        }

        if ($faqs->isEmpty()) {
            return "I'm experiencing high demand right now and can't fully process that request. I'd love to have one of our support agents help you directly. Would you like me to connect you with someone?";
        }

        $bestMatch = null;
        $bestScore = 0;

        foreach ($faqs as $faq) {
            $score = $this->calculateSemanticSimilarity($customerMessage, (string) $faq->question);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $faq;
            }
        }

        // If we have a decent match (>30% similarity), return it. Otherwise, escalate gracefully.
        if ($bestMatch && $bestScore > 0.30) {
            return (string) $bestMatch->answer;
        }

        return "I'm not quite sure what you're asking—could you rephrase that? Or I can connect you with one of our support agents who can better help you.";
    }

    /**
     * Calculate semantic similarity between two strings using substring matching and word overlap.
     * Returns a score between 0 and 1.
     */
    private function calculateSemanticSimilarity(string $text1, string $text2): float
    {
        $text1 = Str::lower(trim((string) preg_replace('/[^\w\s]/u', ' ', $text1)));
        $text2 = Str::lower(trim((string) preg_replace('/[^\w\s]/u', ' ', $text2)));

        if ($this->isVaguePrompt($text1)) {
            return 0.0;
        }

        $text1WordCount = Str::wordCount($text1);
        $text1Length = strlen($text1);

        // Exact substring match scores highest only when user query is meaningful.
        if (($text1WordCount >= 2 || $text1Length >= 6)
            && (str_contains($text2, $text1) || str_contains($text1, $text2))) {
            return 1.0;
        }

        // High-value keywords that indicate intent
        $billingKeywords = ['billing', 'invoice', 'payment', 'refund', 'charge', 'cost', 'price'];
        $vpnKeywords = ['vpn', 'connect', 'network', 'access'];
        $passwordKeywords = ['password', 'reset', 'login', 'forgot'];
        $ticketKeywords = ['ticket', 'submit', 'support', 'issue', 'problem'];
        $hourKeywords = ['hours', 'available', 'time', 'when', 'response'];

        $keywordGroups = [
            $billingKeywords,
            $vpnKeywords,
            $passwordKeywords,
            $ticketKeywords,
            $hourKeywords,
        ];

        // Check if high-value keywords match
        foreach ($keywordGroups as $group) {
            $text1HasKeyword = collect($group)->some(fn ($kw) => str_contains($text1, $kw));
            $text2HasKeyword = collect($group)->some(fn ($kw) => str_contains($text2, $kw));

            if ($text1HasKeyword && $text2HasKeyword) {
                return 0.85;  // Strong match on domain keywords
            }
        }

        // Extract meaningful words (3+ characters, not stopwords)
        $stopwords = ['the', 'and', 'how', 'can', 'are', 'your', 'you', 'i', 'to', 'do', 'my', 'is', 'it'];
        $words1 = collect(preg_split('/\W+/', $text1))
            ->filter(fn (?string $w) => is_string($w) && strlen($w) >= 3 && ! in_array($w, $stopwords))
            ->values();
        $words2 = collect(preg_split('/\W+/', $text2))
            ->filter(fn (?string $w) => is_string($w) && strlen($w) >= 3 && ! in_array($w, $stopwords))
            ->values();

        if ($words1->isEmpty() || $words2->isEmpty()) {
            return 0;
        }

        // Count overlapping words
        $overlap = $words1->intersect($words2)->count();

        // Use Jaccard similarity: intersection / union
        $union = $words1->merge($words2)->unique()->count();
        $similarity = $union > 0 ? $overlap / $union : 0;

        return (float) $similarity;
    }

    private function isVaguePrompt(string $message): bool
    {
        $clean = Str::lower(trim((string) preg_replace('/[^\w\s]/u', ' ', $message)));

        if ($clean === '') {
            return true;
        }

        $tokens = collect(preg_split('/\W+/', $clean))
            ->filter(fn (?string $token) => is_string($token) && $token !== '')
            ->values();

        if ($tokens->isEmpty()) {
            return true;
        }

        $genericTokens = [
            'how', 'help', 'pls', 'please', 'hey', 'yo', 'sup', 'hello', 'hi',
            'hmm', 'ok', 'okay', 'what', 'why', 'when', 'where', 'issue', 'problem',
        ];

        $meaningfulTokens = $tokens->reject(fn (string $token) => in_array($token, $genericTokens, true));

        if ($meaningfulTokens->isEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * @return array<int, Lab>|Lab
     */
    private function resolveProviderFailoverChain(): array|Lab
    {
        $providers = [];

        if (filled(config('ai.providers.gemini.key'))) {
            $providers[] = Lab::Gemini;
        }

        if (filled(config('ai.providers.openai.key'))) {
            $providers[] = Lab::OpenAI;
        }

        if (filled(config('ai.providers.groq.key'))) {
            $providers[] = Lab::Groq;
        }

        return $providers === [] ? Lab::Gemini : $providers;
    }

    private function isThanksMessage(string $message): bool
    {
        $normalized = Str::lower(trim($message));

        if (Str::wordCount($normalized) > 8) {
            return false;
        }

        $thanksPatterns = [
            'thanks',
            'thank you',
            'thx',
            'that helped',
            'that works',
            'solved',
            'much appreciated',
            'appreciate it',
        ];

        return Str::contains($normalized, $thanksPatterns);
    }

    private function getPreviousBotMessage(?ChatbotConversation $conversation): ?string
    {
        if (! $conversation || ! is_array($conversation->messages) || count($conversation->messages) === 0) {
            return null;
        }

        $messages = $conversation->messages;
        $lastMessage = end($messages);

        if (is_array($lastMessage) && ($lastMessage['role'] ?? null) === 'bot') {
            return (string) ($lastMessage['text'] ?? '');
        }

        return null;
    }

    private function appendMessages(?ChatbotConversation $conversation, string $customerMessage, string $reply): array
    {
        $messages = ($conversation && is_array($conversation->messages)) ? $conversation->messages : [];
        $messages[] = ['role' => 'user', 'text' => $customerMessage, 'at' => now()->toIso8601String()];
        $messages[] = ['role' => 'bot', 'text' => $reply, 'at' => now()->toIso8601String()];

        return $messages;
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
        $normalized = Str::lower(trim((string) preg_replace('/[^\w\s]/u', ' ', $message)));

        $directEscalationPhrases = [
            'connect me to support',
            'connect me with support',
            'connect me to the support team',
            'talk to support',
            'speak to support',
            'support team',
            'human agent',
            'live agent',
            'real person',
            'someone from support',
            'escalate this',
            'ticket form',
            'open ticket',
            'open a ticket',
            'create ticket',
            'create a ticket',
            'submit ticket',
            'submit a ticket',
            'send ticket',
            'send a ticket',
            'file a ticket',
            'raise a ticket',
        ];

        if (Str::contains($normalized, $directEscalationPhrases)) {
            return true;
        }

        // Intent combo: ticket + action verb => escalation
        $hasTicketWord = Str::contains($normalized, ['ticket', 'case']);
        $hasTicketAction = Str::contains($normalized, ['open', 'create', 'submit', 'send', 'file', 'raise']);

        if ($hasTicketWord && $hasTicketAction) {
            return true;
        }

        // Intent combo: support + human contact verb => escalation
        $hasSupportWord = Str::contains($normalized, ['support', 'agent', 'team', 'human', 'person']);
        $hasContactVerb = Str::contains($normalized, ['connect', 'talk', 'speak', 'contact', 'transfer']);

        if ($hasSupportWord && $hasContactVerb) {
            return true;
        }

        $affirmations = ['yes', 'yeah', 'yep', 'sure', 'ok', 'okay', 'please'];
        if (! Str::contains($normalized, $affirmations)) {
            return false;
        }

        if (! $previousBotMessage) {
            return false;
        }

        $previous = Str::lower($previousBotMessage);

        return Str::contains($previous, ['ticket form', 'support team', 'human agent', 'connect you']);
    }
}
