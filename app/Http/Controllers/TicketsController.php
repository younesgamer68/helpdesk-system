<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    //
    // In app/Http/Controllers/TicketsController.php

    public function show($company, Ticket $ticket)
    {
        $agents = $ticket->company->user;
        return view('dashboard.tickets.show', compact('ticket', 'agents'));
    }
}
