<?php

namespace App\Livewire\App;

use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class AgentDashboard extends Component
{
    #[Computed]
    public function openTicketsCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
    }

    #[Computed]
    public function resolvedTodayCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereDate('resolved_at', today())
            ->count();
    }

    #[Computed]
    public function pendingReplyCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function unreadNotificationsCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    #[Computed]
    public function openTicketsList(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->oldest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function resolvedTodayList(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereDate('resolved_at', today())
            ->latest('resolved_at')
            ->get();
    }

    #[Computed]
    public function pendingTicketsList(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('status', 'pending')
            ->oldest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function myTickets(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->oldest()
            ->take(8)
            ->get();
    }

    #[Computed]
    public function unassignedTickets(): Collection
    {
        return Ticket::query()
            ->where('company_id', Auth::user()->company_id)
            ->whereNull('assigned_to')
            ->where('status', '!=', 'closed')
            ->with('category:id,name')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentNotifications(): Collection
    {
        return Auth::user()->notifications()->latest()->take(5)->get();
    }

    public function assignToMe(int $ticketId): void
    {
        $ticket = Ticket::query()
            ->where('company_id', Auth::user()->company_id)
            ->whereNull('assigned_to')
            ->findOrFail($ticketId);

        $ticket->update([
            'assigned_to' => Auth::id(),
            'status' => 'in_progress',
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'description' => 'Self-assigned by '.Auth::user()->name.'.',
        ]);

        $this->dispatch('show-toast', message: "Ticket #{$ticket->ticket_number} assigned to you!", type: 'success');
    }

    public function render()
    {
        return view('livewire.app.agent-dashboard');
    }
}
