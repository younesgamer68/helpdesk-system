<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class Priority extends Component
{
    public Ticket $ticket;

    public $listeners = ['priorityChanged'=>'$refresh'];
    public function mount($ticket){
        $this->ticket = $ticket;
    }
    public function render()
    {
        return view('livewire.dashboard.priority');
    }
}
