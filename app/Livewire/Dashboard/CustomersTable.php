<?php

namespace App\Livewire\Dashboard;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersTable extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

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
        $this->resetPage();
    }

    #[Computed]
    public function customers()
    {
        $user = Auth::user();

        $query = Customer::where('company_id', $user->company_id)
            ->withCount('tickets');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return $this->search || $this->statusFilter !== '';
    }

    public function toggleStatus($customerId)
    {
        $customer = Customer::where('company_id', Auth::user()->company_id)->findOrFail($customerId);
        $customer->update(['is_active' => !$customer->is_active]);

        $status = $customer->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-toast', message: "Customer {$customer->name} has been {$status}.", type: 'success');
    }

    public function render()
    {
        return view('livewire.dashboard.customers-table');
    }
}
