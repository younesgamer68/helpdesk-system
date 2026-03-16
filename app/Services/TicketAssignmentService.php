<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketReassigned;
use App\Notifications\TicketUnassigned;
use Illuminate\Support\Facades\DB;

class TicketAssignmentService
{
    /**
     * Automatically assign a ticket to the best available technician.
     *
     * Priority:
     * 1. Available operator with matching specialty and lowest workload
     * 2. Available operator with no specialty (generalist) and lowest workload
     * 3. If no one is available, leave unassigned
     */
    public function assignTicket(Ticket $ticket): ?User
    {
        if (! $ticket->category_id) {
            return $this->assignToGeneralist($ticket);
        }

        // First, try to find a specialist matching the ticket category
        $specialist = $this->findSpecialist($ticket);

        if ($specialist) {
            $this->performAssignment($ticket, $specialist);

            return $specialist;
        }

        // Fall back to a generalist (operator without specialty)
        return $this->assignToGeneralist($ticket);
    }

    /**
     * Find the best available specialist for a ticket's category.
     *
     * Counts only open tickets in the same category for workload comparison.
     * Uses last_assigned_at as round-robin tiebreaker when counts are equal.
     */
    protected function findSpecialist(Ticket $ticket): ?User
    {
        return User::query()
            ->where('company_id', $ticket->company_id)
            ->operators()
            ->available()
            ->online()
            ->where('assigned_tickets_count', '<', 10)
            ->withSpecialty($ticket->category_id)
            ->withCount(['assignedTickets as open_category_tickets_count' => function ($query) use ($ticket) {
                $query->where('category_id', $ticket->category_id)
                    ->whereNotIn('status', ['resolved', 'closed']);
            }])
            ->orderBy('open_category_tickets_count', 'asc')
            ->orderByRaw('COALESCE(last_assigned_at, ?) ASC', ['1970-01-01 00:00:00'])
            ->first();
    }

    /**
     * Find and assign the best available generalist.
     *
     * Counts all open tickets (any category) for workload comparison.
     * Uses last_assigned_at as round-robin tiebreaker.
     */
    protected function assignToGeneralist(Ticket $ticket): ?User
    {
        $generalist = User::query()
            ->where('company_id', $ticket->company_id)
            ->operators()
            ->available()
            ->online()
            ->where('assigned_tickets_count', '<', 10)
            ->whereDoesntHave('categories')
            ->withCount(['assignedTickets as open_tickets_count' => function ($query) {
                $query->whereNotIn('status', ['resolved', 'closed']);
            }])
            ->orderBy('open_tickets_count', 'asc')
            ->orderByRaw('COALESCE(last_assigned_at, ?) ASC', ['1970-01-01 00:00:00'])
            ->first();

        // If no generalist, try any available operator
        if (! $generalist) {
            $generalist = User::query()
                ->where('company_id', $ticket->company_id)
                ->operators()
                ->available()
                ->online()
                ->where('assigned_tickets_count', '<', 10)
                ->withCount(['assignedTickets as open_tickets_count' => function ($query) {
                    $query->whereNotIn('status', ['resolved', 'closed']);
                }])
                ->orderBy('open_tickets_count', 'asc')
                ->orderByRaw('COALESCE(last_assigned_at, ?) ASC', ['1970-01-01 00:00:00'])
                ->first();
        }

        if ($generalist) {
            $this->performAssignment($ticket, $generalist);

            return $generalist;
        }

        // Notify admins that auto-assignment failed
        $admins = User::where('company_id', $ticket->company_id)
            ->whereIn('role', ['admin', 'super_admin'])
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new TicketUnassigned($ticket));
        }

        return null;
    }

    /**
     * Perform the actual assignment and update counters.
     */
    protected function performAssignment(Ticket $ticket, User $operator): void
    {
        DB::transaction(function () use ($ticket, $operator) {
            $ticket->assigned_to = $operator->id;
            $ticket->saveQuietly();
            $operator->increment('assigned_tickets_count');
            $operator->update(['last_assigned_at' => now()]);
        });

        $operator->notify(new TicketAssigned($ticket));
    }

    /**
     * Unassign a ticket and decrement the operator's counter.
     */
    public function unassignTicket(Ticket $ticket): void
    {
        if (! $ticket->assigned_to) {
            return;
        }

        DB::transaction(function () use ($ticket) {
            $previousOperator = User::find($ticket->assigned_to);

            if ($previousOperator && $previousOperator->assigned_tickets_count > 0) {
                $previousOperator->decrement('assigned_tickets_count');
            }

            $ticket->assigned_to = null;
            $ticket->saveQuietly();
        });
    }

    /**
     * Reassign ticket to a different operator.
     */
    public function reassignTicket(Ticket $ticket, User $newOperator): void
    {
        $previousOperatorId = $ticket->assigned_to;
        DB::transaction(function () use ($ticket, $newOperator) {
            // Decrement previous operator's count
            if ($ticket->assigned_to) {
                $previousOperator = User::find($ticket->assigned_to);
                if ($previousOperator && $previousOperator->assigned_tickets_count > 0) {
                    $previousOperator->decrement('assigned_tickets_count');
                }
            }

            // Assign to new operator
            $ticket->assigned_to = $newOperator->id;
            $ticket->saveQuietly();
            $newOperator->increment('assigned_tickets_count');
        });

        if ($previousOperatorId && $previousOperatorId !== $newOperator->id) {
            $previousOperator = User::find($previousOperatorId);
            if ($previousOperator) {
                $previousOperator->notify(new TicketReassigned($ticket));
            }
        }

        $newOperator->notify(new TicketAssigned($ticket));
    }

    /**
     * Recalculate assigned tickets count for all operators in a company.
     * Useful for syncing counts after data migration.
     */
    public function recalculateCounts(int $companyId): void
    {
        User::where('company_id', $companyId)
            ->operators()
            ->each(function (User $operator) {
                $count = Ticket::where('assigned_to', $operator->id)
                    ->whereNotIn('status', ['resolved', 'closed'])
                    ->count();

                $operator->update(['assigned_tickets_count' => $count]);
            });
    }

    /**
     * Assign all pending unassigned tickets for a company.
     * Called when an operator comes online to pick up queued tickets.
     */
    public function assignPendingTickets(int $companyId): int
    {
        $unassignedTickets = Ticket::where('company_id', $companyId)
            ->whereNull('assigned_to')
            ->where('verified', true)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderBy('created_at', 'asc')
            ->get();

        $assignedCount = 0;

        foreach ($unassignedTickets as $ticket) {
            $operator = $this->assignTicket($ticket);

            if ($operator) {
                $assignedCount++;
            } else {
                // No more online operators available, stop trying
                break;
            }
        }

        return $assignedCount;
    }
}
