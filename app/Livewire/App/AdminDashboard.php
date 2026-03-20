<?php

namespace App\Livewire\App;

use App\Models\TenantConfig;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminDashboard extends Component
{
    #[Computed]
    public function openTicketsCount(): int
    {
        return Ticket::whereIn('status', ['open', 'in_progress'])->count();
    }

    #[Computed]
    public function resolvedTodayCount(): int
    {
        return Ticket::where('status', 'resolved')
            ->whereDate('resolved_at', today())
            ->count();
    }

    #[Computed]
    public function unassignedTicketsCount(): int
    {
        return Ticket::whereNull('assigned_to')
            ->where('status', '!=', 'closed')
            ->count();
    }

    #[Computed]
    public function totalAgentsCount(): int
    {
        return User::whereIn('role', ['agent', 'admin', 'operator'])
            ->where('id', '!=', Auth::id())
            ->count();
    }

    #[Computed]
    public function slaBreachCount(): int
    {
        return Ticket::where('sla_status', 'breached')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();
    }

    #[Computed]
    public function openTicketsList(): Collection
    {
        return Ticket::with(['assignedTo', 'category', 'customer'])
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function resolvedTodayList(): Collection
    {
        return Ticket::with(['assignedTo', 'category', 'customer'])
            ->where('status', 'resolved')
            ->whereDate('resolved_at', today())
            ->latest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function unassignedTicketsList(): Collection
    {
        return Ticket::with(['category', 'customer'])
            ->whereNull('assigned_to')
            ->where('status', '!=', 'closed')
            ->latest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function totalAgentsList(): Collection
    {
        return User::whereIn('role', ['agent', 'admin', 'operator'])
            ->where('id', '!=', Auth::id())
            ->withCount(['tickets as open_tickets_count' => function ($query) {
                $query->whereIn('status', ['open', 'in_progress']);
            }])
            ->get();
    }

    #[Computed]
    public function recentTickets(): Collection
    {
        return Ticket::with(['assignedTo', 'category', 'customer'])
            ->latest()
            ->take(8)
            ->get();
    }

    #[Computed]
    public function agentsActivity(): Collection
    {
        return User::where('company_id', Auth::user()->company_id)
            ->whereIn('role', ['operator', 'admin'])
            ->withCount([
                'assignedTickets as active_count' => function ($query) {
                    $query->whereIn('status', ['open', 'in_progress', 'pending']);
                },
            ])
            ->orderByDesc('active_count')
            ->get();
    }

    #[Computed]
    public function maxTicketsPerAgent(): int
    {
        $config = TenantConfig::query()->where('company_id', Auth::user()->company_id)->first();

        return $config?->max_tickets_per_agent ?? 20;
    }

    #[Computed]
    public function recentActivity(): Collection
    {
        return TicketLog::whereHas('ticket')
            ->with(['ticket', 'user'])
            ->latest()
            ->take(10)
            ->get();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.tickets.admin-dashboard');
    }
}
