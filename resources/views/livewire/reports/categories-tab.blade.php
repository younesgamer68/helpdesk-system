@if ($this->categoryHealth->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 items-start">
        @foreach ($this->categoryHealth as $item)
            @php $isExpanded = (int) $expandedCategoryId === (int) $item['category']->id && $this->expandedCategoryDetails; @endphp

            <div wire:key="category-card-{{ $item['category']->id }}"
                class="relative bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden"
                style="height: 230px;">

                {{-- FRONT --}}
                <div
                    class="absolute inset-0 p-5 flex flex-col transition-all duration-200 ease-in-out {{ $isExpanded ? 'opacity-0 translate-y-2 pointer-events-none' : 'opacity-100 translate-y-0' }}">

                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full"
                                style="background-color: {{ $item['category']->color ?? '#14b8a6' }}"></div>
                            <span
                                class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ $item['category']->name }}</span>
                        </div>
                        <button wire:click="toggleCategory({{ $item['category']->id }})"
                            class="text-xs font-medium text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors px-2 py-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            details →
                        </button>
                    </div>

                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $item['rate'] }}%</span>
                        <span class="text-xs text-zinc-500">resolution rate</span>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-sm mb-3">
                        <div>
                            <p class="text-zinc-500 text-xs">Total</p>
                            <p class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $item['total'] }}</p>
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

                    <div class="flex items-center gap-2 mt-auto">
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

                    <div class="mt-3 flex items-end gap-[2px] h-5">
                        @php $sparkMax = !empty($item['sparkline']) ? max(1, max($item['sparkline'])) : 1; @endphp
                        @foreach ($item['sparkline'] ?? [] as $sv)
                            <div class="flex-1 bg-teal-500/40 rounded-sm"
                                style="height: {{ max(($sv / $sparkMax) * 100, 4) }}%"></div>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-zinc-400 mt-1">Last 7 days</p>
                </div>

                {{-- BACK --}}
                <div
                    class="absolute inset-0 p-5 flex flex-col transition-all duration-200 ease-in-out {{ $isExpanded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2 pointer-events-none' }}">

                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full"
                                style="background-color: {{ $item['category']->color ?? '#14b8a6' }}"></div>
                            <span
                                class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $item['category']->name }}</span>
                        </div>
                        <button wire:click="toggleCategory({{ $item['category']->id }})"
                            class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors px-2 py-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            ← back
                        </button>
                    </div>

                    @if ($isExpanded)
                        @php $details = $this->expandedCategoryDetails; @endphp

                        <div class="overflow-y-auto flex-1 space-y-4">
                            <div>
                                <h4 class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider mb-2">Top
                                    Agents</h4>
                                @forelse($details['agents'] as $topAgent)
                                    <div class="flex items-center gap-2 py-1">
                                        <div
                                            class="w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center text-[10px] text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-600 shrink-0">
                                            {{ $topAgent->initials() }}
                                        </div>
                                        <span
                                            class="text-xs text-zinc-600 dark:text-zinc-300 truncate">{{ $topAgent->name }}</span>
                                        <span
                                            class="text-xs text-zinc-400 ml-auto shrink-0">{{ $topAgent->cat_count }}</span>
                                    </div>
                                @empty
                                    <p class="text-xs text-zinc-400">No agents</p>
                                @endforelse
                            </div>

                            <div>
                                <h4 class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider mb-2">
                                    Priority Breakdown</h4>
                                <div class="space-y-1.5">
                                    @foreach ($details['priority_labels'] as $pi => $pLabel)
                                        @php $pMax = !empty($details['priority_values']) ? max(1, max($details['priority_values'])) : 1; @endphp
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-[11px] text-zinc-500 w-12 shrink-0">{{ $pLabel }}</span>
                                            <div
                                                class="flex-1 h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full"
                                                    style="width: {{ ($details['priority_values'][$pi] / $pMax) * 100 }}%; background-color: {{ $details['priority_colors'][$pi] }}">
                                                </div>
                                            </div>
                                            <span
                                                class="text-[11px] text-zinc-400 w-4 text-right shrink-0">{{ $details['priority_values'][$pi] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        @endforeach
    </div>
@else
    <x-app.reports.reports-empty :show="true" />
@endif
