<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TenantConfig;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketReassigned;
use App\Notifications\TicketUnassigned;
use Illuminate\Support\Facades\DB;

class TicketAssignmentService
{
    protected function getMaxTicketsPerAgent(int $companyId): int
    {
        $config = TenantConfig::query()->where('company_id', $companyId)->first();

        return $config?->max_tickets_per_agent ?? 20;
    }

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
     * Pass 1: match exact subcategory.
     * Pass 2: if ticket is a subcategory and no specialist found, match parent category.
     * Both passes use same online/available/maxLoad filters.
     */
    protected function findSpecialist(Ticket $ticket): ?User
    {
        $maxLoad = $this->getMaxTicketsPerAgent($ticket->company_id);

        $baseQuery = function (int $categoryId) use ($ticket, $maxLoad) {
            return User::query()
                ->where('company_id', $ticket->company_id)
                ->operators()
                ->available()
                ->online()
                ->where('assigned_tickets_count', '<', $maxLoad)
                ->withSpecialty($categoryId)
                ->withCount(['assignedTickets as open_category_tickets_count' => function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId)
                        ->whereNotIn('status', ['resolved', 'closed']);
                }])
                ->orderBy('open_category_tickets_count', 'asc')
                ->orderByRaw('COALESCE(last_assigned_at, ?) ASC', ['1970-01-01 00:00:00'])
                ->first();
        };

        // Pass 1: exact category match
        $specialist = $baseQuery($ticket->category_id);

        if ($specialist) {
            return $specialist;
        }

        // Pass 2: fall back to parent category when ticket is a subcategory
        $parentId = TicketCategory::query()
            ->whereKey($ticket->category_id)
            ->value('parent_id');

        if ($parentId) {
            return $baseQuery($parentId);
        }

        return null;
    }

    /**
     * Find and assign the best available generalist.
     *
     * Counts all open tickets (any category) for workload comparison.
     * Uses last_assigned_at as round-robin tiebreaker.
     */
    protected function assignToGeneralist(Ticket $ticket): ?User
    {
        $maxLoad = $this->getMaxTicketsPerAgent($ticket->company_id);

        $generalist = User::query()
            ->where('company_id', $ticket->company_id)
            ->operators()
            ->available()
            ->online()
            ->where('assigned_tickets_count', '<', $maxLoad)
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
                ->where('assigned_tickets_count', '<', $maxLoad)
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
     * Assign a ticket to the best available member within a team.
     *
     * Prefers members whose speciality matches the ticket category or parent category.
     * Falls back to any available team member, then to global assignment logic.
     */
    public function assignToTeam(Ticket $ticket, Team $team): ?User
    {
        $maxLoad = $this->getMaxTicketsPerAgent($ticket->company_id);

        $categoryIds = [];
        if ($ticket->category_id) {
            $parentId = TicketCategory::query()
                ->whereKey($ticket->category_id)
                ->value('parent_id');

            $categoryIds = array_values(array_unique(array_filter([
                $parentId,
                $ticket->category_id,
            ])));
        }

        $availableMembers = $team->members()
            ->operators()
            ->available()
            ->online()
            ->where('assigned_tickets_count', '<', $maxLoad)
            ->with('categories:id')
            ->withCount(['assignedTickets as open_tickets_count' => function ($query) {
                $query->whereNotIn('status', ['resolved', 'closed']);
            }])
            ->orderBy('open_tickets_count')
            ->orderByRaw('COALESCE(last_assigned_at, ?) ASC', ['1970-01-01 00:00:00'])
            ->get();

        if ($availableMembers->isEmpty()) {
            $ticket->forceFill(['team_id' => null])->saveQuietly();

            return $this->assignTicket($ticket);
        }

        // Prefer members with matching specialty
        if (! empty($categoryIds)) {
            $specialists = $availableMembers->filter(function (User $member) use ($categoryIds) {
                $memberCategoryIds = $member->categories->pluck('id')->all();

                if ($member->specialty_id) {
                    $memberCategoryIds[] = $member->specialty_id;
                }

                return count(array_intersect($memberCategoryIds, $categoryIds)) > 0;
            });

            if ($specialists->isNotEmpty()) {
                $operator = $specialists->first();

                $this->performAssignment($ticket, $operator, $team);

                return $operator;
            }
        }

        // Fall back to least-loaded available team member
        $operator = $availableMembers->first();

        $this->performAssignment($ticket, $operator, $team);

        return $operator;
    }

    /**
     * Perform the actual assignment and update counters.
     */
    protected function performAssignment(Ticket $ticket, User $operator, ?Team $team = null): void
    {
        DB::transaction(function () use ($ticket, $operator, $team) {
            $ticket->assigned_to = $operator->id;

            if ($team) {
                $ticket->team_id = $team->id;
            } elseif (! $ticket->team_id) {
                // Auto-resolve team if agent belongs to exactly one
                $agentTeamIds = $operator->teams()->pluck('teams.id');
                if ($agentTeamIds->count() === 1) {
                    $ticket->team_id = $agentTeamIds->first();
                }
            }

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
            $newOperator->update(['last_assigned_at' => now()]);
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
            ->with('category:id,parent_id')
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
