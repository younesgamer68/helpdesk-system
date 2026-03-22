<?php

namespace App\Livewire\App;

use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('My Team')]
class TeamPage extends Component
{
    public string $search = '';

    #[Computed]
    public function teams()
    {
        return Auth::user()->teams()->with([
            'members' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.is_available', 'users.status', 'users.avatar', 'users.assigned_tickets_count'),
            'tickets' => fn ($q) => $q->whereNotIn('status', ['resolved', 'closed']),
        ])->get();
    }

    #[Computed]
    public function teamQueue()
    {
        $teamIds = Auth::user()->teams()->pluck('teams.id');
        if ($teamIds->isEmpty()) {
            return collect();
        }

        $query = Ticket::query()
            ->whereIn('team_id', $teamIds)
            ->whereNull('assigned_to')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with(['customer:id,name,email', 'category:id,name', 'team:id,name,color']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('subject', 'like', '%'.$this->search.'%')
                    ->orWhere('ticket_number', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');
            });
        }

        return $query->oldest()->limit(30)->get();
    }

    public function takeTicket(int $ticketId): void
    {
        $ticket = Ticket::query()
            ->whereNull('assigned_to')
            ->findOrFail($ticketId);

        $userTeamIds = Auth::user()->teams()->pluck('teams.id');
        if ($ticket->team_id === null || ! $userTeamIds->contains($ticket->team_id)) {
            $this->dispatch('show-toast', message: 'You cannot take this ticket.', type: 'error');

            return;
        }

        DB::transaction(function () use ($ticket) {
            $ticket->update([
                'assigned_to' => Auth::id(),
                'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
            ]);

            $user = Auth::user();
            $user->increment('assigned_tickets_count');
            $user->update(['last_assigned_at' => now()]);

            TicketLog::create([
                'company_id' => $ticket->company_id,
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'self_assigned',
                'description' => $user->name.' took this ticket',
            ]);
        });

        $this->dispatch('show-toast', message: "Ticket #{$ticket->ticket_number} assigned to you!", type: 'success');
    }

    public function render()
    {
        return view('livewire.app.team-page');
    }
}
