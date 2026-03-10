<?php

namespace App\Services\Automation\Rules;

use App\Mail\EscalationNotificationMail;
use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class EscalationRule implements RuleInterface
{
    /**
     * @var array<string, int>
     */
    protected array $priorityLevels = [
        'low' => 1,
        'medium' => 2,
        'high' => 3,
        'urgent' => 4,
    ];

    public function evaluate(AutomationRule $rule, Ticket $ticket): bool
    {
        $conditions = $rule->conditions;

        // Check if ticket status matches escalation conditions
        if (!empty($conditions['status'])) {
            $statuses = is_array($conditions['status'])
                ? $conditions['status']
                : [$conditions['status']];

            if (!in_array($ticket->status, $statuses, true)) {
                return false;
            }
        }

        // Check if ticket has been idle for required duration
        $idleHours = $conditions['idle_hours'] ?? 24;
        $lastActivity = $ticket->updated_at;

        if ($lastActivity->diffInHours(now()) < $idleHours) {
            return false;
        }

        // Check category condition
        if (!empty($conditions['category_id'])) {
            if ($ticket->category_id != $conditions['category_id']) {
                return false;
            }
        }

        // Don't escalate already urgent tickets if escalate_priority is the only action
        if ($ticket->priority === 'urgent' && empty($conditions['notify_admin'])) {
            return false;
        }

        return true;
    }

    public function apply(AutomationRule $rule, Ticket $ticket): void
    {
        $actions = $rule->actions;

        // Escalate priority
        if (!empty($actions['escalate_priority'])) {
            $this->escalatePriority($ticket);
        }

        // Set specific priority
        if (!empty($actions['set_priority'])) {
            $this->setPriority($ticket, $actions['set_priority']);
        }

        // Notify admin
        if (!empty($actions['notify_admin'])) {
            $this->notifyAdmin($ticket, $rule);
        }

        // Reassign ticket
        if (!empty($actions['reassign'])) {
            $this->reassignTicket($ticket, $actions);
        }
    }

    /**
     * Find all tickets that are idle and eligible for escalation.
     *
     * @return Collection<int, Ticket>
     */
    public function findIdleTickets(AutomationRule $rule): Collection
    {
        $conditions = $rule->conditions;
        $idleHours = $conditions['idle_hours'] ?? 24;
        $statuses = $conditions['status'] ?? ['pending', 'open'];

        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        $query = Ticket::query()
            ->where('company_id', $rule->company_id)
            ->whereIn('status', $statuses)
            ->where('verified', true)
            ->where('updated_at', '<', now()->subHours($idleHours));

        if (!empty($conditions['category_id'])) {
            $query->where('category_id', $conditions['category_id']);
        }

        return $query->get();
    }

    protected function escalatePriority(Ticket $ticket): void
    {
        $currentLevel = $this->priorityLevels[$ticket->priority] ?? 1;
        $nextPriority = array_search($currentLevel + 1, $this->priorityLevels);

        if ($nextPriority !== false) {
            $ticket->priority = $nextPriority;
            $ticket->saveQuietly();
        }
    }

    protected function setPriority(Ticket $ticket, string $priority): void
    {
        if (isset($this->priorityLevels[$priority])) {
            $ticket->priority = $priority;
            $ticket->saveQuietly();
        }
    }

    protected function notifyAdmin(Ticket $ticket, AutomationRule $rule): void
    {
        $admins = User::query()
            ->where('company_id', $ticket->company_id)
            ->where('role', 'admin')
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)
                ->queue(new EscalationNotificationMail($ticket, $rule));
        }
    }

    protected function reassignTicket(Ticket $ticket, array $actions): void
    {
        if (!empty($actions['reassign_to_operator_id'])) {
            $ticket->assigned_to = $actions['reassign_to_operator_id'];
            $ticket->saveQuietly();
        }
    }
}
