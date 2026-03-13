<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsAnalytics extends Component
{
    use WithPagination;

    public string $activeTab = 'overview';

    public string $datePreset = 'this_week';

    public string $startDate;

    public string $endDate;

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterPriority = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterAgent = '';

    public string $ticketSearch = '';

    public string $ticketSortBy = 'created_at';

    public string $ticketSortDir = 'desc';

    public string $agentSortColumn = 'resolution_rate';

    public string $agentSortDirection = 'desc';

    public ?int $selectedAgentId = null;

    public ?int $expandedCategoryId = null;

    public function mount(): void
    {
        $this->applyPreset('this_week');
    }

    public function applyPreset(string $preset): void
    {
        $this->datePreset = $preset;
        $now = Carbon::now();

        $this->startDate = match ($preset) {
            'today' => $now->copy()->startOfDay()->format('Y-m-d'),
            'this_week' => $now->copy()->startOfWeek()->format('Y-m-d'),
            'this_month' => $now->copy()->startOfMonth()->format('Y-m-d'),
            'last_3_months' => $now->copy()->subMonths(3)->startOfDay()->format('Y-m-d'),
            default => $this->startDate ?? $now->copy()->startOfWeek()->format('Y-m-d'),
        };

        $this->endDate = match ($preset) {
            'today' => $now->copy()->endOfDay()->format('Y-m-d'),
            'this_week' => $now->copy()->endOfWeek()->format('Y-m-d'),
            'this_month' => $now->copy()->endOfMonth()->format('Y-m-d'),
            'last_3_months' => $now->copy()->format('Y-m-d'),
            default => $this->endDate ?? $now->copy()->format('Y-m-d'),
        };

        $this->resetPage();
    }

    public function updatedDatePreset(): void
    {
        if ($this->datePreset !== 'custom') {
            $this->applyPreset($this->datePreset);
        }
    }

    public function updatedStartDate(): void
    {
        $this->datePreset = 'custom';
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->datePreset = 'custom';
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function applyChartFilter(string $type, string $value): void
    {
        match ($type) {
            'status' => $this->filterStatus = $value,
            'priority' => $this->filterPriority = $value,
            'category' => $this->filterCategory = $value,
        };
        $this->activeTab = 'tickets';
        $this->resetPage();
    }

    public function selectAgent(?int $agentId): void
    {
        $this->selectedAgentId = $agentId;
    }

    public function goToAgentTab(int $agentId): void
    {
        $this->selectedAgentId = $agentId;
        $this->activeTab = 'agents';
    }

    public function toggleCategory(int $categoryId): void
    {
        $this->expandedCategoryId = $this->expandedCategoryId === $categoryId ? null : $categoryId;
    }

    public function setTicketSort(string $column): void
    {
        if ($this->ticketSortBy === $column) {
            $this->ticketSortDir = $this->ticketSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ticketSortBy = $column;
            $this->ticketSortDir = 'asc';
        }
    }

    public function setAgentSort(string $column): void
    {
        if ($this->agentSortColumn === $column) {
            $this->agentSortDirection = $this->agentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->agentSortColumn = $column;
            $this->agentSortDirection = 'asc';
        }
    }

    public function clearTicketFilters(): void
    {
        $this->filterStatus = '';
        $this->filterPriority = '';
        $this->filterCategory = '';
        $this->filterAgent = '';
        $this->ticketSearch = '';
        $this->resetPage();
    }

    public function priorityBadgeClasses(?string $priority): string
    {
        return match ($priority ?? '') {
            'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
            'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
            'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
            default => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
        };
    }

    public function statusBadgeClasses(?string $status): string
    {
        return match ($status ?? '') {
            'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'in_progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
            'resolved' => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
            'closed' => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
            default => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
        };
    }

    public function updatingTicketSearch(): void
    {
        $this->resetPage();
    }

    protected function companyId(): int
    {
        return Auth::user()->company_id;
    }

    protected function baseQuery(): Builder
    {
        return Ticket::where('company_id', $this->companyId())
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate);
    }

    protected function previousPeriodDates(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $days = $start->diffInDays($end) + 1;

        return [
            $start->copy()->subDays($days)->format('Y-m-d'),
            $end->copy()->subDays($days)->format('Y-m-d'),
        ];
    }

    protected function previousQuery(): Builder
    {
        [$prevStart, $prevEnd] = $this->previousPeriodDates();

        return Ticket::where('company_id', $this->companyId())
            ->whereDate('created_at', '>=', $prevStart)
            ->whereDate('created_at', '<=', $prevEnd);
    }

    #[Computed]
    public function totalTickets(): int
    {
        return $this->baseQuery()->count();
    }

    #[Computed]
    public function totalTicketsPrev(): int
    {
        return $this->previousQuery()->count();
    }

    #[Computed]
    public function resolvedCount(): int
    {
        return $this->baseQuery()->where('status', 'resolved')->count();
    }

    #[Computed]
    public function resolvedCountPrev(): int
    {
        return $this->previousQuery()->where('status', 'resolved')->count();
    }

    #[Computed]
    public function openCount(): int
    {
        return $this->baseQuery()->whereIn('status', ['open', 'in_progress', 'pending'])->count();
    }

    #[Computed]
    public function openCountPrev(): int
    {
        return $this->previousQuery()->whereIn('status', ['open', 'in_progress', 'pending'])->count();
    }

    #[Computed]
    public function avgFirstResponseMinutes(): ?float
    {
        $ticketIds = $this->baseQuery()->pluck('id');
        if ($ticketIds->isEmpty()) {
            return null;
        }

        $firstReplies = TicketReply::whereIn('ticket_id', $ticketIds)
            ->where('is_technician', true)
            ->orderBy('ticket_id')
            ->orderBy('created_at')
            ->get()
            ->unique('ticket_id');

        if ($firstReplies->isEmpty()) {
            return null;
        }

        $tickets = Ticket::whereIn('id', $firstReplies->pluck('ticket_id'))->get()->keyBy('id');
        $sum = 0;

        foreach ($firstReplies as $reply) {
            $ticket = $tickets->get($reply->ticket_id);
            if ($ticket) {
                $sum += Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($reply->created_at));
            }
        }

        return round($sum / $firstReplies->count(), 1);
    }

    #[Computed]
    public function resolutionRate(): ?float
    {
        $total = $this->totalTickets;
        if ($total === 0) {
            return null;
        }

        return round(($this->resolvedCount / $total) * 100, 1);
    }

    #[Computed]
    public function resolutionRatePrev(): ?float
    {
        $total = $this->totalTicketsPrev;
        if ($total === 0) {
            return null;
        }

        return round(($this->resolvedCountPrev / $total) * 100, 1);
    }

    #[Computed]
    public function ticketVolumeChart(): array
    {
        $labels = [];
        $created = [];
        $resolved = [];
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $cid = $this->companyId();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $date = $d->format('Y-m-d');
            $labels[] = $d->format('M j');
            $created[] = Ticket::where('company_id', $cid)->whereDate('created_at', $date)->count();
            $resolved[] = Ticket::where('company_id', $cid)->whereDate('resolved_at', $date)->count();
        }

        return compact('labels', 'created', 'resolved');
    }

    #[Computed]
    public function statusBreakdown(): array
    {
        $statuses = ['open', 'in_progress', 'pending', 'resolved', 'closed'];
        $cid = $this->companyId();
        $values = [];

        foreach ($statuses as $s) {
            $values[] = Ticket::where('company_id', $cid)
                ->where('status', $s)
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->count();
        }

        return [
            'labels' => ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'],
            'keys' => $statuses,
            'values' => $values,
            'colors' => ['#3b82f6', '#a855f7', '#eab308', '#14b8a6', '#71717a'],
        ];
    }

    #[Computed]
    public function priorityBreakdown(): array
    {
        $priorities = ['urgent', 'high', 'medium', 'low'];
        $values = [];

        foreach ($priorities as $p) {
            $values[] = $this->baseQuery()->where('priority', $p)->count();
        }

        return [
            'labels' => ['Urgent', 'High', 'Medium', 'Low'],
            'keys' => $priorities,
            'values' => $values,
            'colors' => ['#ef4444', '#f97316', '#3b82f6', '#22c55e'],
        ];
    }

    #[Computed]
    public function categoryVolume(): array
    {
        $categories = TicketCategory::where('company_id', $this->companyId())->orderBy('name')->get();
        $labels = [];
        $keys = [];
        $values = [];

        foreach ($categories as $cat) {
            $count = $this->baseQuery()->where('category_id', $cat->id)->count();
            $labels[] = $cat->name;
            $keys[] = (string) $cat->id;
            $values[] = $count;
        }

        $combined = array_map(null, $labels, $keys, $values);
        usort($combined, function ($a, $b) {
            return $b[2] <=> $a[2];
        });

        return [
            'labels' => array_column($combined, 0),
            'keys' => array_column($combined, 1),
            'values' => array_column($combined, 2),
        ];
    }

    #[Computed]
    public function agentLeaderboard(): Collection
    {
        $agents = User::where('company_id', $this->companyId())
            ->whereIn('role', ['admin', 'operator'])
            ->get();

        return $agents->map(function ($agent) {
            $base = $this->baseQuery()->where('assigned_to', $agent->id);
            $assigned = (clone $base)->count();
            $resolved = (clone $base)->where('status', 'resolved')->count();
            $rate = $assigned > 0 ? round(($resolved / $assigned) * 100, 1) : 0;

            return [
                'agent' => $agent,
                'assigned' => $assigned,
                'resolved' => $resolved,
                'rate' => $rate,
            ];
        })->sortByDesc('rate')->values();
    }

    #[Computed]
    public function categoryHealth(): Collection
    {
        $categories = TicketCategory::where('company_id', $this->companyId())->get();

        return $categories->map(function ($cat) {
            $base = $this->baseQuery()->where('category_id', $cat->id);
            $total = (clone $base)->count();
            $resolved = (clone $base)->where('status', 'resolved')->count();
            $open = (clone $base)->whereIn('status', ['open', 'in_progress', 'pending'])->count();
            $rate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
            $resolvedTickets = (clone $base)->whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
            $avgResMinutes = null;
            if ($resolvedTickets->isNotEmpty()) {
                $sum = $resolvedTickets->sum(function ($t) {
                    return Carbon::parse($t->created_at)->diffInMinutes(Carbon::parse($t->resolved_at));
                });
                $avgResMinutes = round($sum / $resolvedTickets->count(), 1);
            }

            $sparkline = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $sparkline[] = Ticket::where('company_id', $this->companyId())
                    ->where('category_id', $cat->id)
                    ->whereDate('created_at', $date)
                    ->count();
            }

            return [
                'category' => $cat,
                'total' => $total,
                'resolved' => $resolved,
                'open' => $open,
                'rate' => $rate,
                'avg_resolution_minutes' => $avgResMinutes,
                'sparkline' => $sparkline,
            ];
        })->sortByDesc('total')->values();
    }

    #[Computed]
    public function expandedCategoryDetails(): ?array
    {
        if (! $this->expandedCategoryId) {
            return null;
        }

        $cid = $this->companyId();
        $base = $this->baseQuery()->where('category_id', $this->expandedCategoryId);

        $catId = $this->expandedCategoryId;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $topAgents = User::where('company_id', $cid)
            ->whereIn('role', ['admin', 'operator'])
            ->whereHas('tickets', function ($q) use ($catId, $cid, $startDate, $endDate) {
                $q->where('category_id', $catId)
                    ->where('company_id', $cid)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->withCount(['tickets as cat_count' => function ($q) use ($catId, $cid, $startDate, $endDate) {
                $q->where('category_id', $catId)
                    ->where('company_id', $cid)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }])
            ->orderByDesc('cat_count')
            ->take(5)
            ->get();

        $priorities = ['urgent', 'high', 'medium', 'low'];
        $priValues = [];
        foreach ($priorities as $p) {
            $priValues[] = (clone $base)->where('priority', $p)->count();
        }

        return [
            'agents' => $topAgents,
            'priority_labels' => ['Urgent', 'High', 'Medium', 'Low'],
            'priority_values' => $priValues,
            'priority_colors' => ['#ef4444', '#f97316', '#3b82f6', '#22c55e'],
        ];
    }

    #[Computed]
    public function selectedAgentData(): ?array
    {
        $agentId = $this->selectedAgentId;
        if (! $agentId) {
            return null;
        }

        $agent = User::find($agentId);
        if (! $agent) {
            return null;
        }

        $base = $this->baseQuery()->where('assigned_to', $agentId);
        $assigned = (clone $base)->count();
        $resolved = (clone $base)->where('status', 'resolved')->count();
        $rate = $assigned > 0 ? round(($resolved / $assigned) * 100, 1) : 0;

        $ticketIds = (clone $base)->pluck('id');
        $responseMinutes = null;
        $resolutionMinutes = null;

        if ($ticketIds->isNotEmpty()) {
            $firstReplies = TicketReply::whereIn('ticket_id', $ticketIds)
                ->where('is_technician', true)
                ->orderBy('ticket_id')
                ->orderBy('created_at')
                ->get()
                ->unique('ticket_id');

            if ($firstReplies->isNotEmpty()) {
                $tickets = Ticket::whereIn('id', $firstReplies->pluck('ticket_id'))->get()->keyBy('id');
                $sum = 0;
                foreach ($firstReplies as $r) {
                    $t = $tickets->get($r->ticket_id);
                    if ($t) {
                        $sum += Carbon::parse($t->created_at)->diffInMinutes(Carbon::parse($r->created_at));
                    }
                }
                $responseMinutes = round($sum / $firstReplies->count(), 1);
            }

            $resolvedTickets = Ticket::whereIn('id', $ticketIds)->whereNotNull('resolved_at')->get();
            if ($resolvedTickets->isNotEmpty()) {
                $sum = $resolvedTickets->sum(function ($t) {
                    return Carbon::parse($t->created_at)->diffInMinutes(Carbon::parse($t->resolved_at));
                });
                $resolutionMinutes = round($sum / $resolvedTickets->count(), 1);
            }
        }

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $dailyLabels = [];
        $dailyResolved = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dailyLabels[] = $d->format('M j');
            $dailyResolved[] = Ticket::where('company_id', $this->companyId())
                ->where('assigned_to', $agentId)
                ->whereDate('resolved_at', $d->format('Y-m-d'))
                ->count();
        }

        $categories = TicketCategory::where('company_id', $this->companyId())->get();
        $catLabels = [];
        $catValues = [];
        foreach ($categories as $cat) {
            $c = (clone $base)->where('category_id', $cat->id)->count();
            if ($c > 0) {
                $catLabels[] = $cat->name;
                $catValues[] = $c;
            }
        }

        $recentResolved = (clone $base)
            ->where('status', 'resolved')
            ->with('category')
            ->latest('resolved_at')
            ->take(10)
            ->get();

        return [
            'agent' => $agent,
            'assigned' => $assigned,
            'resolved' => $resolved,
            'rate' => $rate,
            'avg_response_minutes' => $responseMinutes,
            'avg_resolution_minutes' => $resolutionMinutes,
            'daily_labels' => $dailyLabels,
            'daily_resolved' => $dailyResolved,
            'category_labels' => $catLabels,
            'category_values' => $catValues,
            'recent_resolved' => $recentResolved,
        ];
    }

    #[Computed]
    public function allAgentPerformance(): Collection
    {
        $agents = User::where('company_id', $this->companyId())
            ->whereIn('role', ['admin', 'operator'])
            ->get();

        $result = $agents->map(function ($agent) {
            $base = $this->baseQuery()->where('assigned_to', $agent->id);
            $assigned = (clone $base)->count();
            $resolved = (clone $base)->where('status', 'resolved')->count();
            $rate = $assigned > 0 ? round(($resolved / $assigned) * 100, 1) : 0;

            $ticketIds = (clone $base)->pluck('id');
            $responseMinutes = null;
            $resolutionMinutes = null;

            if ($ticketIds->isNotEmpty()) {
                $firstReplies = TicketReply::whereIn('ticket_id', $ticketIds)
                    ->where('is_technician', true)
                    ->orderBy('ticket_id')
                    ->orderBy('created_at')
                    ->get()
                    ->unique('ticket_id');

                if ($firstReplies->isNotEmpty()) {
                    $tickets = Ticket::whereIn('id', $firstReplies->pluck('ticket_id'))->get()->keyBy('id');
                    $sum = 0;
                    foreach ($firstReplies as $r) {
                        $t = $tickets->get($r->ticket_id);
                        if ($t) {
                            $sum += Carbon::parse($t->created_at)->diffInMinutes(Carbon::parse($r->created_at));
                        }
                    }
                    $responseMinutes = round($sum / $firstReplies->count(), 1);
                }

                $resolvedTickets = Ticket::whereIn('id', $ticketIds)->whereNotNull('resolved_at')->get();
                if ($resolvedTickets->isNotEmpty()) {
                    $sum = $resolvedTickets->sum(function ($t) {
                        return Carbon::parse($t->created_at)->diffInMinutes(Carbon::parse($t->resolved_at));
                    });
                    $resolutionMinutes = round($sum / $resolvedTickets->count(), 1);
                }
            }

            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);
            $dailyResolved = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dailyResolved[] = Ticket::where('company_id', $this->companyId())
                    ->where('assigned_to', $agent->id)
                    ->whereDate('resolved_at', $d->format('Y-m-d'))
                    ->count();
            }

            return [
                'agent' => $agent,
                'tickets_assigned' => $assigned,
                'tickets_resolved' => $resolved,
                'resolution_rate' => $rate,
                'avg_response_minutes' => $responseMinutes,
                'avg_resolution_minutes' => $resolutionMinutes,
                'daily_resolved' => $dailyResolved,
            ];
        });

        $sorted = $this->agentSortDirection === 'asc'
            ? $result->sortBy($this->agentSortColumn, SORT_NATURAL)
            : $result->sortByDesc($this->agentSortColumn, SORT_NATURAL);

        return $sorted->values();
    }

    #[Computed]
    public function agentDailyLabels(): array
    {
        $labels = [];
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $labels[] = $d->format('M j');
        }

        return $labels;
    }

    #[Computed]
    public function agents(): Collection
    {
        return User::where('company_id', $this->companyId())
            ->whereIn('role', ['admin', 'operator'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        return TicketCategory::where('company_id', $this->companyId())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function paginatedTickets()
    {
        $query = Ticket::where('company_id', $this->companyId())
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->with(['user:id,name', 'category:id,name']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }
        if ($this->filterAgent) {
            $query->where('assigned_to', $this->filterAgent);
        }
        if ($this->ticketSearch) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', "%{$this->ticketSearch}%")
                    ->orWhere('subject', 'like', "%{$this->ticketSearch}%")
                    ->orWhere('customer_name', 'like', "%{$this->ticketSearch}%");
            });
        }

        return $query->orderBy($this->ticketSortBy, $this->ticketSortDir)->paginate(20);
    }

    public function exportTicketsCsv(): StreamedResponse
    {
        $query = Ticket::where('company_id', $this->companyId())
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->with(['user', 'category']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }
        if ($this->filterAgent) {
            $query->where('assigned_to', $this->filterAgent);
        }

        $tickets = $query->orderBy('created_at')->get();
        $filename = "tickets-{$this->startDate}-to-{$this->endDate}.csv";

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ticket ID', 'Subject', 'Customer Name', 'Customer Email', 'Category',
                'Priority', 'Status', 'Assigned Agent', 'Created At', 'Resolved At',
                'First Response Time (min)', 'Resolution Time (min)',
            ]);

            foreach ($tickets as $ticket) {
                $firstReply = TicketReply::where('ticket_id', $ticket->id)
                    ->where('is_technician', true)
                    ->orderBy('created_at')
                    ->first();

                $responseMin = ($firstReply && $ticket->created_at)
                    ? Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($firstReply->created_at))
                    : '';

                $resolutionMin = ($ticket->resolved_at && $ticket->created_at)
                    ? Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->resolved_at))
                    : '';

                fputcsv($handle, [
                    $ticket->ticket_number ?? '',
                    $ticket->subject ?? '',
                    $ticket->customer_name ?? '',
                    $ticket->customer_email ?? '',
                    $ticket->category?->name ?? '',
                    $ticket->priority ?? '',
                    $ticket->status ?? '',
                    $ticket->user?->name ?? '',
                    $ticket->created_at ? Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s') : '',
                    $ticket->resolved_at ? Carbon::parse($ticket->resolved_at)->format('Y-m-d H:i:s') : '',
                    $responseMin,
                    $resolutionMin,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportAgentsCsv(): StreamedResponse
    {
        $data = $this->allAgentPerformance;
        $filename = "agent-performance-{$this->startDate}-to-{$this->endDate}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Agent Name', 'Agent Email', 'Tickets Assigned', 'Tickets Resolved',
                'Avg Response Time (min)', 'Avg Resolution Time (min)', 'Resolution Rate %',
            ]);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row['agent']->name,
                    $row['agent']->email,
                    $row['tickets_assigned'],
                    $row['tickets_resolved'],
                    $row['avg_response_minutes'] ?? '',
                    $row['avg_resolution_minutes'] ?? '',
                    $row['resolution_rate'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.dashboard.reports-analytics');
    }
}
