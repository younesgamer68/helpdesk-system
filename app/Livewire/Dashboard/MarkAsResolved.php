<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class MarkAsResolved extends Component
{
    public Ticket $ticket;
    public $state;
    public function mount(Ticket $ticket){
        $this->ticket = $ticket;
        $this->state = $ticket->status;
    }
    public function resolve(){
    

        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);


        $this->state = $this->ticket->status;



        session()->flash('message', 'Ticket marked as resolved!');
        $this->dispatch('statusChanged');

    }
    public function unresolve(){
        $this->ticket->update([
            'status' => 'in_progress',
            'resolved_at' => now(),
        ]);


        $this->state = $this->ticket->status;

        session()->flash('message', 'Ticket marked as resolved!');
        $this->dispatch('statusChanged');
    }
    public function render()
    {
        return view('livewire.dashboard.mark-as-resolved');
    }
}
