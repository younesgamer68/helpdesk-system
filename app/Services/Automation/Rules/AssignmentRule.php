<?php

namespace App\Services\Automation\Rules;

use App\Models\AutomationRule;
use App\Models\Team;
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
            if (! $this->matchesCategoryCondition($ticket, (int) $conditions['category_id'])) {
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

        // Use default assignment service if configured (prioritize this over specific assignment to handle UI toggle behavior)
        if (! empty($actions['assign_to_specialist'])) {
            $this->assignmentService->assignTicket($ticket);

            return;
        }

        if (! empty($actions['assign_to_team_id'])) {
            $team = Team::query()->find($actions['assign_to_team_id']);

            if ($team) {
                $this->assignmentService->assignToTeam($ticket, $team);

                return;
            }
        }

        // Assign to specific operator if configured
        if (! empty($actions['assign_to_operator_id'])) {
            $ticket->update(['assigned_to' => $actions['assign_to_operator_id']]);
        }
    }

    protected function matchesCategoryCondition(Ticket $ticket, int $conditionCategoryId): bool
    {
        if ($ticket->category_id === $conditionCategoryId) {
            return true;
        }

        return $ticket->category?->parent_id === $conditionCategoryId;
    }
}
