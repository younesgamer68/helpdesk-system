<?php

namespace App\Services\Automation\Rules;

use App\Models\AutomationRule;
use App\Models\Ticket;
use Illuminate\Support\Str;

class PriorityRule implements RuleInterface
{
    public function evaluate(AutomationRule $rule, Ticket $ticket): bool
    {
        $conditions = $rule->conditions;

        // Check for keywords in subject or description
        if (! empty($conditions['keywords'])) {
            $content = Str::lower($ticket->subject.' '.$ticket->description);
            $keywords = array_map('strtolower', $conditions['keywords']);

            $found = false;
            foreach ($keywords as $keyword) {
                if (Str::contains($content, $keyword)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                return false;
            }
        }

        // Check category condition
        if (! empty($conditions['category_id'])) {
            if ($ticket->category_id !== $conditions['category_id']) {
                return false;
            }
        }

        // Check current priority condition (only apply if priority is lower)
        if (! empty($conditions['current_priority'])) {
            $priorities = is_array($conditions['current_priority'])
                ? $conditions['current_priority']
                : [$conditions['current_priority']];

            if (! in_array($ticket->priority, $priorities, true)) {
                return false;
            }
        }

        return true;
    }

    public function apply(AutomationRule $rule, Ticket $ticket): void
    {
        $actions = $rule->actions;

        if (! empty($actions['set_priority'])) {
            $newPriority = $actions['set_priority'];
            $validPriorities = ['low', 'medium', 'high', 'urgent'];

            if (in_array($newPriority, $validPriorities, true)) {
                $ticket->priority = $newPriority;
                $ticket->saveQuietly();
            }
        }
    }
}
