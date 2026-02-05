<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Livewire\Component;

class Status extends Component
{

    public Ticket $ticket;
    protected $listeners = ['statusChanged' => 'render'];
    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }
    public function render()
    {
        return view('livewire.dashboard.status');
    }
}
