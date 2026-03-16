<?php

namespace App\Http\Controllers;

use App\Ai\Agents\HelpdeskAgent;
use App\Models\ChatbotFaq;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\WidgetSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotWidgetController extends Controller
{
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

        $customerMessage = trim((string) $validated['message']);

        $prompt = implode("\n\n", [
            "Company: {$company->name}",
            "Greeting: {$aiSettings->chatbot_greeting}",
            'You are a public support chatbot. Respond in plain text only. Keep replies short: maximum 2-3 sentences.',
            'If the answer is not in context, be honest and say you can connect the customer to the ticket form.',
            'FAQ Context:',
            $faqContext !== '' ? $faqContext : 'No FAQs available.',
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

        $sessionKey = "chatbot_unanswered_turns_{$company->id}_{$widgetSetting->widget_key}";
        $fallbackThreshold = max(1, (int) round((float) $aiSettings->chatbot_fallback_threshold));

        $looksUnanswered = $this->isUnansweredTurn($customerMessage, $reply, $faqs->pluck('question')->all());
        $unansweredTurns = $looksUnanswered ? ((int) session($sessionKey, 0) + 1) : 0;

        session([$sessionKey => $unansweredTurns]);

        return response()->json([
            'reply' => $reply,
            'show_ticket_form' => $unansweredTurns >= $fallbackThreshold,
        ]);
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
}
