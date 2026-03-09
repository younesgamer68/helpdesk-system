<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use App\Services\TicketAssignmentService;

class TicketObserver
{
    public function __construct(
        protected TicketAssignmentService $assignmentService,
        protected AutomationEngine $automationEngine
    ) {}

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
