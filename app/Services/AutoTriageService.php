<?php

namespace App\Services;

use App\Ai\Agents\SupportReplyAgent;
use App\Models\AutoTriageRule;
use App\Models\CompanyAiSettings;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\Log;

class AutoTriageService
{
    private ?CompanyAiSettings $settings = null;

    public function triage(Ticket $ticket): void
    {
        $this->settings = CompanyAiSettings::query()
            ->where('company_id', $ticket->company_id)
            ->first();

        if (! $this->settings || ! $this->settings->ai_auto_triage_enabled) {
            return;
        }

        // First try keyword-based rules
        if ($this->applyKeywordRules($ticket)) {
            return;
        }

        // Then try AI-based rules
        $this->applyAiTriage($ticket);
    }

    private function applyKeywordRules(Ticket $ticket): bool
    {
        $rules = AutoTriageRule::query()
            ->withoutGlobalScopes()
            ->where('company_id', $ticket->company_id)
            ->where('type', 'keyword')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $text = strtolower($ticket->subject.' '.$ticket->description);

        foreach ($rules as $rule) {
            if (! is_array($rule->keywords) || empty($rule->keywords)) {
                continue;
            }

            foreach ($rule->keywords as $keyword) {
                if (str_contains($text, strtolower(trim($keyword)))) {
                    $this->applyRule($ticket, $rule);

                    return true;
                }
            }
        }

        return false;
    }

    private function applyAiTriage(Ticket $ticket): void
    {
        $aiRules = AutoTriageRule::query()
            ->withoutGlobalScopes()
            ->where('company_id', $ticket->company_id)
            ->where('type', 'ai')
            ->where('is_active', true)
            ->first();

        if (! $aiRules) {
            // No AI rule configured — attempt generic AI triage
            $this->applyGenericAiTriage($ticket);

            return;
        }

        $this->applyRule($ticket, $aiRules);
    }

    private function applyGenericAiTriage(Ticket $ticket): void
    {
        $categories = TicketCategory::query()
            ->where('company_id', $ticket->company_id)
            ->pluck('name', 'id')
            ->toArray();

        if (empty($categories)) {
            return;
        }

        $categoryList = implode(', ', array_values($categories));
        $priorities = 'low, medium, high, urgent';

        $prompt = "You are an AI triage assistant. Analyze the following support ticket and decide the best category and priority.\n\n";
        $prompt .= "Available categories: {$categoryList}\n";
        $prompt .= "Available priorities: {$priorities}\n\n";
        $prompt .= "Ticket subject: {$ticket->subject}\n";
        $prompt .= 'Ticket description: '.strip_tags($ticket->description)."\n\n";
        $prompt .= "Respond in exactly this format:\n";
        $prompt .= "Category: <exact category name>\n";
        $prompt .= "Priority: <low|medium|high|urgent>\n";
        $prompt .= 'Do not include any other text.';

        try {
            $agent = new SupportReplyAgent;
            $result = (string) $agent->prompt(
                $prompt,
                provider: $this->settings->resolveProvider(),
                model: $this->settings->ai_model,
            );

            $category = null;
            $priority = null;

            if (preg_match('/Category:\s*(.+)/i', $result, $match)) {
                $categoryName = trim($match[1]);
                $categoryId = array_search($categoryName, $categories);
                if ($categoryId !== false) {
                    $category = $categoryId;
                }
            }

            if (preg_match('/Priority:\s*(low|medium|high|urgent)/i', $result, $match)) {
                $priority = strtolower(trim($match[1]));
            }

            $updates = [];
            if ($category && ! $ticket->category_id) {
                $updates['category_id'] = $category;
            }
            if ($priority && $ticket->priority === 'medium') {
                $updates['priority'] = $priority;
            }

            if (! empty($updates)) {
                $ticket->updateQuietly($updates);
            }
        } catch (\Exception $e) {
            Log::warning('AI auto-triage failed: '.$e->getMessage());
        }
    }

    private function applyRule(Ticket $ticket, AutoTriageRule $rule): void
    {
        $updates = [];

        if ($rule->category_id && ! $ticket->category_id) {
            $updates['category_id'] = $rule->category_id;
        }

        if ($rule->priority) {
            $updates['priority'] = $rule->priority;
        }

        if (! empty($updates)) {
            $ticket->updateQuietly($updates);
        }
    }
}
