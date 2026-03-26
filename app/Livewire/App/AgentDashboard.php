<?php

namespace App\Livewire\App;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketMention;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class AgentDashboard extends Component
{
    public ?string $activeModal = null;

    public function loadModal(string $modal): void
    {
        $this->activeModal = $modal;
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
    }

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
    public function slaBreachedCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('sla_status', 'breached')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();
    }

    #[Computed]
    public function openTicketsList(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereIn('status', ['open', 'in_progress'])
            ->with('customer:id,name,email,phone')
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
            ->with('customer:id,name,email,phone')
            ->latest('resolved_at')
            ->get();
    }

    #[Computed]
    public function pendingTicketsList(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('status', 'pending')
            ->with('customer:id,name,email,phone')
            ->oldest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function slaBreachedList(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('sla_status', 'breached')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with('customer:id,name,email,phone')
            ->oldest('due_time')
            ->take(50)
            ->get();
    }

    #[Computed]
    public function myTickets(): Collection
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with('customer:id,name,email,phone')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->oldest()
            ->take(8)
            ->get();
    }

    #[Computed]
    public function unassignedTickets(): Collection
    {
        $categoryIds = Auth::user()->categories()->pluck('ticket_categories.id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return Ticket::query()
            ->whereNull('assigned_to')
            ->where('status', '!=', 'closed')
            ->whereIn('category_id', $categoryIds)
            ->with(['category:id,name', 'customer:id,name,email,phone'])
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

    #[Computed]
    public function teamTickets(): Collection
    {
        $teamIds = Auth::user()->teams()->pluck('teams.id');

        if ($teamIds->isEmpty()) {
            return collect();
        }

        return Ticket::query()
            ->whereIn('team_id', $teamIds)
            ->where('assigned_to', '!=', Auth::id())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with(['assignedTo:id,name', 'customer:id,name,email,phone'])
            ->latest('updated_at')
            ->take(5)
            ->get();
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

    public function toggleAvailability(): void
    {
        $user = Auth::user();
        $user->is_available = ! $user->is_available;
        $user->save();
    }

    #[Computed]
    public function unreadMentions(): Collection
    {
        return TicketMention::where('mentioned_user_id', Auth::id())
            ->whereNull('read_at')
            ->whereHas('ticket')
            ->with(['ticket:id,ticket_number,subject', 'mentionedByUser:id,name'])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function markMentionRead(int $mentionId): void
    {
        TicketMention::where('id', $mentionId)
            ->where('mentioned_user_id', Auth::id())
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.tickets.agent-dashboard');
    }
}
