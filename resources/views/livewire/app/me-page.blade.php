<div class="animate-enter" x-data="agentCharts({
    ticketVolume: @js($this->ticketVolumeChart),
    statusBreakdown: @js($this->statusBreakdown),
    priorityBreakdown: @js($this->priorityBreakdown),
    categoryBreakdown: @js($this->categoryBreakdown),
})" x-init="init()"
    wire:key="me-{{ $datePreset }}-{{ $startDate }}-{{ $endDate }}">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Me</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Your profile and performance stats</p>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Profile Card --}}
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            @if (Auth::user()->avatar)
                                <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
                                    class="w-14 h-14 rounded-full object-cover">
                            @else
                                <div
                                    class="flex items-center justify-center w-14 h-14 bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-full text-lg font-semibold">
                                    {{ Auth::user()->initials() }}
                                </div>
                            @endif
                            <span
                                class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-zinc-900 {{ Auth::user()->is_available ? 'bg-green-500' : 'bg-zinc-400' }}"></span>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            @if ($editingName)
                                <div class="flex items-center gap-2">
                                    <input type="text" wire:model="name" wire:keydown.enter="saveName"
                                        wire:keydown.escape="cancelEditName"
                                        class="px-2 py-1 text-base font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-300 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
                                        autofocus>
                                    <button wire:click="saveName"
                                        class="text-emerald-500 hover:text-emerald-600 text-sm font-medium">Save</button>
                                    <button wire:click="cancelEditName"
                                        class="text-zinc-400 hover:text-zinc-500 text-sm">Cancel</button>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ Auth::user()->name }}</h2>
                                    <button wire:click="$set('editingName', true)"
                                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                                        title="Edit name">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">{{ Auth::user()->email }}</p>

                            <div class="flex items-center gap-3 mt-2">
                                @foreach ($this->userTeams as $team)
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                        @if ($team->color)
                                            <span class="w-2 h-2 rounded-full"
                                                style="background-color: {{ $team->color }}"></span>
                                        @endif
                                        {{ $team->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Availability Toggle --}}
                        <button wire:click="toggleAvailability"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full border transition-colors shrink-0
                                {{ Auth::user()->is_available
                                    ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400'
                                    : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-500' }}">
                            <span
                                class="w-2 h-2 rounded-full {{ Auth::user()->is_available ? 'bg-green-500 animate-pulse' : 'bg-zinc-400' }}"></span>
                            <span
                                class="text-xs font-medium">{{ Auth::user()->is_available ? 'Available' : 'Unavailable' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Specialties --}}
            <div>
                <div
                    class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden h-full">
                    <div
                        class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Specialties</h3>
                        @if ($editingSpecialties)
                            <div class="flex items-center gap-2">
                                <button wire:click="saveSpecialties"
                                    class="text-xs font-medium text-emerald-500 hover:text-emerald-600">Save</button>
                                <button wire:click="$set('editingSpecialties', false)"
                                    class="text-xs text-zinc-400 hover:text-zinc-500">Cancel</button>
                            </div>
                        @else
                            <button wire:click="$set('editingSpecialties', true)"
                                class="text-xs text-emerald-500 hover:text-emerald-400 transition-colors">Edit</button>
                        @endif
                    </div>

                    <div class="px-5 py-4">
                        @if ($editingSpecialties)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($this->categories as $category)
                                    @php $isSelected = in_array((string) $category->id, $selectedCategories); @endphp
                                    <label
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm cursor-pointer transition-colors border
                                        {{ $isSelected
                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'
                                            : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                                        <input type="checkbox" wire:model="selectedCategories"
                                            value="{{ $category->id }}" class="sr-only">
                                        {{ $category->name }}
                                    </label>
                                @endforeach
                            </div>
                        @else
                            @php $userCategories = Auth::user()->categories; @endphp
                            @if ($userCategories->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($userCategories as $category)
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">No specialties selected</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Stats Section ═══ --}}

        {{-- Date Preset Picker --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">My Performance</h2>
            <div class="flex items-center gap-2">
                @foreach (['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_3_months' => 'Last 3 Months'] as $preset => $label)
                    <button wire:click="applyPreset('{{ $preset }}')"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                            {{ $datePreset === $preset
                                ? 'bg-emerald-500 text-white'
                                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- KPI Metrics Strip --}}
        @php
            $totalCur = $this->totalAssigned;
            $totalPrev = $this->totalAssignedPrev;
            $resCur = $this->resolvedCount;
            $resPrev = $this->resolvedCountPrev;
            $openCur = $this->openCount;
            $openPrev = $this->openCountPrev;
            $avgResp = $this->avgFirstResponseMinutes;
            $rateCur = $this->resolutionRate;
            $ratePrev = $this->resolutionRatePrev;
            $metrics = [
                ['Assigned', $totalCur, $totalPrev, 'neutral'],
                ['Resolved', $resCur, $resPrev, 'positive'],
                ['Open', $openCur, $openPrev, 'negative'],
                [
                    'Avg Response',
                    $avgResp !== null ? floor($avgResp / 60) . 'h ' . round($avgResp % 60) . 'm' : '—',
                    null,
                    null,
                ],
                ['Resolution Rate', $rateCur !== null ? $rateCur . '%' : '—', $ratePrev, 'positive'],
            ];
        @endphp
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <div
                class="grid grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-zinc-200 dark:divide-zinc-700">
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

        {{-- Ticket Volume Chart --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">My Ticket Volume</h2>
            <div class="h-72" wire:ignore>
                <canvas id="agent-chart-volume"></canvas>
            </div>
            @if (collect($this->ticketVolumeChart['created'])->sum() === 0 &&
                    collect($this->ticketVolumeChart['resolved'])->sum() === 0)
                <p class="text-center text-sm text-zinc-400 dark:text-zinc-500 mt-4">No ticket data for this period</p>
            @endif
        </div>

        {{-- Three Supporting Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Status Distribution</h2>
                <div class="relative h-52 mx-auto max-w-[220px]">
                    <div class="absolute inset-0" wire:ignore>
                        <canvas id="agent-chart-status"></canvas>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <span
                            class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ array_sum($this->statusBreakdown['values']) }}</span>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 justify-center">
                    @foreach ($this->statusBreakdown['labels'] as $i => $lbl)
                        <span class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="w-2 h-2 rounded-full"
                                style="background-color: {{ $this->statusBreakdown['colors'][$i] }}"></span>
                            {{ $lbl }} ({{ $this->statusBreakdown['values'][$i] }})
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Priority Distribution</h2>
                <div class="h-52" wire:ignore>
                    <canvas id="agent-chart-priority"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Volume by Category</h2>
                <div class="h-52" wire:ignore>
                    <canvas id="agent-chart-category"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
