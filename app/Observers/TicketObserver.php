<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use App\Services\TicketAssignmentService;

class TicketObserver
{
    public function __construct(protected TicketAssignmentService $assignmentService, protected AutomationEngine $automationEngine
    ) {}

    /**
     * Handle the Ticket "creating" event.
     * Calculate and set the initial SLA due_time.
     */
    public function creating(Ticket $ticket): void
    {
        if (! $ticket->due_time) {
            $ticket->due_time = $this->calculateSlaDueTime($ticket);
        }
    }

    /**
     * Handle the Ticket "updating" event.
     * Recalculate SLA due_time if priority changes.
     */
    public function updating(Ticket $ticket): void
    {
        if ($ticket->isDirty('priority')) {
            $newDueTime = $this->calculateSlaDueTime($ticket);

            // Only update if the new priority gives more time, or we just want to strictly follow the new priority.
            // Typically, changing priority resets the SLA timer from now.
            $ticket->due_time = $newDueTime;

            // If the ticket was breached, but now has more time, reset the status
            if ($ticket->sla_status === 'breached' && $newDueTime && $newDueTime->isFuture()) {
                $ticket->sla_status = 'on_time';
            }
        }
    }

    /**
     * Calculate the SLA due_time based on ticket priority and company policy.
     */
    private function calculateSlaDueTime(Ticket $ticket): ?\Carbon\CarbonInterface
    {
        $policy = $ticket->company ? $ticket->company->slaPolicy : null;

        if ($policy && ! $policy->is_enabled) {
            return null; // SLA monitoring disabled
        }

        $minutes = match ($ticket->priority) {
            'urgent' => $policy ? $policy->urgent_minutes : 30,
            'high' => $policy ? $policy->high_minutes : 120, // 2 hours
            'medium' => $policy ? $policy->medium_minutes : 480, // 8 hours
            'low' => $policy ? $policy->low_minutes : 1440, // 24 hours
            default => 1440,
        };

        return now()->addMinutes($minutes);
    }

    /**
     * Handle the Ticket "created" event.
     * Process automation rules for new tickets.
     */
    public function created(Ticket $ticket): void
    {
        // Only process if ticket is verified
        if ($ticket->verified) {
            $this->automationEngine->processNewTicket($ticket);

            // Fallback: If still unassigned after automation, use default assignment
            $ticket->refresh();
            if (! $ticket->assigned_to) {
                $this->assignmentService->assignTicket($ticket);
            }
        }
    }

    /**
     * Handle the Ticket "updated" event.
     * Process automation when ticket becomes verified.
     */
    public function updated(Ticket $ticket): void
    {
        // Process automation when ticket becomes verified
        if ($ticket->wasChanged('verified') && $ticket->verified) {
            $this->automationEngine->processNewTicket($ticket);

            // Fallback: If still unassigned after automation, use default assignment
            $ticket->refresh();
            if (! $ticket->assigned_to) {
                $this->assignmentService->assignTicket($ticket);
            }
        }

        // Decrement count when ticket is resolved or closed
        if ($ticket->wasChanged('status') && in_array($ticket->status, ['resolved', 'closed'])) {
            if ($ticket->assigned_to) {
                $operator = User::find($ticket->assigned_to);
                if ($operator && $operator->assigned_tickets_count > 0) {
                    $operator->decrement('assigned_tickets_count');
                }
            }
        }

        // Increment count when ticket is reopened
        if ($ticket->wasChanged('status') && $ticket->getOriginal('status') === 'closed' && $ticket->status !== 'closed') {
            if ($ticket->assigned_to) {
                $operator = User::find($ticket->assigned_to);
                if ($operator) {
                    $operator->increment('assigned_tickets_count');
                }
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     * Decrement operator's assigned ticket count.
     */
    public function deleted(Ticket $ticket): void
    {
        if ($ticket->assigned_to && ! in_array($ticket->status, ['resolved', 'closed'])) {
            $operator = User::find($ticket->assigned_to);
            if ($operator && $operator->assigned_tickets_count > 0) {
                $operator->decrement('assigned_tickets_count');
            }
        }
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        if ($ticket->assigned_to && ! in_array($ticket->status, ['resolved', 'closed'])) {
            $operator = User::find($ticket->assigned_to);
            if ($operator) {
                $operator->increment('assigned_tickets_count');
            }
        }
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        // Same as deleted
        $this->deleted($ticket);
    }
}
