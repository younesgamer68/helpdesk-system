<?php

namespace App\Services\Automation\Rules;

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\AgentTicketAssigned;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SlaBreachRule implements RuleInterface
{
    /**
     * Determine if the SLA breach rule applies to the ticket.
     */
    public function evaluate(AutomationRule $rule, Ticket $ticket): bool
    {
        // Category check
        if ($rule->category_id && $ticket->category_id !== $rule->category_id) {
            return false;
        }

        // To reach this rule from the scheduler, it must already be breached.
        return true;
    }

    /**
     * Apply SLA breach actions: Reassign, Change Priority, Notify Admin.
     */
    public function apply(AutomationRule $rule, Ticket $ticket): void
    {
        $actions = is_array($rule->actions) ? $rule->actions : [];
        $updates = [];

        // 1. Reassign Operator
        if (! empty($actions['assign_to_operator_id']) && $ticket->assigned_to !== $actions['assign_to_operator_id']) {
            $updates['assigned_to'] = $actions['assign_to_operator_id'];
        }

        // 2. Escalate Priority or Set Priority
        if (! empty($actions['escalate_priority']) && $actions['escalate_priority']) {
            $priorities = ['low', 'medium', 'high', 'urgent'];
            $currentIndex = array_search($ticket->priority, $priorities);

            if ($currentIndex !== false && $currentIndex < count($priorities) - 1) {
                $updates['priority'] = $priorities[$currentIndex + 1];
            }
        } elseif (! empty($actions['set_priority'])) {
            if ($ticket->priority !== $actions['set_priority']) {
                $updates['priority'] = $actions['set_priority'];
            }
        }

        // Apply DB updates
        if (! empty($updates)) {
            $ticket->update($updates);
            $ticket->refresh();

            // Note: the TicketObserver will recalculate the due_time if priority is changed here.
            // But since it's already breached, its SLA shouldn't shift unless we want it to reset.
            // The observer already resets sla_status to 'on_time' if due_time changes to the future.
        }

        // 3. Notify Admins
        if (! empty($actions['notify_admin']) && $actions['notify_admin']) {
            $admins = User::where('company_id', $ticket->company_id)
                ->where('role', 'admin')
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AgentTicketAssigned($ticket));
                Log::info('Admin notified of SLA breach for ticket: '.$ticket->id);
            }
        }
    }
}
