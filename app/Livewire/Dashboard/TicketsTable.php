<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class TicketsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $priorityFilter = '';
    public $assignedFilter = '';
    public $categoryFilter = '';
    public $sortBy = 'id';
    public $sortDirection = 'desc';

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
            "company." . Auth::user()->company_id . ".categories",
            3600,
            fn() => Auth::user()->company->categories()->select('id', 'name')->get()
        );
    }

    #[Computed]
    public function agents()
    {
        if (Auth::user()->role !== 'admin') {
            return collect();
        }

        return cache()->remember(
            "company." . Auth::user()->company_id . ".agents",
            3600,
            fn() => Auth::user()->company->user()->select('id', 'name')->orderBy('name')->get()
        );
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

    #[Computed]
    public function tickets()
    {
        $user = Auth::user();

        $query = Ticket::where('company_id', $user->company_id)
            ->with(['user:id,name', 'category:id,name']);

        if ($user->role !== 'admin') {
            $query->where('assigned_to', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', '%' . $this->search . '%')
                    ->orWhere('subject', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->priorityFilter) $query->where('priority', $this->priorityFilter);
        if ($this->categoryFilter) $query->where('category_id', $this->categoryFilter);
        if ($this->assignedFilter && $user->role === 'admin') $query->where('assigned_to', $this->assignedFilter);

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate(9);
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return $this->search || $this->statusFilter || $this->priorityFilter || $this->assignedFilter || $this->categoryFilter;
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
        if (!$this->hasFormData) {
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
            'category_id'
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

        // Create the ticket
        $ticket = Ticket::create([
            'company_id' => Auth::user()->company_id,
            'ticket_number' => $ticketNumber,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to ?: null,
            'category_id' => $this->category_id ?: null,
            'verified' => true, // Auto-verify admin-created tickets
        ]);

        $this->dispatch('show-toast', message: "Ticket #{$ticketNumber} created successfully!", type: 'success');
        $this->closeCreateModal();
        $this->clearForm(); 
        unset($this->tickets);
        $this->resetPage();
    }

    private function generateTicketNumber()
    {
        do {
            // Format: TKT-YYYYMMDD-XXXX
            $ticketNumber = 'TKT-' . strtoupper(Str::random(6));
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    public function deleteTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticketNumber = $ticket->ticket_number;
        $ticket->delete();

        $this->dispatch('show-toast', message: "Ticket #{$ticketNumber} deleted successfully!", type: 'success');
    }

    public function render()
    {
        return view('livewire.dashboard.tickets-table');
    }
}
