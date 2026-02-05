<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class ChangePriority extends Component
{
    public Ticket $ticket;
    public function mount($ticket)
    {
        $this->ticket = $ticket;
    }
    public function changePriority(string $priority)
    {
        $this->ticket->update(["priority" => $priority]);
        $this->dispatch("priorityChanged");
    }
    public function render()
    {
        return view('livewire.dashboard.change-priority');
    }
}
