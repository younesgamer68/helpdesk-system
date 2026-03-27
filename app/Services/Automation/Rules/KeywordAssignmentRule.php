<?php

namespace App\Services\Automation\Rules;

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Models\TicketCategory;

class KeywordAssignmentRule implements RuleInterface
{
    public function evaluate(AutomationRule $rule, Ticket $ticket): bool
    {
        if ($ticket->assigned_to) {
            return false;
        }

        if (! $ticket->verified) {
            return false;
        }

        // This rule is intentionally designed for uncategorized tickets.
        if ($ticket->category_id !== null) {
            return false;
        }

        $keywords = $rule->conditions['keywords'] ?? [];

        if (! is_array($keywords) || empty($keywords)) {
            return false;
        }

        $haystack = strtolower($ticket->subject.' '.$ticket->description);

        foreach ($keywords as $keyword) {
            $needle = trim(strtolower((string) $keyword));

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function apply(AutomationRule $rule, Ticket $ticket): void
    {
        $categoryId = (int) ($rule->actions['set_category_id'] ?? 0);

        if ($categoryId <= 0) {
            return;
        }

        $category = TicketCategory::query()
            ->where('company_id', $ticket->company_id)
            ->whereKey($categoryId)
            ->first();

        if (! $category) {
            return;
        }

        $ticket->forceFill([
            'category_id' => $category->id,
        ])->saveQuietly();
    }
}
