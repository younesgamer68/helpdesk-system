<?php

namespace App\Livewire\Reports;

use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsAnalytics extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
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

    /**
     * Memoized so the diffInDays arithmetic runs exactly once per request
     * no matter how many computed properties call previousPeriodDates().
     */
    private ?array $_prevDates = null;

    // -------------------------------------------------------------------------

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
        $this->dispatchChartsRefresh();
    }

    public function updatedStartDate(): void
    {
        $this->datePreset = 'custom';
        $this->_prevDates = null; // bust memoize cache on range change
        $this->resetPage();
        $this->dispatchChartsRefresh();
    }

    public function updatedEndDate(): void
    {
        $this->datePreset = 'custom';
        $this->_prevDates = null; // bust memoize cache on range change
        $this->resetPage();
        $this->dispatchChartsRefresh();
    }

    protected function dispatchChartsRefresh(): void
    {
        $this->js("window.dispatchEvent(new CustomEvent('reports-charts-refresh'))");
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

    /**
     * Memoized — diffInDays + subDays only computed once per request regardless
     * of how many KPI properties call this.
     */
    protected function previousPeriodDates(): array
    {
        if ($this->_prevDates !== null) {
            return $this->_prevDates;
        }

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $days = $start->diffInDays($end) + 1;

        return $this->_prevDates = [
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

    /**
     * Reusable subquery: earliest technician reply per ticket.
     * Shared by avgFirstResponseMinutes, allAgentPerformance, selectedAgentData.
     */
    protected function firstReplySubquery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('ticket_replies')
            ->select('ticket_id', DB::raw('MIN(created_at) AS first_reply_at'))
            ->where('is_technician', true)
            ->groupBy('ticket_id');
    }

    // -------------------------------------------------------------------------
    // KPI aggregates — 1 query each instead of 3 separate COUNTs
    // -------------------------------------------------------------------------

    /**
     * Single query for total / resolved / open counts in the current period.
     * All three KPI properties below read from this one cached result object.
     */
    #[Computed]
    public function ticketSummary(): object
    {
        return $this->baseQuery()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status IN ('open', 'in_progress', 'pending') THEN 1 ELSE 0 END) AS open_count
            ")
            ->first();
    }

    /**
     * Same single-query aggregate for the comparison period.
     */
    #[Computed]
    public function prevTicketSummary(): object
    {
        return $this->previousQuery()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status IN ('open', 'in_progress', 'pending') THEN 1 ELSE 0 END) AS open_count
            ")
            ->first();
    }

    // Derived from cached aggregates — zero extra queries each

    #[Computed]
    public function totalTickets(): int
    {
        return (int) ($this->ticketSummary->total ?? 0);
    }

    #[Computed]
    public function totalTicketsPrev(): int
    {
        return (int) ($this->prevTicketSummary->total ?? 0);
    }

    #[Computed]
    public function resolvedCount(): int
    {
        return (int) ($this->ticketSummary->resolved ?? 0);
    }

    #[Computed]
    public function resolvedCountPrev(): int
    {
        return (int) ($this->prevTicketSummary->resolved ?? 0);
    }

    #[Computed]
    public function openCount(): int
    {
        return (int) ($this->ticketSummary->open_count ?? 0);
    }

    #[Computed]
    public function openCountPrev(): int
    {
        return (int) ($this->prevTicketSummary->open_count ?? 0);
    }

    /**
     * Replaced the old pluck-all-ids → load-all-replies → foreach pattern
     * with a single JOIN + AVG(TIMESTAMPDIFF(…)) executed entirely in the DB.
     */
    #[Computed]
    public function avgFirstResponseMinutes(): ?float
    {
        $avg = DB::table('tickets AS t')
            ->joinSub($this->firstReplySubquery(), 'fr', 'fr.ticket_id', '=', 't.id')
            ->where('t.company_id', $this->companyId())
            ->whereDate('t.created_at', '>=', $this->startDate)
            ->whereDate('t.created_at', '<=', $this->endDate)
            ->selectRaw('AVG('.$this->diffMinutesSql('t.created_at', 'fr.first_reply_at').') AS avg_min')
            ->value('avg_min');

        return $avg !== null ? round((float) $avg, 1) : null;
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
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $cid = $this->companyId();

        $createdByDate = Ticket::where('company_id', $cid)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->pluck('cnt', 'date');

        $resolvedByDate = Ticket::where('company_id', $cid)
            ->whereNotNull('resolved_at')
            ->whereDate('resolved_at', '>=', $this->startDate)
            ->whereDate('resolved_at', '<=', $this->endDate)
            ->selectRaw('DATE(resolved_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->pluck('cnt', 'date');

        $labels = [];
        $created = [];
        $resolved = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $date = $d->format('Y-m-d');
            $labels[] = $d->format('M j');
            $created[] = $createdByDate->get($date, 0);
            $resolved[] = $resolvedByDate->get($date, 0);
        }

        return compact('labels', 'created', 'resolved');
    }

    #[Computed]
    public function statusBreakdown(): array
    {
        $statuses = ['open', 'in_progress', 'pending', 'resolved', 'closed'];
        $cid = $this->companyId();

        $counts = Ticket::where('company_id', $cid)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $values = array_map(fn (string $s) => $counts->get($s, 0), $statuses);

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

        $counts = $this->baseQuery()
            ->selectRaw('priority, COUNT(*) as cnt')
            ->groupBy('priority')
            ->pluck('cnt', 'priority');

        $values = array_map(fn (string $p) => $counts->get($p, 0), $priorities);

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
        $categories = TicketCategory::where('company_id', $this->companyId())->orderBy('name')->get()->keyBy('id');

        $counts = $this->baseQuery()
            ->whereNotNull('category_id')
            ->selectRaw('category_id, COUNT(*) as cnt')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id');

        $combined = [];
        foreach ($categories as $cat) {
            $combined[] = [$cat->name, (string) $cat->id, $counts->get($cat->id, 0)];
        }
        usort($combined, fn ($a, $b) => $b[2] <=> $a[2]);

        return [
            'labels' => array_column($combined, 0),
            'keys' => array_column($combined, 1),
            'values' => array_column($combined, 2),
        ];
    }

    /**
     * Derived from the already-computed allAgentPerformance so there is no
     * second set of DB queries for the overview leaderboard widget.
     * Because both carry #[Computed], allAgentPerformance runs once per request.
     */
    #[Computed]
    public function agentLeaderboard(): Collection
    {
        return $this->allAgentPerformance
            ->map(fn (array $row) => [
                'agent' => $row['agent'],
                'assigned' => $row['tickets_assigned'],
                'resolved' => $row['tickets_resolved'],
                'rate' => $row['resolution_rate'],
            ])
            ->sortByDesc('rate')
            ->values();
    }

    #[Computed]
    public function categoryHealth(): Collection
    {
        $cid = $this->companyId();
        $categories = TicketCategory::where('company_id', $cid)->get()->keyBy('id');

        $counts = Ticket::where('company_id', $cid)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved, SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END) as open', ['resolved', 'open', 'in_progress', 'pending'])
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Replaced: load all resolved ticket models into PHP → iterate in foreach.
        // Now: single DB aggregate, AVG computed by the database engine.
        $resolutionMinutesByCat = DB::table('tickets')
            ->where('company_id', $cid)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereNotNull('category_id')
            ->whereNotNull('resolved_at')
            ->selectRaw('category_id, AVG('.$this->diffMinutesSql('created_at', 'resolved_at').') AS avg_min')
            ->groupBy('category_id')
            ->pluck('avg_min', 'category_id')
            ->map(fn ($v) => $v !== null ? round((float) $v, 1) : null);

        // Sparkline clamped to the active date range (max 7 days from range end)
        // so it never queries data outside the current filter window.
        $rangeEnd = Carbon::parse($this->endDate);
        $rangeStart = Carbon::parse($this->startDate);
        $sparkDays = min(7, (int) $rangeStart->diffInDays($rangeEnd) + 1);

        $sparklineDates = collect();
        for ($i = $sparkDays - 1; $i >= 0; $i--) {
            $sparklineDates->push($rangeEnd->copy()->subDays($i)->format('Y-m-d'));
        }

        $sparkStart = $rangeEnd->copy()->subDays($sparkDays - 1)->format('Y-m-d');

        $sparklineData = Ticket::where('company_id', $cid)
            ->whereNotNull('category_id')
            ->whereDate('created_at', '>=', $sparkStart)
            ->whereDate('created_at', '<=', $this->endDate)
            ->selectRaw('DATE(created_at) as date, category_id, COUNT(*) as cnt')
            ->groupBy(DB::raw('DATE(created_at)'), 'category_id')
            ->get()
            ->groupBy('category_id');

        return $categories->map(function ($cat) use ($counts, $resolutionMinutesByCat, $sparklineData, $sparklineDates) {
            $row = $counts->get($cat->id);
            $total = $row?->total ?? 0;
            $resolved = $row?->resolved ?? 0;
            $open = $row?->open ?? 0;
            $rate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
            $avgResMinutes = $resolutionMinutesByCat->get($cat->id);

            $byDate = $sparklineData->get($cat->id)?->pluck('cnt', 'date') ?? collect();
            $sparkline = $sparklineDates->map(fn ($d) => $byDate->get($d, 0))->values()->all();

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

        $priCounts = (clone $base)
            ->selectRaw('priority, COUNT(*) as cnt')
            ->groupBy('priority')
            ->pluck('cnt', 'priority');
        $priorities = ['urgent', 'high', 'medium', 'low'];
        $priValues = array_map(fn (string $p) => $priCounts->get($p, 0), $priorities);

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

        $cid = $this->companyId();
        $base = $this->baseQuery()->where('assigned_to', $agentId);
        $assigned = (clone $base)->count();
        $resolved = (clone $base)->where('status', 'resolved')->count();
        $rate = $assigned > 0 ? round(($resolved / $assigned) * 100, 1) : 0;

        // Replaced: pluck IDs → load replies → load tickets → foreach in PHP.
        // Now: single JOIN + AVG(TIMESTAMPDIFF(…)) in the DB.
        $responseMinutes = DB::table('tickets AS t')
            ->joinSub($this->firstReplySubquery(), 'fr', 'fr.ticket_id', '=', 't.id')
            ->where('t.company_id', $cid)
            ->where('t.assigned_to', $agentId)
            ->whereDate('t.created_at', '>=', $this->startDate)
            ->whereDate('t.created_at', '<=', $this->endDate)
            ->selectRaw('AVG('.$this->diffMinutesSql('t.created_at', 'fr.first_reply_at').') AS avg_min')
            ->value('avg_min');
        $responseMinutes = $responseMinutes !== null ? round((float) $responseMinutes, 1) : null;

        // Replaced: load all resolved ticket models → sum in PHP.
        $resolutionMinutes = DB::table('tickets')
            ->where('company_id', $cid)
            ->where('assigned_to', $agentId)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG('.$this->diffMinutesSql('created_at', 'resolved_at').') AS avg_min')
            ->value('avg_min');
        $resolutionMinutes = $resolutionMinutes !== null ? round((float) $resolutionMinutes, 1) : null;

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $dailyResolvedByDate = Ticket::where('company_id', $cid)
            ->where('assigned_to', $agentId)
            ->whereNotNull('resolved_at')
            ->whereDate('resolved_at', '>=', $this->startDate)
            ->whereDate('resolved_at', '<=', $this->endDate)
            ->selectRaw('DATE(resolved_at) as date, COUNT(*) as cnt')
            ->groupBy(DB::raw('DATE(resolved_at)'))
            ->pluck('cnt', 'date');

        $dailyLabels = [];
        $dailyResolved = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $date = $d->format('Y-m-d');
            $dailyLabels[] = $d->format('M j');
            $dailyResolved[] = $dailyResolvedByDate->get($date, 0);
        }

        $catCounts = (clone $base)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, COUNT(*) as cnt')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id');

        $categories = TicketCategory::where('company_id', $cid)->orderBy('name')->get();
        $catLabels = [];
        $catValues = [];
        foreach ($categories as $cat) {
            $c = $catCounts->get($cat->id, 0);
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
        $cid = $this->companyId();
        $agents = User::where('company_id', $cid)
            ->whereIn('role', ['admin', 'operator'])
            ->get()
            ->keyBy('id');

        $stats = Ticket::where('company_id', $cid)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as assigned, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved', ['resolved'])
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('assigned_to');

        $dailyByAgent = Ticket::where('company_id', $cid)
            ->whereNotNull('assigned_to')
            ->whereNotNull('resolved_at')
            ->whereDate('resolved_at', '>=', $this->startDate)
            ->whereDate('resolved_at', '<=', $this->endDate)
            ->selectRaw('assigned_to, DATE(resolved_at) as date, COUNT(*) as cnt')
            ->groupBy('assigned_to', DB::raw('DATE(resolved_at)'))
            ->get()
            ->groupBy('assigned_to');

        // Replaced: pluck all ticket IDs → load all tickets → load all replies
        // → two nested foreach loops in PHP. Now two focused DB aggregates.
        $responseMinutesByAgent = DB::table('tickets AS t')
            ->joinSub($this->firstReplySubquery(), 'fr', 'fr.ticket_id', '=', 't.id')
            ->where('t.company_id', $cid)
            ->whereDate('t.created_at', '>=', $this->startDate)
            ->whereDate('t.created_at', '<=', $this->endDate)
            ->whereNotNull('t.assigned_to')
            ->selectRaw('t.assigned_to, AVG('.$this->diffMinutesSql('t.created_at', 'fr.first_reply_at').') AS avg_response')
            ->groupBy('t.assigned_to')
            ->pluck('avg_response', 'assigned_to')
            ->map(fn ($v) => $v !== null ? round((float) $v, 1) : null);

        $resolutionMinutesByAgent = DB::table('tickets')
            ->where('company_id', $cid)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereNotNull('assigned_to')
            ->whereNotNull('resolved_at')
            ->selectRaw('assigned_to, AVG('.$this->diffMinutesSql('created_at', 'resolved_at').') AS avg_resolution')
            ->groupBy('assigned_to')
            ->pluck('avg_resolution', 'assigned_to')
            ->map(fn ($v) => $v !== null ? round((float) $v, 1) : null);

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $dateLabels = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateLabels[] = $d->format('Y-m-d');
        }

        $result = $agents->map(function ($agent) use ($stats, $dailyByAgent, $responseMinutesByAgent, $resolutionMinutesByAgent, $dateLabels) {
            $row = $stats->get($agent->id);
            $assigned = $row?->assigned ?? 0;
            $resolved = $row?->resolved ?? 0;
            $rate = $assigned > 0 ? round(($resolved / $assigned) * 100, 1) : 0;

            $byDate = $dailyByAgent->get($agent->id)?->pluck('cnt', 'date') ?? collect();
            $dailyResolved = array_map(fn ($d) => $byDate->get($d, 0), $dateLabels);

            return [
                'agent' => $agent,
                'tickets_assigned' => $assigned,
                'tickets_resolved' => $resolved,
                'resolution_rate' => $rate,
                'avg_response_minutes' => $responseMinutesByAgent->get($agent->id),
                'avg_resolution_minutes' => $resolutionMinutesByAgent->get($agent->id),
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
    public function teamStats(): Collection
    {
        $cid = $this->companyId();

        $teams = Team::where('company_id', $cid)
            ->withCount('members')
            ->orderBy('name')
            ->get();

        $ticketCounts = Ticket::where('company_id', $cid)
            ->whereNotNull('team_id')
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->select('team_id')
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'resolved' or status = 'closed' then 1 else 0 end) as resolved")
            ->selectRaw("sum(case when status in ('open', 'in_progress', 'pending') then 1 else 0 end) as open_count")
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        return $teams->map(function ($team) use ($ticketCounts) {
            $row = $ticketCounts->get($team->id);
            $total = $row ? (int) $row->total : 0;
            $resolved = $row ? (int) $row->resolved : 0;
            $open = $row ? (int) $row->open_count : 0;

            return [
                'id' => $team->id,
                'name' => $team->name,
                'color' => $team->color,
                'members_count' => $team->members_count,
                'total' => $total,
                'resolved' => $resolved,
                'open' => $open,
                'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100) : 0,
            ];
        });
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
            ->with(['assignedTo:id,name', 'category:id,name', 'customer:id,name,email']);

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
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', "%{$this->ticketSearch}%")
                            ->orWhere('email', 'like', "%{$this->ticketSearch}%");
                    });
            });
        }

        return $query->orderBy($this->ticketSortBy, $this->ticketSortDir)->paginate(20);
    }

    /**
     * Replaced: ->get() loads entire result set into memory before streaming.
     * Now: ->chunk(500, …) processes in batches; first-replies fetched per
     * chunk so the IN(…) clause stays small regardless of total export size.
     */
    public function exportTicketsCsv(): StreamedResponse
    {
        $query = Ticket::where('company_id', $this->companyId())
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->with(['assignedTo', 'category', 'customer:id,name,email']);

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

        $filename = "tickets-{$this->startDate}-to-{$this->endDate}.csv";

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ticket ID', 'Subject', 'Customer Name', 'Customer Email', 'Category',
                'Priority', 'Status', 'Assigned Agent', 'Created At', 'Resolved At',
                'First Response Time (min)', 'Resolution Time (min)',
            ]);

            $query->orderBy('created_at')->chunk(500, function ($tickets) use ($handle) {
                $ticketIds = $tickets->pluck('id');

                $firstReplies = TicketReply::whereIn('ticket_id', $ticketIds)
                    ->where('is_technician', true)
                    ->orderBy('ticket_id')
                    ->orderBy('created_at')
                    ->get()
                    ->unique('ticket_id')
                    ->keyBy('ticket_id');

                foreach ($tickets as $ticket) {
                    $firstReply = $firstReplies->get($ticket->id);

                    $responseMin = ($firstReply && $ticket->created_at)
                        ? Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($firstReply->created_at))
                        : '';

                    $resolutionMin = ($ticket->resolved_at && $ticket->created_at)
                        ? Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->resolved_at))
                        : '';

                    fputcsv($handle, [
                        $ticket->ticket_number ?? '',
                        $ticket->subject ?? '',
                        $ticket->customer?->name ?? '',
                        $ticket->customer?->email ?? '',
                        $ticket->category?->name ?? '',
                        $ticket->priority ?? '',
                        $ticket->status ?? '',
                        $ticket->assignedTo?->name ?? '',
                        $ticket->created_at ? Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s') : '',
                        $ticket->resolved_at ? Carbon::parse($ticket->resolved_at)->format('Y-m-d H:i:s') : '',
                        $responseMin,
                        $resolutionMin,
                    ]);
                }
            });

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

    public function getChartConfig(): array
    {
        $selected = $this->activeTab === 'agents' ? $this->selectedAgentData : null;
        $selectedForCharts = $selected ? [
            'daily_labels' => $selected['daily_labels'],
            'daily_resolved' => $selected['daily_resolved'],
            'category_labels' => $selected['category_labels'],
            'category_values' => $selected['category_values'],
        ] : null;

        return [
            'ticketVolume' => $this->ticketVolumeChart,
            'statusBreakdown' => $this->statusBreakdown,
            'priorityBreakdown' => $this->priorityBreakdown,
            'categoryVolume' => $this->categoryVolume,
            'activeTab' => $this->activeTab,
            'selectedAgentData' => $selectedForCharts,
            'expandedCategoryDetails' => null,
            'categoryHealth' => null,
        ];
    }

    protected function diffMinutesSql(string $from, string $to): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "(strftime('%s', $to) - strftime('%s', $from)) / 60.0"
            : "TIMESTAMPDIFF(MINUTE, $from, $to)";
    }

    public function toJSON(): string
    {
        return json_encode($this->getChartConfig());
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.reports.reports-analytics');
    }
}
