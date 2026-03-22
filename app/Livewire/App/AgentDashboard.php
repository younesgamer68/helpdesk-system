<?php

namespace App\Livewire\App;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketMention;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
#[Title('My Work')]
class AgentDashboard extends Component
{
    use WithPagination;

    public string $activePill = '';

    public string $search = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $categoryFilter = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public array $selectedTickets = [];

    public bool $selectAll = false;

    public array $statuses = ['open', 'in_progress', 'pending', 'resolved', 'closed'];

    public array $priorities = ['low', 'medium', 'high', 'urgent'];

    public function mount(): void
    {
        if ($this->urgentCount > 0) {
            $this->activePill = 'urgent';
        } elseif ($this->mentionCount > 0) {
            $this->activePill = 'mentions';
        } elseif ($this->needsReplyCount > 0) {
            $this->activePill = 'needs-reply';
        } elseif ($this->unassignedCount > 0) {
            $this->activePill = 'unassigned';
        } else {
            $this->activePill = 'all';
        }
    }

    public function setPill(string $pill): void
    {
        $this->activePill = $pill;
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->categoryFilter = '';
        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedTickets = $this->tickets->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedTickets = [];
        }
    }

    public function updatedSelectedTickets(): void
    {
        $this->selectAll = count($this->selectedTickets) === count($this->tickets->items());
    }

    public function setSortBy(string $column): void
    {
        $sortable = ['subject', 'status', 'priority', 'updated_at', 'created_at'];
        if (! in_array($column, $sortable)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function greeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    #[Computed]
    public function subtitle(): string
    {
        $urgentCount = $this->urgentCount;
        if ($urgentCount > 0) {
            return "You have {$urgentCount} urgent ".($urgentCount === 1 ? 'ticket' : 'tickets');
        }

        $mentionCount = $this->mentionCount;
        if ($mentionCount > 0) {
            return "You have {$mentionCount} unread ".($mentionCount === 1 ? 'mention' : 'mentions');
        }

        $pendingCount = $this->needsReplyCount;
        if ($pendingCount > 0) {
            return "{$pendingCount} ".($pendingCount === 1 ? 'client is' : 'clients are').' waiting for your reply';
        }

        return 'You are all caught up';
    }

    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return Auth::user()->company->categories()->select('id', 'name')->get();
    }

    #[Computed]
    public function urgentCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where(function ($q) {
                $q->where('priority', 'urgent')
                    ->orWhere('sla_status', 'breached');
            })
            ->count();
    }

    #[Computed]
    public function needsReplyCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function allMyCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();
    }

    #[Computed]
    public function unassignedCount(): int
    {
        $teamIds = Auth::user()->teams()->pluck('teams.id');
        if ($teamIds->isEmpty()) {
            return 0;
        }

        return Ticket::query()
            ->whereIn('team_id', $teamIds)
            ->whereNull('assigned_to')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();
    }

    #[Computed]
    public function mentionCount(): int
    {
        return TicketMention::query()
            ->where('mentioned_user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function mentionTickets(): Collection
    {
        return TicketMention::query()
            ->where('mentioned_user_id', Auth::id())
            ->whereNull('read_at')
            ->with([
                'ticket' => fn ($q) => $q->with(['customer:id,name,email', 'category:id,name']),
                'mentionedByUser:id,name',
                'reply:id,message',
            ])
            ->latest()
            ->get();
    }

    #[Computed]
    public function tickets()
    {
        $query = Ticket::query()
            ->where('company_id', Auth::user()->company_id)
            ->with(['customer:id,name,email', 'category:id,name', 'assignedTo:id,name']);

        // Base scope by pill
        match ($this->activePill) {
            'urgent' => $query->where('assigned_to', Auth::id())
                ->whereNotIn('status', ['resolved', 'closed'])
                ->where(function ($q) {
                    $q->where('priority', 'urgent')
                        ->orWhere('sla_status', 'breached');
                }),
            'needs-reply' => $query->where('assigned_to', Auth::id())
                ->where('status', 'pending'),
            'unassigned' => $query->whereIn('team_id', Auth::user()->teams()->pluck('teams.id'))
                ->whereNull('assigned_to')
                ->whereNotIn('status', ['resolved', 'closed']),
            default => $query->where('assigned_to', Auth::id())
                ->whereNotIn('status', ['resolved', 'closed']),
        };

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', '%'.$this->search.'%')
                    ->orWhere('subject', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Apply filters
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->statusFilter !== '' || $this->priorityFilter !== '' || $this->categoryFilter !== '';
    }

    public function bulkSetStatus(string $status): void
    {
        if (empty($this->selectedTickets)) {
            return;
        }

        Ticket::whereIn('id', $this->selectedTickets)
            ->where('assigned_to', Auth::id())
            ->update(['status' => $status]);

        $this->dispatch('show-toast', message: count($this->selectedTickets).' tickets updated to '.str($status)->replace('_', ' ')->title(), type: 'success');
        $this->selectedTickets = [];
        $this->selectAll = false;
    }

    public function bulkSetPriority(string $priority): void
    {
        if (empty($this->selectedTickets)) {
            return;
        }

        Ticket::whereIn('id', $this->selectedTickets)
            ->where('assigned_to', Auth::id())
            ->update(['priority' => $priority]);

        $this->dispatch('show-toast', message: count($this->selectedTickets).' tickets set to '.ucfirst($priority).' priority', type: 'success');
        $this->selectedTickets = [];
        $this->selectAll = false;
    }

    public function markMentionRead(int $mentionId): void
    {
        TicketMention::query()
            ->where('id', $mentionId)
            ->where('mentioned_user_id', Auth::id())
            ->update(['read_at' => now()]);
    }

    public function takeTicket(int $ticketId): void
    {
        $ticket = Ticket::query()
            ->whereNull('assigned_to')
            ->findOrFail($ticketId);

        $userTeamIds = Auth::user()->teams()->pluck('teams.id');
        if ($ticket->team_id === null || ! $userTeamIds->contains($ticket->team_id)) {
            $this->dispatch('show-toast', message: 'You cannot take this ticket.', type: 'error');

            return;
        }

        DB::transaction(function () use ($ticket) {
            $ticket->update([
                'assigned_to' => Auth::id(),
                'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
            ]);

            $user = Auth::user();
            $user->increment('assigned_tickets_count');
            $user->update(['last_assigned_at' => now()]);

            TicketLog::create([
                'company_id' => $ticket->company_id,
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'self_assigned',
                'description' => $user->name.' took this ticket',
            ]);
        });

        $this->dispatch('show-toast', message: "Ticket #{$ticket->ticket_number} assigned to you!", type: 'success');
    }

    public function toggleAvailability(): void
    {
        $user = Auth::user();
        $user->is_available = ! $user->is_available;
        $user->save();
    }

    public function render()
    {
        return view('livewire.tickets.agent-dashboard');
    }
}
