<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class AssignedAgent extends Component
{
    public Ticket $ticket;
    public $listeners = ['agentChanged' => '$refresh'];
    public function mount($ticket){
        $this->ticket = $ticket;
    }
    public function render()
    {
        return view('livewire.dashboard.assigned-agent');
    }
}
