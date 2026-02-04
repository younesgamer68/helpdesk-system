<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsTable extends Component
{
    use WithPagination;

    public function render()
    {
        $user = Auth::user();
        $tickets = [];

        if ($user->isAdmin()) {
            $tickets = Ticket::where('company_id', $user->company_id)->with('user')->with('category')->paginate(15);
        } else {
            $tickets = Ticket::where('company_id', $user->company_id)->where('assigned_to', $user->id)->with('user')->with('category')->paginate(15);
        }
        return view('livewire.dashboard.tickets-table', ['tickets' => $tickets, 'user' => Auth::user()]);
    }
}
