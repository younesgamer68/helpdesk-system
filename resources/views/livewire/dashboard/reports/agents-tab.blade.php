@use('Carbon\Carbon')

<div class="space-y-6">
    {{-- Agent Pills --}}
    <div class="flex flex-wrap gap-2">
        <button wire:click="selectAgent(null)"
            class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedAgentId === null ? 'bg-teal-500/20 text-teal-400 border border-teal-500/40' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
            All Agents
        </button>
        @foreach ($this->agents as $agent)
            <button wire:click="selectAgent({{ $agent->id }})"
                class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors flex items-center gap-2 {{ $selectedAgentId === $agent->id ? 'bg-teal-500/20 text-teal-400 border border-teal-500/40' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                <span class="w-5 h-5 rounded-full bg-zinc-300 dark:bg-zinc-700 flex items-center justify-center text-[10px] text-zinc-700 dark:text-zinc-300">{{ $agent->initials() }}</span>
                {{ $agent->name }}
            </button>
        @endforeach
    </div>

    @if ($selectedAgentId && $this->selectedAgentData)
        @php $sa = $this->selectedAgentData; @endphp

        {{-- Agent Profile Header --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-lg font-bold text-teal-400 border-2 border-teal-500/30">
                {{ $sa['agent']->initials() }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $sa['agent']->name }}</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $sa['agent']->email }}</p>
            </div>
        </div>

        {{-- Agent Stats Strip --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            <div class="grid grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-zinc-200 dark:divide-zinc-800">
                @foreach ([
                    ['Assigned', $sa['assigned']],
                    ['Resolved', $sa['resolved']],
                    ['Resolution Rate', $sa['rate'] . '%'],
                    ['Avg Response', $sa['avg_response_minutes'] !== null ? floor($sa['avg_response_minutes'] / 60) . 'h ' . round($sa['avg_response_minutes'] % 60) . 'm' : '—'],
                    ['Avg Resolution', $sa['avg_resolution_minutes'] !== null ? floor($sa['avg_resolution_minutes'] / 60) . 'h ' . round($sa['avg_resolution_minutes'] % 60) . 'm' : '—'],
                ] as [$lbl, $val])
                    <div class="px-4 py-3 lg:py-0">
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ $lbl }}</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $val }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Agent Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Daily Resolved</h2>
                <div class="h-56" wire:ignore>
                    <canvas id="chart-agent-daily"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Category Distribution</h2>
                <div class="h-56" wire:ignore>
                    <canvas id="chart-agent-cats"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Resolved Tickets --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Recent Resolved Tickets</h2>
            @if ($sa['recent_resolved']->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-zinc-500 dark:text-zinc-400 text-xs uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="text-left py-2 px-3">Subject</th>
                                <th class="text-left py-2 px-3">Category</th>
                                <th class="text-right py-2 px-3">Resolution Time</th>
                                <th class="text-right py-2 px-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/90">
                            @foreach ($sa['recent_resolved'] as $ticket)
                                @php
                                    $resTime = $ticket->resolved_at && $ticket->created_at
                                        ? Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->resolved_at))
                                        : null;
                                @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                                    <td class="py-2 px-3 text-zinc-900 dark:text-zinc-100 max-w-xs truncate">{{ $ticket->subject }}</td>
                                    <td class="py-2 px-3 text-zinc-500 dark:text-zinc-400">{{ $ticket->category?->name ?? '—' }}</td>
                                    <td class="py-2 px-3 text-right text-zinc-500 dark:text-zinc-400">
                                        {{ $resTime !== null ? floor($resTime / 60) . 'h ' . $resTime % 60 . 'm' : '—' }}
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}"
                                            class="text-teal-400 hover:text-teal-300 text-xs" wire:navigate>View →</a>
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
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">All Agent Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-zinc-500 dark:text-zinc-400 uppercase text-xs font-medium border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="text-left py-3 px-4">Agent</th>
                            <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100" wire:click="setAgentSort('tickets_assigned')">
                                Assigned @if ($agentSortColumn === 'tickets_assigned') {{ $agentSortDirection === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100" wire:click="setAgentSort('tickets_resolved')">
                                Resolved @if ($agentSortColumn === 'tickets_resolved') {{ $agentSortDirection === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100" wire:click="setAgentSort('avg_response_minutes')">
                                Avg Response @if ($agentSortColumn === 'avg_response_minutes') {{ $agentSortDirection === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100" wire:click="setAgentSort('avg_resolution_minutes')">
                                Avg Resolution @if ($agentSortColumn === 'avg_resolution_minutes') {{ $agentSortDirection === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                            <th class="text-right py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100" wire:click="setAgentSort('resolution_rate')">
                                Resolution Rate @if ($agentSortColumn === 'resolution_rate') {{ $agentSortDirection === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/90">
                        @forelse($this->allAgentPerformance as $row)
                            @php $isTop = $row['resolution_rate'] === $this->allAgentPerformance->max('resolution_rate') && $row['tickets_assigned'] > 0; @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 cursor-pointer {{ $isTop ? 'border-l-5' : '' }}" wire:click="selectAgent({{ $row['agent']->id }})">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                            {{ $row['agent']->initials() }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['agent']->name }}</p>
                                            <p class="text-xs text-zinc-500">{{ $row['agent']->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">{{ $row['tickets_assigned'] }}</td>
                                <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">{{ $row['tickets_resolved'] }}</td>
                                <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">
                                    {{ $row['avg_response_minutes'] !== null ? floor($row['avg_response_minutes'] / 60) . 'h ' . round($row['avg_response_minutes'] % 60) . 'm' : '—' }}
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-600 dark:text-zinc-300">
                                    {{ $row['avg_resolution_minutes'] !== null ? floor($row['avg_resolution_minutes'] / 60) . 'h ' . round($row['avg_resolution_minutes'] % 60) . 'm' : '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2 justify-end">
                                        <div class="w-16 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-teal-500 rounded-full" style="width: {{ min($row['resolution_rate'], 100) }}%"></div>
                                        </div>
                                        <span class="text-zinc-600 dark:text-zinc-300 text-xs w-10 text-right">{{ $row['resolution_rate'] }}%</span>
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
</div>
