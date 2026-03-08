<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
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
     */
    protected function findSpecialist(Ticket $ticket): ?User
    {
        return User::query()
            ->where('company_id', $ticket->company_id)
            ->operators()
            ->available()
            ->withSpecialty($ticket->category_id)
            ->orderBy('assigned_tickets_count', 'asc')
            ->first();
    }

    /**
     * Find and assign the best available generalist.
     */
    protected function assignToGeneralist(Ticket $ticket): ?User
    {
        $generalist = User::query()
            ->where('company_id', $ticket->company_id)
            ->operators()
            ->available()
            ->whereNull('specialty_id')
            ->orderBy('assigned_tickets_count', 'asc')
            ->first();

        // If no generalist, try any available operator
        if (! $generalist) {
            $generalist = User::query()
                ->where('company_id', $ticket->company_id)
                ->operators()
                ->available()
                ->orderBy('assigned_tickets_count', 'asc')
                ->first();
        }

        if ($generalist) {
            $this->performAssignment($ticket, $generalist);

            return $generalist;
        }

        return null;
    }

    /**
     * Perform the actual assignment and update counters.
     */
    protected function performAssignment(Ticket $ticket, User $operator): void
    {
        DB::transaction(function () use ($ticket, $operator) {
            $ticket->update(['assigned_to' => $operator->id]);
            $operator->increment('assigned_tickets_count');
        });
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

            $ticket->update(['assigned_to' => null]);
        });
    }

    /**
     * Reassign ticket to a different operator.
     */
    public function reassignTicket(Ticket $ticket, User $newOperator): void
    {
        DB::transaction(function () use ($ticket, $newOperator) {
            // Decrement previous operator's count
            if ($ticket->assigned_to) {
                $previousOperator = User::find($ticket->assigned_to);
                if ($previousOperator && $previousOperator->assigned_tickets_count > 0) {
                    $previousOperator->decrement('assigned_tickets_count');
                }
            }

            // Assign to new operator
            $ticket->update(['assigned_to' => $newOperator->id]);
            $newOperator->increment('assigned_tickets_count');
        });
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
}
