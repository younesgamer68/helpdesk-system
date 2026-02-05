<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class AssignedAgent extends Component
{
    public Ticket $ticket;
    public $listeners = ['agentChanged' => 'render'];
    public function mount($ticket){
        $this->ticket = $ticket;
    }
    public function render()
    {
        return view('livewire.dashboard.assigned-agent');
    }
}
