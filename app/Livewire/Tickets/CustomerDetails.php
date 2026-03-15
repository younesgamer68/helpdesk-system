<?php

namespace App\Livewire\Tickets;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CustomerDetails extends Component
{
    public $customer;

    public $activeTab = 'tickets';

    public function mount($customer)
    {
        $this->customer = Customer::where('company_id', Auth::user()->company_id)
            ->withCount('tickets')
            ->findOrFail($customer);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[Computed]
    public function tickets()
    {
        return Ticket::where('company_id', Auth::user()->company_id)
            ->where('customer_id', $this->customer->id)
            ->with('user', 'category')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function conversations()
    {
        // Get all public replies mapping to this customer's tickets
        return TicketReply::whereHas('ticket', function ($query) {
            $query->where('company_id', Auth::user()->company_id)
                ->where('customer_id', $this->customer->id);
        })
            ->with(['ticket', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function toggleStatus()
    {
        $this->customer->update(['is_active' => ! $this->customer->is_active]);

        $status = $this->customer->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-toast', message: "Customer {$this->customer->name} has been {$status}.", type: 'success');
    }

    public function render()
    {
        return view('livewire.dashboard.customer-details');
    }
}
