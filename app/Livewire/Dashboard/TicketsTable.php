<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $priorityFilter = '';
    public $assignedFilter = '';
    public $categoryFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatingAssignedFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function setSortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->assignedFilter = '';
        $this->categoryFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Ticket::where('company_id', $user->company_id)
            ->with('user')
            ->with('category');

        // Filter by assigned user for non-admins
        if (!$user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        // Search functionality
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', '%' . $this->search . '%')
                    ->orWhere('subject', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Priority filter
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        // Category filter
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        // Assigned to filter (only for admins)
        if ($this->assignedFilter && $user->isAdmin()) {
            $query->where('assigned_to', $this->assignedFilter);
        }

        // Sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $tickets = $query->paginate(15);

        // Get filter options
        $statuses = ['open', 'resolved', 'closed', 'on-hold'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $categories = $user->company->categories()->get();
        $agents = $user->isAdmin() ? $user->company->user()->get() : collect();

        return view('livewire.dashboard.tickets-table', [
            'tickets' => $tickets,
            'user' => $user,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'categories' => $categories,
            'agents' => $agents,
            'hasActiveFilters' => $this->search || $this->statusFilter || $this->priorityFilter || $this->assignedFilter || $this->categoryFilter
        ]);
    }
}
