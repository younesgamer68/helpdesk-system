<?php

namespace App\Services\Automation\Rules;

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Services\TicketAssignmentService;

class AssignmentRule implements RuleInterface
{
    public function __construct(protected TicketAssignmentService $assignmentService
    ) {}

    public function evaluate(AutomationRule $rule, Ticket $ticket): bool
    {
        // Don't re-assign if already assigned
        if ($ticket->assigned_to) {
            return false;
        }

        // Don't assign unverified tickets
        if (! $ticket->verified) {
            return false;
        }

        $conditions = $rule->conditions;

        // Check category condition
        if (! empty($conditions['category_id'])) {
            if ($ticket->category_id != $conditions['category_id']) {
                return false;
            }
        }

        // Check priority condition
        if (! empty($conditions['priority'])) {
            $priorities = is_array($conditions['priority'])
                ? $conditions['priority']
                : [$conditions['priority']];

            if (! in_array($ticket->priority, $priorities)) {
                return false;
            }
        }

        return true;
    }

    public function apply(AutomationRule $rule, Ticket $ticket): void
    {
        $actions = $rule->actions;

        // Use default assignment service if configured
        if (! empty($actions['assign_to_specialist'])) {
            $this->assignmentService->assignTicket($ticket);

            return;
        }

        // Assign to specific operator if configured
        if (! empty($actions['assign_to_operator_id'])) {
            $ticket->update(['assigned_to' => $actions['assign_to_operator_id']]);
        }
    }
}
