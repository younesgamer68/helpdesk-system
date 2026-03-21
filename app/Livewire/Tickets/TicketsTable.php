<?php

namespace App\Livewire\Tickets;

use App\Models\SavedFilterView;
use App\Models\TenantConfig;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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

    public $dateFrom = '';

    public $dateTo = '';

    public $sortBy = 'id';

    public $sortDirection = 'desc';

    public $showDeletedOnly = false;

    public $selectedTickets = [];

    public $selectAll = false;

    public $statuses = ['open', 'in_progress', 'resolved', 'closed', 'pending'];

    public $priorities = ['low', 'medium', 'high', 'urgent'];

    // Modal state
    public $showCreateModal = false;

    public $showDiscardConfirmation = false;

    // Form fields
    public $customer_name = '';

    public $customer_email = '';

    public $customer_phone = '';

    public $subject = '';

    public $description = '';

    public $priority = 'medium';

    public $status = 'pending';

    public $assigned_to = '';

    public $category_id = '';

    public $customViewName = '';

    public $showSaveViewModal = false;

    public string $ticketView = 'mine';

    protected function rules()
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:500',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:ticket_categories,id',
        ];
    }

    protected $validationAttributes = [
        'customer_name' => 'customer name',
        'customer_email' => 'customer email',
        'customer_phone' => 'customer phone',
        'assigned_to' => 'assigned agent',
        'category_id' => 'category',
    ];

    #[Computed]
    public function categories()
    {
        return cache()->remember(
            'company.'.Auth::user()->company_id.'.categories',
            3600,
            fn () => Auth::user()->company->categories()->select('id', 'name')->get()
        );
    }

    #[Computed]
    public function agents()
    {
        if (Auth::user()->role !== 'admin') {
            return collect();
        }

        return cache()->remember(
            'company.'.Auth::user()->company_id.'.agents',
            3600,
            fn () => Auth::user()->company->user()->select('id', 'name')->orderBy('name')->get()
        );
    }

    public function refreshTickets()
    {
        unset($this->tickets);
    }

    #[Computed]
    public function savedViews()
    {
        return SavedFilterView::where('user_id', Auth::id())->orderBy('name')->get();
    }

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

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingShowDeletedOnly()
    {
        $this->resetPage();
        $this->clearFilters();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTickets = $this->tickets->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedTickets = [];
        }
    }

    public function updatedSelectedTickets()
    {
        $this->selectAll = count($this->selectedTickets) === count($this->tickets->items());
    }

    public function setSortBy($column)
    {
        $sortable = ['id', 'subject', 'status', 'priority', 'created_at', 'assigned_to'];
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

    public function setTicketView(string $view): void
    {
        if (in_array($view, ['mine', 'all'])) {
            $this->ticketView = $view;
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->assignedFilter = '';
        $this->categoryFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function applyPreset(string $preset): void
    {
        $this->clearFilters();

        if ($preset === 'unassigned_high') {
            $this->priorityFilter = 'high';
            $this->statusFilter = 'open';
            $this->assignedFilter = '';
        } elseif ($savedView = SavedFilterView::where('user_id', Auth::id())->where('id', (int) $preset)->first()) {
            foreach ($savedView->filters as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }

        $this->resetPage();
    }

    public function saveCustomView()
    {
        $this->validate([
            'customViewName' => 'required|string|max:255',
        ]);

        SavedFilterView::create([
            'user_id' => Auth::id(),
            'name' => $this->customViewName,
            'filters' => [
                'search' => $this->search,
                'statusFilter' => $this->statusFilter,
                'priorityFilter' => $this->priorityFilter,
                'assignedFilter' => $this->assignedFilter,
                'categoryFilter' => $this->categoryFilter,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
            ],
        ]);

        $this->dispatch('show-toast', message: 'View saved successfully!', type: 'success');
        $this->customViewName = '';
        $this->showSaveViewModal = false;
    }

    public function deleteSavedView($id)
    {
        SavedFilterView::where('user_id', Auth::id())->where('id', $id)->delete();
        $this->dispatch('show-toast', message: 'View removed successfully!', type: 'success');
    }

    #[Computed]
    public function tickets()
    {
        $user = Auth::user()->loadMissing('categories:id,name');

        $query = Ticket::where('company_id', $user->company_id)->where('verified', 1);

        if ($this->showDeletedOnly) {
            $query->onlyTrashed();
        }

        $query->with(['assignedTo:id,name', 'category:id,name', 'customer:id,name,email,phone']);

        // Filter for non-admin users (operators)
        if ($user->role !== 'admin') {
            if ($this->ticketView === 'all') {
                // Show tickets from operator's specialty categories that are unassigned, plus their own
                $specialtyIds = $user->categories->pluck('id')->filter()->values();
                $query->where(function ($q) use ($user, $specialtyIds) {
                    $q->where('assigned_to', $user->id);
                    if ($specialtyIds->isNotEmpty()) {
                        $q->orWhere(function ($subQ) use ($specialtyIds) {
                            $subQ->whereIn('category_id', $specialtyIds)
                                ->whereNull('assigned_to');
                        });
                    }
                });
            } else {
                // "mine" - show only tickets assigned to this operator
                $query->where('assigned_to', $user->id);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                    $q->orWhereFullText(['subject', 'description'], $this->search);
                } else {
                    $q->orWhere('subject', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                }
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }
        if ($this->assignedFilter && $user->role === 'admin') {
            $query->where('assigned_to', $this->assignedFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate(8);
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return $this->search || $this->statusFilter || $this->priorityFilter || $this->assignedFilter || $this->categoryFilter || $this->dateFrom || $this->dateTo || $this->showDeletedOnly;
    }

    #[Computed]
    public function hasFormData()
    {
        return $this->customer_name
            || $this->customer_email
            || $this->customer_phone
            || $this->subject
            || $this->description
            || $this->assigned_to
            || $this->category_id;
    }

    #[On('open-create-ticket-modal')]
    public function openCreateModal()
    {
        // Only reset if there's no existing form data
        if (! $this->hasFormData) {
            $this->priority = 'medium';
            $this->status = 'pending';
        }

        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function attemptCloseCreateModal()
    {
        // If form has data, show confirmation
        if ($this->hasFormData) {
            $this->showDiscardConfirmation = true;
        } else {
            $this->closeCreateModal();
        }
    }

    public function cancelDiscard()
    {
        $this->showDiscardConfirmation = false;
    }

    public function confirmDiscard()
    {
        $this->showDiscardConfirmation = false;
        $this->closeCreateModal();
        $this->clearForm();
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->showDiscardConfirmation = false;
        // Don't reset form data - keep it for next time
    }

    public function clearForm()
    {
        $this->reset([
            'customer_name',
            'customer_email',
            'customer_phone',
            'subject',
            'description',
            'priority',
            'status',
            'assigned_to',
            'category_id',
        ]);

        $this->priority = 'medium';
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function createTicket()
    {
        $this->validate();

        // Generate unique ticket number
        $ticketNumber = $this->generateTicketNumber();

        // Find or create the customer
        $customer = \App\Models\Customer::firstOrCreate(
            [
                'company_id' => Auth::user()->company_id,
                'email' => $this->customer_email,
            ],
            [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone ?: null,
                'is_active' => true,
            ]
        );

        // Create the ticket
        $ticket = Ticket::create([
            'company_id' => Auth::user()->company_id,
            'ticket_number' => $ticketNumber,
            'customer_id' => $customer->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to ?: null,
            'category_id' => $this->category_id ?: null,
            'verified' => true, // Auto-verify admin-created tickets
            'source' => 'agent',
        ]);

        $this->dispatch('show-toast', message: "Ticket #{$ticketNumber} created successfully!", type: 'success');
        $this->closeCreateModal();
        $this->clearForm();
        $this->refreshTickets();
        $this->resetPage();
    }

    private function generateTicketNumber()
    {
        do {
            // Format: TKT-YYYYMMDD-XXXX
            $ticketNumber = 'TKT-'.strtoupper(Str::random(6));
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    public function deleteTicket($ticketId)
    {
        if (Auth::user()->role !== 'admin') {
            $this->dispatch('show-toast', message: 'Unauthorized.', type: 'error');

            return;
        }
        $ticket = Ticket::where('company_id', Auth::user()->company_id)->findOrFail($ticketId);
        $ticketNumber = $ticket->ticket_number;
        $ticket->delete();

        $this->dispatch('show-toast', message: "Ticket #{$ticketNumber} deleted successfully!", type: 'success');

        // Remove from selected if it was there
        $this->selectedTickets = array_diff($this->selectedTickets, [$ticketId]);
    }

    public function restoreTicket($ticketId)
    {
        if (Auth::user()->role !== 'admin') {
            $this->dispatch('show-toast', message: 'Unauthorized.', type: 'error');

            return;
        }
        $ticket = Ticket::withTrashed()->where('company_id', Auth::user()->company_id)->findOrFail($ticketId);
        $ticketNumber = $ticket->ticket_number;
        $ticket->restore();

        $this->dispatch('show-toast', message: "Ticket #{$ticketNumber} restored successfully!", type: 'success');
        $this->refreshTickets();
    }

    public function deleteSelectedTickets()
    {
        if (Auth::user()->role !== 'admin') {
            $this->dispatch('show-toast', message: 'Unauthorized.', type: 'error');

            return;
        }
        if (empty($this->selectedTickets)) {
            return;
        }

        $count = count($this->selectedTickets);
        Ticket::whereIn('id', $this->selectedTickets)->where('company_id', Auth::user()->company_id)->delete();

        $this->dispatch('show-toast', message: "{$count} tickets deleted successfully!", type: 'success');

        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function bulkSetStatus(string $status): void
    {
        if (Auth::user()->role !== 'admin') {
            $this->dispatch('show-toast', message: 'Unauthorized.', type: 'error');

            return;
        }
        if (empty($this->selectedTickets)) {
            return;
        }

        Ticket::whereIn('id', $this->selectedTickets)->where('company_id', Auth::user()->company_id)->update(['status' => $status]);

        $this->dispatch('show-toast', message: count($this->selectedTickets).' tickets updated to '.str($status)->replace('_', ' ')->title(), type: 'success');
        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->refreshTickets();
    }

    public function bulkSetPriority(string $priority): void
    {
        if (Auth::user()->role !== 'admin') {
            $this->dispatch('show-toast', message: 'Unauthorized.', type: 'error');

            return;
        }
        if (empty($this->selectedTickets)) {
            return;
        }

        Ticket::whereIn('id', $this->selectedTickets)->where('company_id', Auth::user()->company_id)->update(['priority' => $priority]);

        $this->dispatch('show-toast', message: count($this->selectedTickets).' tickets set to '.ucfirst($priority).' priority', type: 'success');
        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->refreshTickets();
    }

    public function bulkAssignAgent(int $agentId): void
    {
        if (Auth::user()->role !== 'admin') {
            $this->dispatch('show-toast', message: 'Unauthorized.', type: 'error');

            return;
        }

        $agent = \App\Models\User::where('id', $agentId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        if (empty($this->selectedTickets)) {
            return;
        }

        $config = TenantConfig::query()->where('company_id', Auth::user()->company_id)->first();
        $maxLoad = $config?->max_tickets_per_agent ?? 20;

        // Count tickets already assigned to this agent (exclude tickets being reassigned from this agent)
        $currentLoad = Ticket::where('assigned_to', $agentId)
            ->where('company_id', Auth::user()->company_id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // Tickets in selection that are NOT already assigned to this agent
        $newTicketsCount = Ticket::whereIn('id', $this->selectedTickets)
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) use ($agentId) {
                $query->whereNull('assigned_to')
                    ->orWhere('assigned_to', '!=', $agentId);
            })
            ->count();

        if (($currentLoad + $newTicketsCount) > $maxLoad) {
            $available = max(0, $maxLoad - $currentLoad);
            $this->dispatch('show-toast', message: "Agent {$agent->name} is at max load ({$currentLoad}/{$maxLoad} tickets). Only {$available} more can be assigned.", type: 'error');

            return;
        }

        Ticket::whereIn('id', $this->selectedTickets)->where('company_id', Auth::user()->company_id)->update(['assigned_to' => $agentId]);

        $this->dispatch('show-toast', message: count($this->selectedTickets).' tickets assigned successfully', type: 'success');
        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->refreshTickets();
    }

    public function render()
    {
        return view('livewire.tickets.tickets-table');
    }
}
