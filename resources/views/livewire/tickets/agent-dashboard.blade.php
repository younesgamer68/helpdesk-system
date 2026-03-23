<div class="animate-enter">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Welcome back, {{ Auth::user()->name }}</p>
        </div>
        <button type="button" wire:click="toggleAvailability"
            class="flex items-center gap-2 px-3 py-1.5 rounded-full border transition-colors {{ Auth::user()->is_available ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-500' }}">
            <span
                class="w-2 h-2 rounded-full {{ Auth::user()->is_available ? 'bg-green-500 animate-pulse' : 'bg-zinc-400' }}"></span>
            <span class="text-xs font-medium">{{ Auth::user()->is_available ? 'Available' : 'Unavailable' }}</span>
        </button>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-2">
        <button type="button" wire:click="loadModal('open-tickets')"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-left transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] hover:shadow-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/80">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Open
                    Tickets</span>
                <div class="p-1.5 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->openTicketsCount }}</p>
        </button>

        <button type="button" wire:click="loadModal('resolved-tickets')"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-left transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] hover:shadow-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/80">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Resolved
                    Today</span>
                <div class="p-1.5 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->resolvedTodayCount }}</p>
        </button>

        <button type="button" wire:click="loadModal('pending-tickets')"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-left transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] hover:shadow-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/80">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Pending
                    Reply</span>
                <div class="p-1.5 bg-yellow-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->pendingReplyCount }}</p>
        </button>

        <a href="{{ route('notifications', Auth::user()->company->slug) }}" wire:navigate
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-left transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] hover:shadow-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Unread
                    Notifications</span>
                <div class="p-1.5 bg-emerald-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->unreadNotificationsCount }}</p>
        </a>

        <button type="button" wire:click="loadModal('sla-breached')"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-left transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] hover:shadow-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/80">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">SLA
                    Breached</span>
                <div class="p-1.5 bg-red-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-red-500 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
            </div>
            <p
                class="text-3xl font-bold {{ $this->slaBreachedCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                {{ $this->slaBreachedCount }}</p>
        </button>
    </div>



    <!-- Two Column Layout -->
    <div class="grid lg:grid-cols-5 gap-6">
        <!-- Left Column (3/5) -->
        <div class="lg:col-span-3 space-y-6">
            <!-- My Tickets -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">My Tickets</h2>
                    <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
                        class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">
                        View all →
                    </a>
                </div>

                @if ($this->myTickets->isNotEmpty())
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($this->myTickets as $ticket)
                            <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}"
                                wire:navigate
                                class="animate-enter flex items-center gap-4 px-5 py-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group"
                                style="animation-delay: {{ $loop->index * 50 }}ms; animation-fill-mode: both;">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="text-xs text-zinc-500 font-mono">{{ $ticket->ticket_number }}</span>
                                        @php
                                            $priorityBg = match ($ticket->priority) {
                                                'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                                'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium border {{ $priorityBg }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                        @php
                                            $statusBg = match ($ticket->status) {
                                                'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                                'in_progress'
                                                    => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium border {{ $statusBg }}">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-sm text-zinc-700 dark:text-zinc-200 truncate group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                        {{ $ticket->subject }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ $ticket->customer_name }}</p>
                                </div>
                                <span
                                    class="text-[11px] text-zinc-500 dark:text-zinc-400 whitespace-nowrap">{{ $ticket->updated_at->diffForHumans() }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">You have no open tickets 🎉</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column (2/5) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Unassigned Tickets -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Unassigned Tickets</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Pick up a ticket</p>
                </div>

                @if ($this->unassignedTickets->isNotEmpty())
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($this->unassignedTickets as $ticket)
                            <div class="animate-enter px-5 py-3.5"
                                style="animation-delay: {{ $loop->index * 50 }}ms; animation-fill-mode: both;">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span
                                                class="text-xs text-zinc-500 font-mono">{{ $ticket->ticket_number }}</span>
                                            @php
                                                $uPriorityBg = match ($ticket->priority) {
                                                    'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                    'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                    'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                                    'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                    default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium border {{ $uPriorityBg }}">
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-zinc-700 dark:text-zinc-200 truncate">
                                            {{ $ticket->subject }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            {{ $ticket->customer_name }} · {{ $ticket->category->name ?? 'N/A' }}</p>
                                    </div>
                                    <button wire:click="assignToMe({{ $ticket->id }})" wire:loading.attr="disabled"
                                        wire:target="assignToMe({{ $ticket->id }})"
                                        class="flex-shrink-0 mt-1 px-3 py-1.5 text-xs font-medium text-emerald-400 border border-emerald-500/30 rounded-lg hover:bg-emerald-500/10 transition-colors disabled:opacity-50">
                                        <span wire:loading.remove wire:target="assignToMe({{ $ticket->id }})">Assign
                                            to me</span>
                                        <span wire:loading
                                            wire:target="assignToMe({{ $ticket->id }})">Assigning…</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-10 text-center">
                        @if (Auth::user()->categories()->count() === 0)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Set your speciality categories in your
                                profile to see relevant unassigned tickets</p>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No unassigned tickets in your
                                speciality categories right now</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Recent Activity -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Recent Activity</h2>
                    <a href="{{ route('notifications', Auth::user()->company->slug) }}" wire:navigate
                        class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">
                        View all →
                    </a>
                </div>

                @if ($this->recentNotifications->isNotEmpty())
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($this->recentNotifications as $notification)
                            <div class="animate-enter flex items-start gap-3 px-5 py-3.5"
                                style="animation-delay: {{ $loop->index * 50 }}ms; animation-fill-mode: both;">
                                @if (is_null($notification->read_at))
                                    <div class="w-2 h-2 mt-1.5 rounded-full bg-emerald-400 flex-shrink-0"></div>
                                @else
                                    <div class="w-2 h-2 mt-1.5 rounded-full flex-shrink-0"></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm {{ is_null($notification->read_at) ? 'font-medium text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 dark:text-zinc-300' }}">
                                        {{ $notification->data['message'] ?? 'Notification' }}
                                    </p>
                                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- My Team's Tickets --}}
    @if (Auth::user()->teams()->count() > 0)
        <div
            class="mt-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">My Team's Tickets</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Recent tickets from your team</p>
                </div>
            </div>

            @if ($this->teamTickets->isNotEmpty())
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach ($this->teamTickets as $ticket)
                        <div class="animate-enter flex items-center justify-between gap-4 px-5 py-3.5"
                            style="animation-delay: {{ $loop->index * 50 }}ms; animation-fill-mode: both;">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs text-zinc-500 font-mono">{{ $ticket->ticket_number }}</span>
                                    @php
                                        $tStatusBg = match ($ticket->status) {
                                            'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                            'in_progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                            default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium border {{ $tStatusBg }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </div>
                                <p class="text-sm text-zinc-700 dark:text-zinc-200 truncate">{{ $ticket->subject }}
                                </p>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    {{ $ticket->customer_name }}
                                    @if ($ticket->assignedTo)
                                        · Assigned to {{ $ticket->assignedTo->name }}
                                    @else
                                        · <span class="text-amber-500">Unassigned</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if (!$ticket->assigned_to)
                                    <button wire:click="takeTicket({{ $ticket->id }})" wire:loading.attr="disabled"
                                        wire:target="takeTicket({{ $ticket->id }})"
                                        class="px-3 py-1.5 text-xs font-medium text-teal-600 dark:text-teal-400 border border-teal-500/30 rounded-lg hover:bg-teal-500/10 transition-colors disabled:opacity-50">
                                        <span wire:loading.remove
                                            wire:target="takeTicket({{ $ticket->id }})">Take</span>
                                        <span wire:loading wire:target="takeTicket({{ $ticket->id }})">...</span>
                                    </button>
                                @endif
                                <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}"
                                    wire:navigate
                                    class="px-3 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                    View
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-10 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No team tickets at the moment</p>
                </div>
            @endif
        </div>
    @endif

    {{-- You were mentioned --}}
    @if ($this->unreadMentions->isNotEmpty())
        <div
            class="mt-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">You were mentioned</h2>
                    <span
                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        {{ $this->unreadMentions->count() }}
                    </span>
                </div>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @foreach ($this->unreadMentions as $mention)
                    <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $mention->ticket->ticket_number]) }}"
                        wire:navigate wire:click="markMentionRead({{ $mention->id }})"
                        class="animate-enter flex items-center justify-between gap-4 px-5 py-3.5 border-l-4 border-amber-400 hover:bg-amber-50/50 dark:hover:bg-amber-900/10 transition-colors"
                        style="animation-delay: {{ $loop->index * 50 }}ms; animation-fill-mode: both;">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                {{ $mention->ticket->subject }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                by {{ $mention->mentionedByUser->name }}
                            </p>
                        </div>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $mention->created_at->diffForHumans() }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- KPI Modal (only rendered when a KPI card is clicked) -->
    @if ($activeModal)
        <flux:modal name="agent-dashboard-kpi-modal" flyout wire:key="agent-dashboard-kpi-modal-{{ $activeModal }}"
            wire:close="closeModal" wire:cancel="closeModal" x-init="$nextTick(() => {
                const _s = window.scrollY;
                $flux.modal('agent-dashboard-kpi-modal').show();
                requestAnimationFrame(() => window.scrollTo({ top: _s, behavior: 'instant' }));
            })"
            class="max-w-4xl max-h-[85vh] flex flex-col">
            @switch($activeModal)
                @case('open-tickets')
                    <flux:heading size="lg" class="mb-4">Open Tickets</flux:heading>
                    <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
                        @if ($this->openTicketsList->isEmpty())
                            <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No open tickets at this time.</p>
                        @else
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach ($this->openTicketsList as $ticket)
                                    <div class="py-3 flex items-center justify-between group cursor-pointer"
                                        onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                                        <div>
                                            <p
                                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ $ticket->subject }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                {{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}</p>
                                        </div>
                                        <flux:badge variant="primary" size="sm">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @break

                @case('resolved-tickets')
                    <flux:heading size="lg" class="mb-4">Resolved Today</flux:heading>
                    <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
                        @if ($this->resolvedTodayList->isEmpty())
                            <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No tickets resolved today.</p>
                        @else
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach ($this->resolvedTodayList as $ticket)
                                    <div class="py-3 flex items-center justify-between group cursor-pointer"
                                        onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                                        <div>
                                            <p
                                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ $ticket->subject }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                {{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}</p>
                                        </div>
                                        <flux:badge variant="success" size="sm">Resolved</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @break

                @case('pending-tickets')
                    <flux:heading size="lg" class="mb-4">Pending Reply</flux:heading>
                    <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
                        @if ($this->pendingTicketsList->isEmpty())
                            <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No tickets pending reply.</p>
                        @else
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach ($this->pendingTicketsList as $ticket)
                                    <div class="py-3 flex items-center justify-between group cursor-pointer"
                                        onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                                        <div>
                                            <p
                                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ $ticket->subject }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                {{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}</p>
                                        </div>
                                        <flux:badge variant="warning" size="sm">Pending</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @break

                @case('sla-breached')
                    <flux:heading size="lg" class="mb-4">SLA Breached Tickets</flux:heading>
                    <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
                        @if ($this->slaBreachedList->isEmpty())
                            <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No SLA breaches — great work!</p>
                        @else
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach ($this->slaBreachedList as $ticket)
                                    <div class="py-3 flex items-center justify-between group cursor-pointer"
                                        onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                                        <div>
                                            <p
                                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ $ticket->subject }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                {{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}
                                                @if ($ticket->due_time)
                                                    &middot; Due {{ $ticket->due_time->diffForHumans() }}
                                                @endif
                                            </p>
                                        </div>
                                        <flux:badge color="red" size="sm">Breached</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @break

            @endswitch
        </flux:modal>
    @endif
</div>
