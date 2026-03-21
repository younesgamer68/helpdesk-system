@use('Carbon\Carbon')

@php
    $totalCur = $this->totalTickets;
    $totalPrev = $this->totalTicketsPrev;
    $resCur = $this->resolvedCount;
    $resPrev = $this->resolvedCountPrev;
    $openCur = $this->openCount;
    $openPrev = $this->openCountPrev;
    $avgResp = $this->avgFirstResponseMinutes;
    $rateCur = $this->resolutionRate;
    $ratePrev = $this->resolutionRatePrev;
    $metrics = [
        ['Total Tickets', $totalCur, $totalPrev, 'neutral'],
        ['Resolved', $resCur, $resPrev, 'positive'],
        ['Open', $openCur, $openPrev, 'negative'],
        [
            'Avg First Response',
            $avgResp !== null ? floor($avgResp / 60) . 'h ' . round($avgResp % 60) . 'm' : '—',
            null,
            null,
        ],
        ['Resolution Rate', $rateCur !== null ? $rateCur . '%' : '—', $ratePrev, 'positive'],
    ];
@endphp

{{-- Metrics Strip --}}
<div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 mb-8">
    <div class="grid grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-zinc-200 dark:divide-zinc-700">
        @foreach ($metrics as $i => [$label, $value, $prev, $direction])
            <div
                class="px-4 py-3 lg:py-0 {{ $i === 0 ? 'lg:pl-0' : '' }} {{ $i === count($metrics) - 1 ? 'lg:pr-0' : '' }}">
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ is_numeric($value) ? number_format($value) : $value }}</p>
                @if ($prev !== null && is_numeric($value) && is_numeric($prev) && $prev > 0)
                    @php
                        $change =
                            $label === 'Resolution Rate'
                                ? round($value - $prev, 1)
                                : round((($value - $prev) / $prev) * 100, 0);
                        $isGood = match ($direction) {
                            'positive' => $change >= 0,
                            'negative' => $change <= 0,
                            default => true,
                        };
                    @endphp
                    <p class="mt-0.5 text-xs {{ $isGood ? 'text-green-400' : 'text-red-400' }}">
                        {{ $change >= 0 ? '↑' : '↓' }}{{ abs($change) }}{{ $label === 'Resolution Rate' ? 'pts' : '%' }}
                        vs prior
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Hero Volume Chart --}}
<div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 mb-8">
    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Ticket Volume</h2>
    <div class="h-80" wire:ignore>
        <canvas id="chart-volume"></canvas>
    </div>
    <x-app.reports.reports-empty :show="collect($this->ticketVolumeChart['created'])->sum() === 0 &&
        collect($this->ticketVolumeChart['resolved'])->sum() === 0" />
</div>

{{-- Three Supporting Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Status Distribution</h2>
        <div class="relative h-52 mx-auto max-w-[220px]">
            <div class="absolute inset-0" wire:ignore>
                <canvas id="chart-status"></canvas>
            </div>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <span
                    class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ array_sum($this->statusBreakdown['values']) }}</span>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2 justify-center">
            @foreach ($this->statusBreakdown['labels'] as $i => $lbl)
                <button wire:click="applyChartFilter('status', '{{ $this->statusBreakdown['keys'][$i] }}')"
                    class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    <span class="w-2 h-2 rounded-full"
                        style="background-color: {{ $this->statusBreakdown['colors'][$i] }}"></span>
                    {{ $lbl }} ({{ $this->statusBreakdown['values'][$i] }})
                </button>
            @endforeach
        </div>
        <x-app.reports.reports-empty :show="array_sum($this->statusBreakdown['values']) === 0" />
    </div>
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Priority Distribution</h2>
        <div class="h-52" wire:ignore>
            <canvas id="chart-priority"></canvas>
        </div>
        <x-app.reports.reports-empty :show="array_sum($this->priorityBreakdown['values']) === 0" />
    </div>
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Volume by Category</h2>
        <div class="h-52" wire:ignore>
            <canvas id="chart-category-vol"></canvas>
        </div>
        <x-app.reports.reports-empty :show="collect($this->categoryVolume['values'])->sum() === 0" />
    </div>
</div>

{{-- Agent Leaderboard + Category Health --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Agent Leaderboard</h2>
        @if ($this->agentLeaderboard->isNotEmpty())
            <div class="space-y-3">
                @foreach ($this->agentLeaderboard->take(6) as $i => $row)
                    <button wire:click="goToAgentTab({{ $row['agent']->id }})"
                        class="w-full flex items-center gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 rounded-lg p-2 -m-2 transition-colors text-left">
                        <span
                            class="text-xs font-bold {{ $i === 0 ? 'text-emerald-400' : 'text-zinc-500 dark:text-zinc-400' }} w-5">{{ $i + 1 }}</span>
                        <div
                            class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                            {{ $row['agent']->initials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                {{ $row['agent']->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row['resolved'] }} resolved /
                                {{ $row['assigned'] }}
                                assigned</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-1.5 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full"
                                    style="width: {{ min($row['rate'], 100) }}%"></div>
                            </div>
                            <span
                                class="text-xs text-zinc-600 dark:text-zinc-300 w-10 text-right">{{ $row['rate'] }}%</span>
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <x-app.reports.reports-empty :show="true" />
        @endif
    </div>
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Category Health</h2>
        @if ($this->categoryHealth->isNotEmpty())
            <div class="space-y-3">
                @foreach ($this->categoryHealth->take(6) as $row)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full shrink-0 bg-emerald-500"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                {{ $row['category']->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row['total'] }} tickets &middot;
                                {{ $row['open'] }}
                                open</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-1.5 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full"
                                    style="width: {{ min($row['rate'], 100) }}%"></div>
                            </div>
                            <span
                                class="text-xs text-zinc-600 dark:text-zinc-300 w-10 text-right">{{ $row['rate'] }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-app.reports.reports-empty :show="true" />
        @endif
    </div>
</div>
