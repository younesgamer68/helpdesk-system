<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TicketDetails extends Component
{
    public Ticket $ticket;
    public $state = '';

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket->load(['user:id,name,email', 'category:id,name,description']);
        $this->state = $ticket->status;
    }

    #[Computed]
    public function agents()
    {
        return cache()->remember(
            "company." . Auth::user()->company_id . ".agents",
            3600,
            fn() => Auth::user()->company->user()
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get()
        );
    }

    public function resolve()
    {
        if ($this->ticket->status === 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket is already resolved!', type: 'error');
            return;
        }

        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Ticket marked as resolved!', type: 'success');
    }

    public function unresolve()
    {
        if ($this->ticket->status !== 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket is not resolved!', type: 'error');
            return;
        }

        $this->ticket->update([
            'status' => 'in_progress',
            'resolved_at' => null,
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Ticket reopened!', type: 'success');
    }

    public function assign($agentId)
    {
        $agent = $this->agents()->find($agentId);

        if ($this->ticket->assigned_to === $agentId) {
            $this->dispatch('show-toast', message: "Ticket is already assigned to {$agent->name}!", type: 'error');
            return;
        }

        $this->ticket->update(['assigned_to' => $agentId]);
        $this->ticket->refresh();

        $this->dispatch('show-toast', message: "Ticket assigned to {$agent->name}!", type: 'success');
    }

    public function changePriority($priority)
    {
        $normalizedPriority = strtolower($priority);

        if ($this->ticket->priority === $normalizedPriority) {
            $this->dispatch('show-toast', message: "Ticket is already prioritized as {$priority}!", type: 'error');
            return;
        }

        if (!in_array($normalizedPriority, ['low', 'medium', 'high', 'urgent'])) {
            $this->dispatch('show-toast', message: 'Invalid priority level!', type: 'error');
            return;
        }

        $this->ticket->update(['priority' => $normalizedPriority]);
        $this->ticket->refresh();

        $this->dispatch('show-toast', message: "Priority changed to " . ucfirst($normalizedPriority) . "!", type: 'success');
    }

    public function changeStatus($status)
    {
        $dbStatus = str_replace(' ', '_', strtolower($status));

        if ($this->ticket->status === $dbStatus) {
            $this->dispatch('show-toast', message: "Ticket is already in '" . str_replace('_', ' ', $status) . "' status!", type: 'error');
            return;
        }

        $validStatuses = ['pending', 'open', 'in_progress', 'resolved', 'closed'];

        if (!in_array($dbStatus, $validStatuses)) {
            $this->dispatch('show-toast', message: 'Invalid status!', type: 'error');
            return;
        }

        $this->ticket->update(['status' => $dbStatus]);
        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: "Status changed to " . str_replace('_', ' ', ucfirst($dbStatus)) . "!", type: 'success');
    }

    public function closeTicket()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Ticket is already closed!', type: 'error');
            return;
        }

        if ($this->ticket->status !== 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket must be resolved before closing!', type: 'error');
            return;
        }

        $this->ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Ticket closed successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.dashboard.ticket-details', [
            'ticket' => $this->ticket,
            'state' => $this->state,
            'agents' => $this->agents(),
        ]);
    }
}
