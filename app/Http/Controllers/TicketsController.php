<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class TicketsController extends Controller
{
    //
    // In app/Http/Controllers/TicketsController.php

    public function show($company, Ticket $ticket)
    {
        $ticket->loadMissing([
            'assignedTo:id,name',
            'category:id,name',
            'customer:id,name,email,phone',
            'company',
        ]);
        $agents = \App\Models\User::where('company_id', $ticket->company_id)
            ->whereIn('role', ['admin', 'operator'])
            ->whereNull('deleted_at')
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        return view('app.tickets.show', compact('ticket', 'agents'));
    }
}
