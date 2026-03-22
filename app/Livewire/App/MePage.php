<?php

namespace App\Livewire\App;

use App\Models\Ticket;
use App\Models\TicketCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Me')]
class MePage extends Component
{
    public string $name = '';

    public bool $editingName = false;

    public array $selectedCategories = [];

    public bool $editingSpecialties = false;

    public string $datePreset = 'this_week';

    public string $startDate;

    public string $endDate;

    private ?array $_prevDates = null;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->selectedCategories = Auth::user()->categories()->pluck('ticket_category_id')->map(fn ($id) => (string) $id)->toArray();
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

        $this->_prevDates = null;
    }

    public function updatedDatePreset(): void
    {
        if ($this->datePreset !== 'custom') {
            $this->applyPreset($this->datePreset);
        }
    }

    public function saveName(): void
    {
        $this->validate(['name' => 'required|string|max:255']);
        Auth::user()->update(['name' => $this->name]);
        $this->editingName = false;
        $this->dispatch('show-toast', message: 'Name updated.', type: 'success');
    }

    public function cancelEditName(): void
    {
        $this->name = Auth::user()->name;
        $this->editingName = false;
    }

    public function toggleAvailability(): void
    {
        $user = Auth::user();
        $user->is_available = ! $user->is_available;
        $user->save();
    }

    public function saveSpecialties(): void
    {
        $user = Auth::user();
        $user->specialty_id = ! empty($this->selectedCategories) ? (int) $this->selectedCategories[0] : null;
        $user->save();
        $user->categories()->sync($this->selectedCategories);
        $this->editingSpecialties = false;
        $this->dispatch('show-toast', message: 'Specialties updated.', type: 'success');
    }

    // ── Date helpers ────────────────────────────────────────────────────

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

    protected function agentBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Ticket::where('assigned_to', Auth::id())
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate);
    }

    protected function agentPreviousQuery(): \Illuminate\Database\Eloquent\Builder
    {
        [$prevStart, $prevEnd] = $this->previousPeriodDates();

        return Ticket::where('assigned_to', Auth::id())
            ->whereDate('created_at', '>=', $prevStart)
            ->whereDate('created_at', '<=', $prevEnd);
    }

    protected function diffMinutesSql(string $from, string $to): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "(strftime('%s', $to) - strftime('%s', $from)) / 60.0"
            : "TIMESTAMPDIFF(MINUTE, $from, $to)";
    }

    // ── KPI aggregates ──────────────────────────────────────────────────

    #[Computed]
    public function ticketSummary(): object
    {
        return $this->agentBaseQuery()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status IN ('open', 'in_progress', 'pending') THEN 1 ELSE 0 END) AS open_count
            ")
            ->first();
    }

    #[Computed]
    public function prevTicketSummary(): object
    {
        return $this->agentPreviousQuery()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status IN ('open', 'in_progress', 'pending') THEN 1 ELSE 0 END) AS open_count
            ")
            ->first();
    }

    #[Computed]
    public function totalAssigned(): int
    {
        return (int) ($this->ticketSummary->total ?? 0);
    }

    #[Computed]
    public function totalAssignedPrev(): int
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

    #[Computed]
    public function avgFirstResponseMinutes(): ?float
    {
        $firstReply = DB::table('ticket_replies')
            ->select('ticket_id', DB::raw('MIN(created_at) AS first_reply_at'))
            ->where('is_technician', true)
            ->groupBy('ticket_id');

        $avg = DB::table('tickets AS t')
            ->joinSub($firstReply, 'fr', 'fr.ticket_id', '=', 't.id')
            ->where('t.assigned_to', Auth::id())
            ->whereDate('t.created_at', '>=', $this->startDate)
            ->whereDate('t.created_at', '<=', $this->endDate)
            ->selectRaw('AVG('.$this->diffMinutesSql('t.created_at', 'fr.first_reply_at').') AS avg_min')
            ->value('avg_min');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    #[Computed]
    public function resolutionRate(): ?float
    {
        $total = $this->totalAssigned;
        if ($total === 0) {
            return null;
        }

        return round(($this->resolvedCount / $total) * 100, 1);
    }

    #[Computed]
    public function resolutionRatePrev(): ?float
    {
        $total = $this->totalAssignedPrev;
        if ($total === 0) {
            return null;
        }

        return round(($this->resolvedCountPrev / $total) * 100, 1);
    }

    // ── Chart data ──────────────────────────────────────────────────────

    #[Computed]
    public function ticketVolumeChart(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $agentId = Auth::id();

        $createdByDate = Ticket::where('assigned_to', $agentId)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->pluck('cnt', 'date');

        $resolvedByDate = Ticket::where('assigned_to', $agentId)
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

        $counts = $this->agentBaseQuery()
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

        $counts = $this->agentBaseQuery()
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
    public function categoryBreakdown(): array
    {
        $categories = TicketCategory::where('company_id', Auth::user()->company_id)->orderBy('name')->get()->keyBy('id');

        $counts = $this->agentBaseQuery()
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

    // ── Existing profile properties ─────────────────────────────────────

    #[Computed]
    public function categories()
    {
        return TicketCategory::where('company_id', Auth::user()->company_id)->get();
    }

    #[Computed]
    public function userTeams()
    {
        return Auth::user()->teams()->select('teams.id', 'teams.name', 'teams.color')->get();
    }

    public function render()
    {
        return view('livewire.app.me-page');
    }
}
