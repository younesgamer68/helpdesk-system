<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class ChangeStatus extends Component
{
    public Ticket $ticket;
    public function mount($ticket)
    {
        $this->ticket = $ticket;
    }
    public function changeStatus(string $status)
    {
        $this->ticket->update(["status" => $status]);
        $this->dispatch("statusChanged");
    }
    public function render()
    {
        return view('livewire.dashboard.change-status');
    }
}
