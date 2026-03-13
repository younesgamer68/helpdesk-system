@use('Carbon\Carbon')
<div x-data="reportsCharts({
    ticketVolume: @js($this->ticketVolumeChart),
    statusBreakdown: @js($this->statusBreakdown),
    priorityBreakdown: @js($this->priorityBreakdown),
    categoryVolume: @js($this->categoryVolume),
    activeTab: @js($activeTab),
    selectedAgentData: @js($activeTab === 'agents' ? $this->selectedAgentData : null),
    expandedCategoryDetails: @js($activeTab === 'categories' ? $this->expandedCategoryDetails : null),
    categoryHealth: @js($activeTab === 'categories' ? $this->categoryHealth : null),
})" x-init="init()" id="reports-page">
    <div class="p-4 lg:p-6 space-y-6">

        {{-- Header Bar --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Reports & Analytics</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Insights and performance metrics for your
                    helpdesk</p>
            </div>
            <div class="flex flex-wrap items-center gap-3" id="reports-controls">
                <flux:select wire:model.live="datePreset" class="w-130px">
                    <flux:select.option value="today">Today</flux:select.option>
                    <flux:select.option value="this_week">This Week</flux:select.option>
                    <flux:select.option value="this_month">This Month</flux:select.option>
                    <flux:select.option value="last_3_months">Last 3 Months</flux:select.option>
                    <flux:select.option value="custom">Custom</flux:select.option>
                </flux:select>
                @if ($datePreset === 'custom')
                    <input type="date" wire:model.live="startDate"
                        class="w-130px rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2" />
                    <input type="date" wire:model.live="endDate"
                        class="w-130px rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2" />
                @endif

            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="border-b border-zinc-200 dark:border-zinc-800">
            <nav class="flex gap-1 justify-between -mb-px">
                <div>
                    @foreach ([
        'overview' => 'Overview',
        'agents' => 'Agent Performance',
        'tickets' => 'Tickets',
        'categories' => 'Categories',
    ] as $tabKey => $tabLabel)
                        <button wire:click="setTab('{{ $tabKey }}')"
                            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === $tabKey ? 'border-teal-500 text-teal-400' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:border-zinc-600' }}">
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2">
                    @if ($activeTab === 'tickets')
                        <flux:button wire:click="exportTicketsCsv" variant="ghost" size="sm"
                            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                            <span class="flex items-center gap-1 whitespace-nowrap">
                                <flux:icon.arrow-down-tray class="size-4 shrink-0" /> Tickets CSV
                            </span>
                        </flux:button>
                    @endif
                    @if ($activeTab === 'agents')
                        <flux:button wire:click="exportAgentsCsv" variant="ghost" size="sm"
                            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                            <span class="flex items-center gap-1 whitespace-nowrap">
                                <flux:icon.arrow-down-tray class="size-4 shrink-0" /> Agents CSV
                            </span>
                        </flux:button>
                    @endif
                    <flux:button type="button" variant="ghost" size="sm"
                        class="expdf-btn text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100"
                        @click="exportPdf()">
                        <flux:icon.document class="size-4 mr-1" /> Export PDF
                    </flux:button>
                </div>
            </nav>
        </div>

        {{-- ===== OVERVIEW TAB ===== --}}
        @if ($activeTab === 'overview')
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
            @endphp

            {{-- Metrics Strip --}}
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                <div
                    class="grid grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-zinc-200 dark:divide-zinc-800">
                    @php
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
                    @foreach ($metrics as $i => [$label, $value, $prev, $direction])
                        <div
                            class="px-4 py-3 lg:py-0 {{ $i === 0 ? 'lg:pl-0' : '' }} {{ $i === count($metrics) - 1 ? 'lg:pr-0' : '' }}">
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ $label }}
                            </p>
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
                                    {{ $change >= 0 ? '↑' : '↓' }}
                                    {{ abs($change) }}{{ $label === 'Resolution Rate' ? 'pts' : '%' }} vs prior
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Hero Volume Chart --}}
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Ticket Volume</h2>
                <div class="h-80" wire:ignore>
                    <canvas id="chart-volume"></canvas>
                </div>
                <x-reports-empty :show="collect($this->ticketVolumeChart['created'])->sum() === 0 &&
                    collect($this->ticketVolumeChart['resolved'])->sum() === 0" />
            </div>

            {{-- Three Supporting Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Status Distribution</h2>
                    <div class="relative h-52 mx-auto max-w-[220px]" wire:ignore>
                        <canvas id="chart-status"></canvas>
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
                    <x-reports-empty :show="array_sum($this->statusBreakdown['values']) === 0" />
                </div>
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Priority Distribution</h2>
                    <div class="h-52" wire:ignore>
                        <canvas id="chart-priority"></canvas>
                    </div>
                    <x-reports-empty :show="array_sum($this->priorityBreakdown['values']) === 0" />
                </div>
                <div
                    class="mt-print bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Volume by Category</h2>
                    <div class="h-52" wire:ignore>
                        <canvas id="chart-category-vol"></canvas>
                    </div>
                    <x-reports-empty :show="collect($this->categoryVolume['values'])->sum() === 0" />
                </div>
            </div>

            {{-- Agent Leaderboard + Category Health --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Agent Leaderboard</h2>
                    @if ($this->agentLeaderboard->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($this->agentLeaderboard->take(6) as $i => $row)
                                <button wire:click="goToAgentTab({{ $row['agent']->id }})"
                                    class="w-full flex items-center gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg p-2 -m-2 transition-colors text-left">
                                    <span
                                        class="text-xs font-bold {{ $i === 0 ? 'text-teal-400' : 'text-zinc-500' }} w-5">{{ $i + 1 }}</span>
                                    <div
                                        class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                        {{ $row['agent']->initials() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">{{ $row['agent']->name }}
                                        </p>
                                        <p class="text-xs text-zinc-500">{{ $row['resolved'] }} resolved /
                                            {{ $row['assigned'] }} assigned</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-16 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-teal-500 rounded-full"
                                                style="width: {{ min($row['rate'], 100) }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs text-zinc-600 dark:text-zinc-300 w-10 text-right">{{ $row['rate'] }}%</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <x-reports-empty :show="true" />
                    @endif
                </div>
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Category Health</h2>
                    @if ($this->categoryHealth->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($this->categoryHealth->take(6) as $row)
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full shrink-0"
                                        style="background-color: {{ $row['category']->color ?? '#14b8a6' }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">{{ $row['category']->name }}
                                        </p>
                                        <p class="text-xs text-zinc-500">{{ $row['total'] }} tickets &middot;
                                            {{ $row['open'] }} open</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-16 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-teal-500 rounded-full"
                                                style="width: {{ min($row['rate'], 100) }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs text-zinc-600 dark:text-zinc-300 w-10 text-right">{{ $row['rate'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-reports-empty :show="true" />
                    @endif
                </div>
            </div>

            {{-- ===== AGENT PERFORMANCE TAB ===== --}}
        @elseif($activeTab === 'agents')
            {{-- Agent Pills --}}
            <div class="flex flex-wrap gap-2">
                <button wire:click="selectAgent(null)"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedAgentId === null ? 'bg-teal-500/20 text-teal-400 border border-teal-500/40' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                    All Agents
                </button>
                @foreach ($this->agents as $agent)
                    <button wire:click="selectAgent({{ $agent->id }})"
                        class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors flex items-center gap-2 {{ $selectedAgentId === $agent->id ? 'bg-teal-500/20 text-teal-400 border border-teal-500/40' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        <span
                            class="w-5 h-5 rounded-full bg-zinc-300 dark:bg-zinc-700 flex items-center justify-center text-[10px] text-zinc-700 dark:text-zinc-300">{{ $agent->initials() }}</span>
                        {{ $agent->name }}
                    </button>
                @endforeach
            </div>

            @if ($selectedAgentId && $this->selectedAgentData)
                @php $sa = $this->selectedAgentData; @endphp
                {{-- Agent Profile Header --}}
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-lg font-bold text-teal-400 border-2 border-teal-500/30">
                        {{ $sa['agent']->initials() }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $sa['agent']->name }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $sa['agent']->email }}</p>
                    </div>
                </div>

                {{-- Agent Stats Strip --}}
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <div
                        class="grid grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-zinc-200 dark:divide-zinc-800">
                        @php
                            $agentMetrics = [
                                ['Assigned', $sa['assigned']],
                                ['Resolved', $sa['resolved']],
                                ['Resolution Rate', $sa['rate'] . '%'],
                                [
                                    'Avg Response',
                                    $sa['avg_response_minutes'] !== null
                                        ? floor($sa['avg_response_minutes'] / 60) .
                                            'h ' .
                                            round($sa['avg_response_minutes'] % 60) .
                                            'm'
                                        : '—',
                                ],
                                [
                                    'Avg Resolution',
                                    $sa['avg_resolution_minutes'] !== null
                                        ? floor($sa['avg_resolution_minutes'] / 60) .
                                            'h ' .
                                            round($sa['avg_resolution_minutes'] % 60) .
                                            'm'
                                        : '—',
                                ],
                            ];
                        @endphp
                        @foreach ($agentMetrics as [$lbl, $val])
                            <div class="px-4 py-3 lg:py-0">
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ $lbl }}</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $val }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Agent Daily Chart + Category Distribution --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Daily Resolved</h2>
                        <div class="h-56" wire:ignore>
                            <canvas id="chart-agent-daily"></canvas>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Category Distribution
                        </h2>
                        <div class="h-56" wire:ignore>
                            <canvas id="chart-agent-cats"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Recent Resolved Tickets --}}
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Recent Resolved Tickets
                    </h2>
                    @if ($sa['recent_resolved']->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="text-zinc-500 dark:text-zinc-400 text-xs uppercase border-b border-zinc-200 dark:border-zinc-800">
                                    <tr>
                                        <th class="text-left py-2 px-3">Subject</th>
                                        <th class="text-left py-2 px-3">Category</th>
                                        <th class="text-right py-2 px-3">Resolution Time</th>
                                        <th class="text-right py-2 px-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/60">
                                    @foreach ($sa['recent_resolved'] as $ticket)
                                        @php
                                            $resTime =
                                                $ticket->resolved_at && $ticket->created_at
                                                    ? Carbon::parse($ticket->created_at)->diffInMinutes(
                                                        Carbon::parse($ticket->resolved_at),
                                                    )
                                                    : null;
                                        @endphp
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                                            <td class="py-2 px-3 text-zinc-900 dark:text-zinc-100 max-w-xs truncate">
                                                {{ $ticket->subject }}</td>
                                            <td class="py-2 px-3 text-zinc-500 dark:text-zinc-400">
                                                {{ $ticket->category?->name ?? '—' }}</td>
                                            <td class="py-2 px-3 text-right text-zinc-500 dark:text-zinc-400">
                                                {{ $resTime !== null ? floor($resTime / 60) . 'h ' . $resTime % 60 . 'm' : '—' }}
                                            </td>
                                            <td class="py-2 px-3 text-right">
                                                <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}"
                                                    class="text-teal-400 hover:text-teal-300 text-xs"
                                                    wire:navigate>View →</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-reports-empty :show="true" message="No resolved tickets this period" />
                    @endif
                </div>
            @else
                {{-- All Agents Overview --}}
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-white">All Agent Performance</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="text-zinc-500 dark:text-zinc-400 uppercase text-xs font-medium border-b border-zinc-200 dark:border-zinc-800">
                                <tr>
                                    <th class="text-left py-3 px-4">Agent</th>
                                    <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100"
                                        wire:click="setAgentSort('tickets_assigned')">
                                        Assigned @if ($agentSortColumn === 'tickets_assigned')
                                            {{ $agentSortDirection === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                    <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100"
                                        wire:click="setAgentSort('tickets_resolved')">
                                        Resolved @if ($agentSortColumn === 'tickets_resolved')
                                            {{ $agentSortDirection === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                    <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100"
                                        wire:click="setAgentSort('avg_response_minutes')">
                                        Avg Response @if ($agentSortColumn === 'avg_response_minutes')
                                            {{ $agentSortDirection === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                    <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100"
                                        wire:click="setAgentSort('avg_resolution_minutes')">
                                        Avg Resolution @if ($agentSortColumn === 'avg_resolution_minutes')
                                            {{ $agentSortDirection === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                    <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100"
                                        wire:click="setAgentSort('resolution_rate')">
                                        Resolution Rate @if ($agentSortColumn === 'resolution_rate')
                                            {{ $agentSortDirection === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @forelse($this->allAgentPerformance as $row)
                                    @php $isTop = $row['resolution_rate'] === $this->allAgentPerformance->max('resolution_rate') && $row['tickets_assigned'] > 0; @endphp
                                    <tr class="hover:bg-zinc-800/30 cursor-pointer {{ $isTop ? 'border-l-2 ' : '' }}"
                                        wire:click="selectAgent({{ $row['agent']->id }})">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                                    {{ $row['agent']->initials() }}</div>
                                                <div>
                                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $row['agent']->name }}</p>
                                                    <p class="text-xs text-zinc-500">{{ $row['agent']->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">
                                            {{ $row['tickets_assigned'] }}</td>
                                        <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">
                                            {{ $row['tickets_resolved'] }}</td>
                                        <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">
                                            {{ $row['avg_response_minutes'] !== null ? floor($row['avg_response_minutes'] / 60) . 'h ' . round($row['avg_response_minutes'] % 60) . 'm' : '—' }}
                                        </td>
                                        <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">
                                            {{ $row['avg_resolution_minutes'] !== null ? floor($row['avg_resolution_minutes'] / 60) . 'h ' . round($row['avg_resolution_minutes'] % 60) . 'm' : '—' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2 justify-end">
                                                <div
                                                    class="w-16 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                                    <div class="h-full bg-teal-500 rounded-full"
                                                        style="width: {{ min($row['resolution_rate'], 100) }}%"></div>
                                                </div>
                                                <span
                                                    class="text-zinc-600 dark:text-zinc-300 text-xs w-10 text-right">{{ $row['resolution_rate'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-zinc-500">No agents found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ===== TICKETS TAB ===== --}}
        @elseif($activeTab === 'tickets')
            {{-- Filters --}}
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                <div class="flex flex- items-center gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" wire:model.live.debounce.300ms="ticketSearch"
                            placeholder="Search subject or customer..."
                            class="w-full rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2 placeholder-zinc-500" />
                    </div>
                    <flux:select wire:model.live="filterStatus" class="min-w-[130px]">
                        <flux:select.option value="">All Statuses</flux:select.option>
                        @foreach (['open', 'in_progress', 'pending', 'resolved', 'closed'] as $s)
                            <flux:select.option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="filterPriority" class="min-w-[120px]">
                        <flux:select.option value="">All Priorities</flux:select.option>
                        @foreach (['low', 'medium', 'high', 'urgent'] as $p)
                            <flux:select.option value="{{ $p }}">{{ ucfirst($p) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="filterCategory" class="min-w-[130px]">
                        <flux:select.option value="">All Categories</flux:select.option>
                        @foreach ($this->categories as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="filterAgent" class="min-w-[130px]">
                        <flux:select.option value="">All Agents</flux:select.option>
                        @foreach ($this->agents as $ag)
                            <flux:select.option value="{{ $ag->id }}">{{ $ag->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @if ($filterStatus || $filterPriority || $filterCategory || $filterAgent || $ticketSearch)
                        <flux:button wire:click="clearTicketFilters" variant="ghost" size="sm"
                            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">Clear
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Tickets Table --}}
            <div
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="text-zinc-500 dark:text-zinc-400 uppercase text-xs font-medium border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                @foreach ([
        'ticket_number' => 'Ticket ID',
        'subject' => 'Subject',
        'customer_name' => 'Customer',
        'category_id' => 'Category',
        'priority' => 'Priority',
        'status' => 'Status',
        'assigned_to' => 'Agent',
        'created_at' => 'Created',
    ] as $col => $heading)
                                    <th class="py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100"
                                        wire:click="setTicketSort('{{ $col }}')">
                                        {{ $heading }} @if ($ticketSortBy === $col)
                                            {{ $ticketSortDir === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                @endforeach
                                <th class="py-3 px-4 text-right">Resolution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/60">
                            @forelse($this->paginatedTickets as $ticket)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors cursor-pointer"
                                    onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                                    <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400 font-mono text-xs">
                                        {{ $ticket->ticket_number }}</td>
                                    <td class="py-3 px-4 text-zinc-900 dark:text-zinc-100 max-w-[200px] truncate">
                                        {{ $ticket->subject }}</td>
                                    <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $ticket->customer_name }}</td>
                                    <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">
                                        {{ $ticket->category?->name ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        @php
                                            $priBg = match ($ticket->priority) {
                                                'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                                'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                default
                                                    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $priBg }}">{{ ucfirst($ticket->priority) }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $stBg = match ($ticket->status) {
                                                'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'in_progress'
                                                    => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                                'resolved' => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
                                                'closed'
                                                    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
                                                default
                                                    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $stBg }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">
                                        {{ $ticket->user?->name ?? '—' }}</td>
                                    <td class="py-3 px-4 text-zinc-500 text-xs">
                                        {{ Carbon::parse($ticket->created_at)->format('M j, Y') }}</td>
                                    <td class="py-3 px-4 text-right text-zinc-500 text-xs">
                                        @if ($ticket->resolved_at)
                                            @php $mins = Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->resolved_at)); @endphp
                                            {{ floor($mins / 60) }}h {{ $mins % 60 }}m
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-12 text-center">
                                        <x-reports-empty :show="true" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($this->paginatedTickets->hasPages())
                    <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-800">
                        {{ $this->paginatedTickets->links() }}
                    </div>
                @endif
            </div>

            {{-- ===== CATEGORIES TAB ===== --}}
        @elseif($activeTab === 'categories')
            @if ($this->categoryHealth->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($this->categoryHealth as $item)
                        <div
                            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                            <button wire:click="toggleCategory({{ $item['category']->id }})"
                                class="w-full p-5 text-left hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full"
                                            style="background-color: {{ $item['category']->color ?? '#14b8a6' }}">
                                        </div>
                                        <span
                                            class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item['category']->name }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-zinc-500 transition-transform {{ $expandedCategoryId === $item['category']->id ? 'rotate-180' : '' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span
                                        class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $item['rate'] }}%</span>
                                    <span class="text-xs text-zinc-500">resolution rate</span>
                                </div>
                                <div class="grid grid-cols-3 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-zinc-500 text-xs">Total</p>
                                        <p class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $item['total'] }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-zinc-500 text-xs">Resolved</p>
                                        <p class="text-teal-400 font-semibold">{{ $item['resolved'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-zinc-500 text-xs">Open</p>
                                        <p class="text-blue-400 font-semibold">{{ $item['open'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-zinc-500">Avg resolution:</span>
                                    <span class="text-xs text-zinc-600 dark:text-zinc-300">
                                        @if ($item['avg_resolution_minutes'] !== null)
                                            {{ floor($item['avg_resolution_minutes'] / 60) }}h
                                            {{ round($item['avg_resolution_minutes'] % 60) }}m
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-3 flex items-end gap-[2px] h-6">
                                    @php $sparkMax = max($item['sparkline']) ?: 1; @endphp
                                    @foreach ($item['sparkline'] as $sv)
                                        <div class="flex-1 bg-teal-500/40 rounded-sm"
                                            style="height: {{ max(($sv / $sparkMax) * 100, 4) }}%"></div>
                                    @endforeach
                                </div>
                                <p class="text-[10px] text-zinc-500 dark:text-zinc-600 mt-1">Last 7 days</p>
                            </button>

                            {{-- Expanded Details --}}
                            @if ($expandedCategoryId === $item['category']->id && $this->expandedCategoryDetails)
                                @php $details = $this->expandedCategoryDetails; @endphp
                                <div class="border-t border-zinc-200 dark:border-zinc-800 p-5 space-y-4">
                                    <div>
                                        <h4 class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Top
                                            Agents</h4>
                                        @forelse($details['agents'] as $topAgent)
                                            <div class="flex items-center gap-2 py-1">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-[10px] text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                                    {{ $topAgent->initials() }}</div>
                                                <span
                                                    class="text-sm text-zinc-600 dark:text-zinc-300">{{ $topAgent->name }}</span>
                                                <span class="text-xs text-zinc-500 ml-auto">{{ $topAgent->cat_count }}
                                                    tickets</span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-zinc-500">No agents for this category</p>
                                        @endforelse
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">
                                            Priority Breakdown</h4>
                                        <div class="space-y-1.5">
                                            @foreach ($details['priority_labels'] as $pi => $pLabel)
                                                @php $pMax = max($details['priority_values']) ?: 1; @endphp
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-xs text-zinc-500 dark:text-zinc-400 w-14">{{ $pLabel }}</span>
                                                    <div
                                                        class="flex-1 h-2 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full"
                                                            style="width: {{ ($details['priority_values'][$pi] / $pMax) * 100 }}%; background-color: {{ $details['priority_colors'][$pi] }}">
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-xs text-zinc-500 w-6 text-right">{{ $details['priority_values'][$pi] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <x-reports-empty :show="true" />
            @endif
        @endif
    </div>

    {{-- PDF Export overlay --}}
    {{-- PDF toast — replaces the jarring fullscreen overlay --}}
    <div x-show="exportingPdf" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="export-overlay fixed bottom-6 right-6 z-50 bg-zinc-900 dark:bg-zinc-800 text-white rounded-xl px-5 py-3.5
            flex items-center gap-3 shadow-xl border border-zinc-700">
        <svg class="animate-spin size-4 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        <span class="text-sm font-medium">Generating PDF…</span>
    </div>
</div>
