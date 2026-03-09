<?php

namespace App\Services\Automation\Rules;

use App\Mail\AutoReplyMail;
use App\Models\AutomationRule;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;

class AutoReplyRule implements RuleInterface
{
    public function evaluate(AutomationRule $rule, Ticket $ticket): bool
    {
        // Only send auto-reply for verified tickets
        if (! $ticket->verified) {
            return false;
        }

        $conditions = $rule->conditions;

        // Check if auto-reply should be sent on ticket creation
        if (! empty($conditions['on_create']) && $conditions['on_create'] === true) {
            // Check if ticket was just created (within last minute)
            if ($ticket->created_at->diffInMinutes(now()) > 1) {
                return false;
            }
        }

        // Check category condition
        if (! empty($conditions['category_id'])) {
            if ($ticket->category_id !== $conditions['category_id']) {
                return false;
            }
        }

        // Check priority condition
        if (! empty($conditions['priority'])) {
            $priorities = is_array($conditions['priority'])
                ? $conditions['priority']
                : [$conditions['priority']];

            if (! in_array($ticket->priority, $priorities, true)) {
                return false;
            }
        }

        return true;
    }

    public function apply(AutomationRule $rule, Ticket $ticket): void
    {
        $actions = $rule->actions;

        if (empty($actions['send_email']) || $actions['send_email'] !== true) {
            return;
        }

        $message = $actions['message'] ?? 'Thank you for your ticket. Our team will respond shortly.';
        $subject = $actions['subject'] ?? 'Re: '.$ticket->subject;

        Mail::to($ticket->customer_email)
            ->queue(new AutoReplyMail($ticket, $subject, $message));
    }
}
