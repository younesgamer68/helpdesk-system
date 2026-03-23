<div>
    <x-ui.flash-message />

    {{-- Operator Ticket View Tabs --}}
    @if (Auth::user()->isOperator())
        <div
            class="mb-4 flex gap-1 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-fit border border-zinc-200 dark:border-zinc-700">
            <button wire:click="setTicketView('mine')"
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $ticketView === 'mine' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                My Tickets
            </button>
            <button wire:click="setTicketView('team')"
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $ticketView === 'team' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                My Team
            </button>
            <button wire:click="setTicketView('all')"
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $ticketView === 'all' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                All Tickets
            </button>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="mb-4 space-y-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="relative w-full xl:max-w-sm">
                <svg class="pointer-events-none absolute left-0 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search tickets"
                    class="w-full border-0 border-b border-zinc-200 bg-transparent py-2 pl-6 pr-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500">
            </div>

            <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                <flux:dropdown>
                    <flux:button variant="outline"
                        class="w-[140px] justify-between px-3 py-2 text-xs font-normal text-zinc-600 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700"
                        icon-trailing="chevron-down">
                        {{ $statusFilter ? ucfirst(str_replace('_', ' ', $statusFilter)) : 'All Statuses' }}
                    </flux:button>
                    <flux:menu class="w-[140px]">
                        <flux:menu.radio.group wire:model.live="statusFilter">
                            <flux:menu.radio value=""
                                class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                All Statuses</flux:menu.radio>
                            @foreach ($statuses as $status)
                                <flux:menu.radio value="{{ $status }}"
                                    class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}</flux:menu.radio>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                <flux:dropdown>
                    <flux:button variant="outline"
                        class="w-[140px] justify-between px-3 py-2 text-xs font-normal text-zinc-600 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700"
                        icon-trailing="chevron-down">
                        {{ $priorityFilter ? ucfirst($priorityFilter) : 'All Priorities' }}
                    </flux:button>
                    <flux:menu class="w-[140px]">
                        <flux:menu.radio.group wire:model.live="priorityFilter">
                            <flux:menu.radio value=""
                                class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                All Priorities</flux:menu.radio>
                            @foreach ($priorities as $priority)
                                <flux:menu.radio value="{{ $priority }}"
                                    class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                    {{ ucfirst($priority) }}</flux:menu.radio>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                @if (Auth::user()->isAdmin())
                    <flux:dropdown>
                        <flux:button variant="outline"
                            class="w-[150px] justify-between px-3 py-2 text-xs font-normal text-zinc-600 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700"
                            icon-trailing="chevron-down">
                            {{ $categoryFilter ? $this->categoriesFlat->where('id', $categoryFilter)->first()?->name ?? 'All Categories' : 'All Categories' }}
                        </flux:button>
                        <flux:menu class="w-[150px]">
                            <flux:menu.radio.group wire:model.live="categoryFilter">
                                <flux:menu.radio value=""
                                    class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                    All Categories</flux:menu.radio>
                                @foreach ($this->categoriesFlat as $category)
                                    <flux:menu.radio value="{{ $category->id }}"
                                        class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                        {{ $category->name }}</flux:menu.radio>
                                @endforeach
                            </flux:menu.radio.group>
                        </flux:menu>
                    </flux:dropdown>

                    <flux:dropdown>
                        <flux:button variant="outline"
                            class="w-[140px] justify-between px-3 py-2 text-xs font-normal text-zinc-600 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700"
                            icon-trailing="chevron-down">
                            {{ $teamFilter ? $this->teamsForFilter->where('id', $teamFilter)->first()?->name ?? 'All Teams' : 'All Teams' }}
                        </flux:button>
                        <flux:menu class="w-[140px]">
                            <flux:menu.radio.group wire:model.live="teamFilter">
                                <flux:menu.radio value=""
                                    class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                    All Teams</flux:menu.radio>
                                @foreach ($this->teamsForFilter as $team)
                                    <flux:menu.radio value="{{ $team->id }}"
                                        class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                        {{ $team->name }}</flux:menu.radio>
                                @endforeach
                            </flux:menu.radio.group>
                        </flux:menu>
                    </flux:dropdown>
                @endif

                <input wire:model.live="dateFrom" type="date"
                    class="rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-600 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">

                <input wire:model.live="dateTo" type="date"
                    class="rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-600 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">

                @if (Auth::user()->isAdmin())
                    <div class="ml-1 flex items-center">
                        <flux:switch wire:model.live="showDeletedOnly" label="Show Deleted" />
                    </div>
                @endif
            </div>
        </div>

        @if ($this->hasActiveFilters)
            <div class="flex items-center gap-3 text-xs">
                <button wire:click="$set('showSaveViewModal', true)"
                    class="font-medium text-amber-600 transition-colors hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300">
                    Save current view
                </button>
                <button wire:click="clearFilters"
                    class="text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    Clear all filters
                </button>
            </div>
        @endif

        <!-- Saved Filters (Presets) -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-xs font-medium uppercase tracking-wide text-zinc-400">Saved views :</span>
            @foreach ($this->savedViews as $view)
                <div class="relative">
                    <button wire:click="applyPreset('{{ $view->id }}')"
                        class="rounded-full border border-zinc-200 bg-zinc-100 px-3 py-1 pr-7 text-xs font-medium text-zinc-600 transition-colors hover:border-zinc-300 hover:bg-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        {{ $view->name }}
                    </button>
                    <button wire:click="deleteSavedView({{ $view->id }})"
                        wire:confirm="Are you sure you want to delete this view?"
                        class="absolute right-1 top-1/2 flex -translate-y-1/2 rounded-full p-0.5 text-zinc-400 transition-colors hover:bg-zinc-300/60 hover:text-red-500 dark:hover:bg-zinc-600/60">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Specialty Indicator for Operators -->
    @php
        $operatorSpecialties = Auth::user()->isAdmin()
            ? collect()
            : Auth::user()->categories->pluck('name')->filter()->values();
    @endphp
    @if ($operatorSpecialties->isNotEmpty())
        <div class="mb-4">
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-500/10 text-purple-600 dark:text-purple-400 text-sm font-medium rounded-full border border-purple-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ $operatorSpecialties->count() === 1 ? 'Your Specialty:' : 'Your Specialties:' }}
                {{ $operatorSpecialties->implode(', ') }}
            </span>
        </div>
    @endif

    <!-- Bulk Actions Bar -->
    @if (!empty($selectedTickets))
        <div
            class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ count($selectedTickets) }}
                    tickets selected</span>
                <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                <!-- Bulk Status -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 hover:text-teal-500 transition-colors">
                        Set Status
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute left-0 mt-2 w-40 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl z-20 overflow-hidden">
                        @foreach ($statuses as $status)
                            <button wire:click="bulkSetStatus('{{ $status }}')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                <!-- Bulk Priority -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 hover:text-teal-500 transition-colors">
                        Set Priority
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute left-0 mt-2 w-40 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl z-20 overflow-hidden">
                        @foreach ($priorities as $priority)
                            <button wire:click="bulkSetPriority('{{ $priority }}')" @click="open = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                                {{ ucfirst($priority) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if (Auth::user()->isAdmin())
                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                    <!-- Bulk Assign -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 hover:text-teal-500 transition-colors">
                            Assign To
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                            class="absolute left-0 mt-2 w-48 max-h-60 overflow-y-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl z-20 overflow-hidden">
                            @foreach ($this->agents as $agent)
                                <button wire:click="bulkAssignAgent({{ $agent->id }})" @click="open = false"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                                    {{ $agent->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                    <!-- Bulk Assign Team -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 hover:text-teal-500 transition-colors">
                            Assign Team
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                            class="absolute left-0 mt-2 w-44 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl z-20 overflow-hidden">
                            @foreach ($this->teamsForFilter as $team)
                                <button wire:click="bulkAssignTeam({{ $team->id }})" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    {{ $team->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                @if (Auth::user()->isAdmin())
                    <button wire:click="deleteSelectedTickets"
                        wire:confirm="Are you sure you want to delete {{ count($selectedTickets) }} tickets?"
                        class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 transition-colors px-2 py-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-700/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Selected
                    </button>
                @endif
            </div>
            <button wire:click="$set('selectedTickets', [])"
                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                Cancel selection
            </button>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                    @if (Auth::user()->isAdmin())
                        <th class="w-12 px-3 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="w-4 h-4 bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-teal-500 rounded focus:ring-teal-500 focus:ring-offset-white dark:focus:ring-offset-zinc-900">
                        </th>
                    @endif
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Subject
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Customer
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Assigned To
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Status
                    </th>
                    <th class="w-14 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->tickets as $ticket)
                    @php
                        $priorityBorder = match ($ticket->priority) {
                            'urgent' => 'border-l-red-500',
                            'high' => 'border-l-orange-400',
                            'medium' => 'border-l-blue-400',
                            'low' => 'border-l-zinc-300 dark:border-l-zinc-600',
                            default => 'border-l-zinc-300 dark:border-l-zinc-600',
                        };

                        $statusBadge = match ($ticket->status) {
                            'open'
                                => 'border-zinc-300 bg-transparent text-zinc-600 dark:border-zinc-700 dark:text-zinc-300',
                            'in_progress'
                                => 'border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-300',
                            'pending'
                                => 'border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300',
                            'resolved'
                                => 'border-green-200 bg-green-50 text-green-600 dark:border-green-900 dark:bg-green-950/30 dark:text-green-300',
                            'closed'
                                => 'border-zinc-200 bg-zinc-100 text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400',
                            default
                                => 'border-zinc-300 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300',
                        };
                    @endphp
                    <tr class="cursor-pointer border-b border-l-3 border-zinc-100 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-900 {{ $priorityBorder }} {{ in_array($ticket->id, $selectedTickets) ? 'bg-teal-500/5' : '' }}"
                        wire:key="{{ $ticket->id }}"
                        onclick="Livewire.navigate('{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}')">
                        @if (Auth::user()->isAdmin())
                            <td class="px-3 py-4 text-left" wire:click.stop>
                                <input type="checkbox" wire:model.live="selectedTickets" value="{{ $ticket->id }}"
                                    class="w-4 h-4 bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-teal-500 rounded focus:ring-teal-500 focus:ring-offset-white dark:focus:ring-offset-zinc-900">
                            </td>
                        @endif
                        <td class="px-4 py-4 align-middle">
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                {{ Str::limit($ticket->subject, 65) }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400">
                                {{ $ticket->category->name ?? 'No category' }} · {{ $ticket->ticket_number }}
                            </p>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $ticket->customer_name }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                @if ($ticket->assignedTo)
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                                        {{ substr($ticket->assignedTo->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                        @if ($ticket->assignedTo->id === Auth::id())
                                            You <span
                                                class="text-xs text-zinc-400">({{ $ticket->assignedTo->name }})</span>
                                        @else
                                            {{ $ticket->assignedTo->name }}
                                        @endif
                                    </span>
                                    @if ($ticket->team)
                                        <span class="block text-xs text-zinc-400 mt-0.5">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle"
                                                style="background-color: {{ $ticket->team->color ?? '#71717a' }}"></span>
                                            {{ $ticket->team->name }}
                                        </span>
                                    @endif
                                @elseif ($ticketView === 'team')
                                    <button wire:click.stop="takeTicket({{ $ticket->id }})"
                                        wire:loading.attr="disabled" wire:target="takeTicket({{ $ticket->id }})"
                                        class="px-3 py-1.5 text-xs font-medium text-teal-600 dark:text-teal-400 border border-teal-500/30 rounded-lg hover:bg-teal-500/10 transition-colors disabled:opacity-50">
                                        <span wire:loading.remove wire:target="takeTicket({{ $ticket->id }})">Take
                                            this ticket</span>
                                        <span wire:loading
                                            wire:target="takeTicket({{ $ticket->id }})">Taking...</span>
                                    </button>
                                @else
                                    @if ($ticket->team)
                                        <span class="text-sm text-amber-600 dark:text-amber-400">Unassigned</span>
                                        <span class="block text-xs text-zinc-400 mt-0.5">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle"
                                                style="background-color: {{ $ticket->team->color ?? '#71717a' }}"></span>
                                            {{ $ticket->team->name }}
                                        </span>
                                    @else
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">
                                            U
                                        </div>
                                        <span class="text-sm text-zinc-400">Unassigned</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="inline-flex items-center">
                                <span
                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs {{ $statusBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                                @if ($ticket->sla_status === 'breached')
                                    <span class="group relative ml-2 inline-flex" aria-label="SLA Breached">
                                        <span class="cursor-help text-sm leading-none text-red-400">⚠</span>
                                        <span
                                            class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden -translate-x-1/2 whitespace-nowrap rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 shadow-sm group-hover:block dark:border-red-900/70 dark:bg-red-950/50 dark:text-red-300">
                                            SLA breached: response deadline exceeded
                                        </span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm" wire:click.stop>
                            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                <button @click="open = !open"
                                    class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                                    <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 rounded-lg shadow-xl border border-zinc-200 dark:border-zinc-700 z-10"
                                    style="display: none;">
                                    <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}"
                                        wire:navigate
                                        class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View details
                                    </a>
                                    @if (Auth::user()->isAdmin())
                                        @if ($ticket->trashed())
                                            <button wire:click="restoreTicket({{ $ticket->id }})"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-teal-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-teal-300 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Restore
                                            </button>
                                        @else
                                            <button @click="open = false; confirmDeletion($wire, {{ $ticket->id }})"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-300 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isAdmin() ? 6 : 5 }}" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            @if ($ticketView === 'team' && Auth::user()->isOperator() && Auth::user()->teams()->count() === 0)
                                <p class="mt-4 text-zinc-500 dark:text-zinc-400">You are not part of any team yet.</p>
                            @else
                                <p class="mt-4 text-zinc-500 dark:text-zinc-400">No tickets found. Try adjusting your
                                    filters.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
        {{ $this->tickets->withPath(route('tickets', ['company' => Auth::user()->company->slug]))->links('pagination.tickets-compact') }}
    </div>


    <!-- Create Ticket Modal -->
    @if ($showCreateModal)
        <flux:modal wire:model="showCreateModal" class="md:w-[800px]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Create New Ticket</flux:heading>
                    <flux:subheading>Fill in the details to create a support ticket</flux:subheading>
                </div>

                <form wire:submit="createTicket" class="space-y-6">
                    <!-- Draft Indicator -->
                    @if ($this->hasFormData)
                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-3 flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-amber-400 font-medium">Draft saved</p>
                                <p class="text-xs text-amber-300/80 mt-0.5">Your changes are automatically preserved
                                </p>
                            </div>
                            <button type="button" wire:click="clearForm"
                                class="text-xs text-amber-400 hover:text-amber-300 underline">
                                Clear all
                            </button>
                        </div>
                    @endif

                    <!-- Customer Information Section -->
                    <div>
                        <flux:separator class="mb-6" />
                        <flux:heading size="md" class="mb-4">Customer Information</flux:heading>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label>Customer Name</flux:label>
                                    <flux:input wire:model.blur="customer_name" placeholder="John Doe" />
                                    <flux:error name="customer_name" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>Customer Email</flux:label>
                                <flux:input wire:model.blur="customer_email" type="email"
                                    placeholder="john@example.com" />
                                <flux:error name="customer_email" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Customer Phone</flux:label>
                                <flux:input wire:model.blur="customer_phone" placeholder="+1 (555) 123-4567" />
                                <flux:error name="customer_phone" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Ticket Details Section -->
                    <div>
                        <flux:separator class="mb-6" />
                        <flux:heading size="md" class="mb-4">Ticket Details</flux:heading>

                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Subject</flux:label>
                                <flux:input wire:model.blur="subject" placeholder="Brief description of the issue" />
                                <flux:error name="subject" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Description</flux:label>
                                <flux:textarea wire:model.blur="description" rows="5"
                                    placeholder="Detailed description of the issue..." />
                                <flux:error name="description" />
                            </flux:field>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>Priority</flux:label>
                                    <flux:select wire:model.live="priority">
                                        <flux:select.option value="low">Low</flux:select.option>
                                        <flux:select.option value="medium">Medium</flux:select.option>
                                        <flux:select.option value="high">High</flux:select.option>
                                        <flux:select.option value="urgent">Urgent</flux:select.option>
                                    </flux:select>
                                    <flux:error name="priority" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Status</flux:label>
                                    <flux:select wire:model.live="status">
                                        <flux:select.option value="pending">Pending</flux:select.option>
                                        <flux:select.option value="open">Open</flux:select.option>
                                        <flux:select.option value="in_progress">In Progress</flux:select.option>
                                        <flux:select.option value="resolved">Resolved</flux:select.option>
                                        <flux:select.option value="closed">Closed</flux:select.option>
                                    </flux:select>
                                    <flux:error name="status" />
                                </flux:field>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div>
                        <flux:separator class="mb-6" />
                        <flux:heading size="md" class="mb-4">Assignment & Categorization</flux:heading>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if (Auth::user()->isAdmin())
                                <flux:field>
                                    <flux:label>Team</flux:label>
                                    <flux:select wire:model.live="createTeamId"
                                        placeholder="No Team (assign to any agent)">
                                        <flux:select.option value="">No Team</flux:select.option>
                                        @foreach ($this->teamsForCreate as $team)
                                            <flux:select.option value="{{ $team->id }}">{{ $team->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>

                                <flux:field>
                                    <flux:label>Assign To</flux:label>
                                    <flux:select wire:model.live="assigned_to" placeholder="Unassigned">
                                        <flux:select.option value="">Unassigned</flux:select.option>
                                        @foreach ($this->agents as $agent)
                                            <flux:select.option value="{{ $agent->id }}">{{ $agent->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <p class="text-xs text-zinc-400 italic mt-1">
                                        @if ($createTeamId)
                                            Showing {{ count($this->agents) }} agents in this team
                                        @else
                                            Showing all agents
                                        @endif
                                    </p>
                                    <flux:error name="assigned_to" />
                                </flux:field>
                            @endif

                            <flux:field>
                                <flux:label>Category</flux:label>
                                <select wire:model.live="category_id"
                                    class="block w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-zinc-400 dark:focus:border-zinc-500 focus:outline-none focus:ring-0">
                                    <option value="">No Category</option>
                                    @foreach ($this->categories as $parentCategory)
                                        @if ($parentCategory->children->isNotEmpty())
                                            <optgroup label="{{ $parentCategory->name }}">
                                                @foreach ($parentCategory->children as $child)
                                                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <flux:error name="category_id" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Info Note -->
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-blue-400 font-medium">Auto-verified Ticket</p>
                                <p class="text-xs text-blue-300/80 mt-1">Tickets created by staff are automatically
                                    verified and don't require email confirmation.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <flux:button type="button" wire:click="attemptCloseCreateModal" variant="ghost"
                            class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Create Ticket
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>

        <!-- Discard Confirmation Dialog -->
        <flux:modal wire:model="showDiscardConfirmation" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Discard changes?</flux:heading>
                    <flux:subheading>You have unsaved changes. If you close now, your progress will be lost.
                    </flux:subheading>
                </div>

                <div class="flex gap-3">
                    <flux:button wire:click="cancelDiscard" variant="ghost" class="flex-1">
                        Keep editing
                    </flux:button>
                    <flux:button wire:click="confirmDiscard" variant="danger" class="flex-1">
                        Discard
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Save View Modal -->
    <flux:modal name="save-view" wire:model="showSaveViewModal" class="min-w-[20rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Save Current View</flux:heading>
                <flux:subheading>Give your filter combination a name to use it later.</flux:subheading>
            </div>

            <flux:input wire:model="customViewName" label="View Name" placeholder="e.g. My Urgent Tickets" />

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary" wire:click="saveCustomView">Save View</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
