<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Reassignment extends Component
{
    public $agents;
    public Ticket $ticket;
    public function mount($ticket)
    {
        $this->ticket = $ticket;
        $this->agents = Auth::user()->company->user;
    }
    public function assign($agentId){
        $this->ticket->update(
            ['assigned_to'=> $agentId]
        );
        $this->dispatch('agentChanged');
    }
    public function render()
    {
        return view('livewire.dashboard.reassignment');
    }
}
