<?php

namespace App\Livewire\Dashboard;

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
            ->whereDate('updated_at', today())
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
    public function openTicketsList(): Collection
    {
        return Ticket::with(['user', 'category'])
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->take(50)
            ->get();
    }

    #[Computed]
    public function resolvedTodayList(): Collection
    {
        return Ticket::with(['user', 'category'])
            ->where('status', 'resolved')
            ->whereDate('updated_at', today())
            ->latest()
            ->get();
    }

    #[Computed]
    public function unassignedTicketsList(): Collection
    {
        return Ticket::with(['category'])
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
        return Ticket::with(['user', 'category'])
            ->latest()
            ->take(8)
            ->get();
    }

    #[Computed]
    public function agentsActivity(): Collection
    {
        $companyId = Auth::user()->company_id;

        return User::whereIn('role', ['agent', 'operator'], 'and', false)
            ->where('company_id', '=', $companyId, 'and')
            ->withCount([
                'tickets as active_count' => function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn('status', ['open', 'in_progress', 'pending']);
                },
            ])
            ->get();
    }

    #[Computed]
    public function recentActivity(): Collection
    {
        return TicketLog::with(['ticket', 'user'])
            ->latest()
            ->take(10)
            ->get();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}
