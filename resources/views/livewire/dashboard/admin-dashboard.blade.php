<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Admin Dashboard</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Welcome back, {{ Auth::user()->name }}</p>
    </div>

    <!-- KPI Cards (5 grid) -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <!-- Open Tickets -->
        <button type="button" class="text-left bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 transition-colors" x-on:click="$flux.modal('open-tickets-modal').show()">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Open Tickets</span>
                <div class="p-1.5 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->openTicketsCount }}</p>
        </button>

        <!-- Resolved Today -->
        <button type="button" class="text-left bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 transition-colors" x-on:click="$flux.modal('resolved-tickets-modal').show()">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Resolved Today</span>
                <div class="p-1.5 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->resolvedTodayCount }}</p>
        </button>

        <!-- Unassigned Tickets -->
        <button type="button" class="text-left bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 transition-colors" x-on:click="$flux.modal('unassigned-tickets-modal').show()">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Unassigned</span>
                <div class="p-1.5 bg-orange-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->unassignedTicketsCount }}</p>
        </button>

        <!-- Total Agents -->
        <button type="button" class="text-left bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 transition-colors" x-on:click="$flux.modal('total-agents-modal').show()">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Agents</span>
                <div class="p-1.5 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->totalAgentsCount }}</p>
        </button>

        <!-- SLA Breaches -->
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">SLA Breaches</span>
                <div class="p-1.5 bg-red-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-zinc-600">0</p>
            <div class="absolute inset-x-0 bottom-0 top-0 bg-white/60 dark:bg-zinc-900/60 flex items-center justify-center backdrop-blur-[1px]">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                    Coming soon
                </span>
            </div>
        </div>
    </div>

    <!-- Middle Section: 2/3 and 1/3 grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Left: Recent Tickets (2/3) -->
        <div class="lg:col-span-2 flex flex-col space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Recent Tickets</h2>
                <a href="{{ route('tickets', Auth::user()->company->slug) }}" class="text-sm text-teal-400 hover:text-teal-300 transition-colors">
                    View all &rarr;
                </a>
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden flex-1">
                @if($this->recentTickets->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No tickets yet</h3>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 text-xs uppercase font-medium border-b border-zinc-200 dark:border-zinc-800/80">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Ticket ID</th>
                                    <th class="px-6 py-3 font-medium">Subject</th>
                                    <th class="px-6 py-3 font-medium">Customer</th>
                                    <th class="px-6 py-3 font-medium">Assigned To</th>
                                    <th class="px-6 py-3 font-medium">Priority</th>
                                    <th class="px-6 py-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/10">
                                @foreach($this->recentTickets as $ticket)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors group cursor-pointer" onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                                        <td class="px-6 py-3.5 text-zinc-500 dark:text-zinc-400 font-mono">{{ $ticket->ticket_number }}</td>
                                        <td class="px-6 py-3.5 font-medium text-zinc-900 dark:text-zinc-100 min-w-[200px] truncate max-w-xs transition-colors group-hover:text-teal-400">{{ $ticket->subject }}</td>
                                        <td class="px-6 py-3.5 text-zinc-600 dark:text-zinc-300">{{ $ticket->customer_name }}</td>
                                        <td class="px-6 py-3.5">
                                            @if($ticket->assignedTo)
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-[10px] font-medium text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                                        {{ collect(explode(' ', $ticket->assignedTo->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span class="text-zinc-600 dark:text-zinc-300 text-sm">
                                                        @if($ticket->assignedTo->id === Auth::id())
                                                            You <span class="text-xs text-zinc-500">({{ $ticket->assignedTo->name }})</span>
                                                        @else
                                                            {{ $ticket->assignedTo->name }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-zinc-500 italic text-sm">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @php
                                                $priorityBg = match ($ticket->priority) {
                                                    'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                    'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                    'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                                    'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                    default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $priorityBg }}">
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @php
                                                $statusBg = match ($ticket->status) {
                                                    'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                    'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                                    'in_progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                    default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusBg }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Agent Activity (1/3) -->
        <div class="flex flex-col space-y-4 h-full">
            <h2 class="text-lg font-semibold text-white">Agent Activity</h2>
            <!-- Matches max height of Recent Tickets intuitively, using absolute positioning inside a flex grid -->
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl flex-1 relative min-h-[300px] lg:min-h-0">
                <div class="absolute inset-0 overflow-y-auto divide-y divide-zinc-800/10 custom-scrollbar">
                @if($this->agentsActivity->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-zinc-500">No agents found.</p>
                    </div>
                @else
                    @foreach($this->agentsActivity as $agent)
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="relative flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-sm font-medium text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700">
                                        {{ collect(explode(' ', $agent->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                    </div>
                                    <!-- Online dot -->
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-zinc-900 rounded-full"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        @if($agent->id === Auth::id())
                                            You <span class="text-xs text-zinc-500 font-normal">({{ $agent->name }})</span>
                                        @else
                                            {{ $agent->name }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-zinc-500 truncate">{{ $agent->email }}</p>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 w-32">
                                @php
                                    $activeCount = $agent->active_count ?? 0;
                                    $percentage = min(($activeCount / 10) * 100, 100);
                                    
                                    $barColor = match (true) {
                                        $percentage <= 40 => 'bg-green-500',
                                        $percentage <= 70 => 'bg-amber-500',
                                        $percentage <= 90 => 'bg-orange-500',
                                        default => 'bg-red-500',
                                    };

                                    $statusLabel = match (true) {
                                        $percentage <= 40 => 'Low',
                                        $percentage <= 70 => 'Medium',
                                        $percentage <= 90 => 'High',
                                        default => 'Overloaded',
                                    };
                                    
                                    $labelColor = $percentage > 90 ? 'text-red-500' : 'text-zinc-500 dark:text-zinc-400';
                                @endphp
                                
                                <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 mb-2 overflow-hidden">
                                    <div class="{{ $barColor }} h-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-medium uppercase tracking-wider {{ $labelColor }}">{{ $statusLabel }}</span>
                                    <span class="text-[10px] text-zinc-500">{{ $activeCount }} / 10</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent Activity -->
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Recent Activity</h2>
        
        <div class="bg-zinc-50 dark:bg-zinc-900/50  border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden p-1">
            @if($this->recentActivity->isEmpty())
                <div class="px-5 py-12 text-center">
                    <p class="text-sm text-zinc-500">No recent system activity.</p>
                </div>
            @else
                <div class="divide-y divide-zinc-800/10">
                    @foreach($this->recentActivity as $log)
                        <div class="px-5 py-3 flex items-center gap-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/20 transition-colors">
                            <div class="flex-shrink-0">
                                @if($log->action === 'assigned')
                                    <div class="w-8 h-8 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                @elseif($log->action === 'priority_changed')
                                    <div class="w-8 h-8 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                    @if($log->user)
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                            @if($log->user->id === Auth::id())
                                                You <span class="text-xs text-zinc-400 font-normal">({{ $log->user->name }})</span>
                                            @else
                                                {{ $log->user->name }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">System</span>
                                    @endif
                                    <span class="text-zinc-500 dark:text-zinc-400">
                                        {{ strtolower($log->description) }}
                                    </span>
                                </p>
                                @if($log->ticket)
                                    <p class="text-xs text-zinc-500 mt-0.5 max-w-2xl truncate">
                                        <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $log->ticket->ticket_number]) }}" class="hover:text-teal-400 transition-colors">
                                            {{ $log->ticket->ticket_number }} &mdash; {{ $log->ticket->subject }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                            
                            <div class="flex-shrink-0 text-right">
                                <span class="text-xs text-zinc-500">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Modals for KPI Lists -->
    <!-- Open Tickets Modal -->
    <flux:modal name="open-tickets-modal" variant="flyout" class="max-w-4xl max-h-[85vh] flex flex-col">
        <flux:heading size="lg" class="mb-4">Open Tickets</flux:heading>
        <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
            @if($this->openTicketsList->isEmpty())
                <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No open tickets at this time.</p>
            @else
                <div class="divide-y divide-zinc-800/10">
                    @foreach($this->openTicketsList as $ticket)
                        <div class="py-3 flex items-center justify-between group cursor-pointer" onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-teal-400 transition-colors">{{ $ticket->subject }}</p>
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}</p>
                            </div>
                            <flux:badge variant="primary" size="sm">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Resolved Today Modal -->
    <flux:modal name="resolved-tickets-modal" variant="flyout" class="max-w-4xl max-h-[85vh] flex flex-col">
        <flux:heading size="lg" class="mb-4">Resolved Today</flux:heading>
        <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
            @if($this->resolvedTodayList->isEmpty())
                <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No tickets resolved today.</p>
            @else
                <div class="divide-y divide-zinc-800/10">
                    @foreach($this->resolvedTodayList as $ticket)
                        <div class="py-3 flex items-center justify-between group cursor-pointer" onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-teal-400 transition-colors">{{ $ticket->subject }}</p>
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}</p>
                            </div>
                            <flux:badge variant="success" size="sm">Resolved</flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Unassigned Tickets Modal -->
    <flux:modal name="unassigned-tickets-modal" variant="flyout" class="max-w-4xl max-h-[85vh] flex flex-col">
        <flux:heading size="lg" class="mb-4">Unassigned Tickets</flux:heading>
        <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
            @if($this->unassignedTicketsList->isEmpty())
                <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No unassigned tickets pending.</p>
            @else
                <div class="divide-y divide-zinc-800/10">
                    @foreach($this->unassignedTicketsList as $ticket)
                        <div class="py-3 flex items-center justify-between group cursor-pointer" onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-teal-400 transition-colors">{{ $ticket->subject }}</p>
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $ticket->ticket_number }} &middot; {{ $ticket->customer_name }}</p>
                            </div>
                            <flux:badge variant="warning" size="sm">{{ ucfirst($ticket->priority) }}</flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Total Agents Modal -->
    <flux:modal name="total-agents-modal" variant="flyout" class="max-w-2xl max-h-[85vh] flex flex-col">
        <flux:heading size="lg" class="mb-4">All Agents</flux:heading>
        <div class="overflow-y-auto flex-1 custom-scrollbar -mx-6 px-6">
            @if($this->totalAgentsList->isEmpty())
                <p class="text-zinc-500 dark:text-zinc-400 py-4 text-center">No active agents.</p>
            @else
                <div class="divide-y divide-zinc-800/10">
                    @foreach($this->totalAgentsList as $agent)
                        <div class="py-4 flex items-center justify-between group cursor-pointer" onclick="window.location='{{ route('operators', ['company' => Auth::user()->company->slug]) }}'">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-sm font-medium text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700">
                                        {{ collect(explode(' ', $agent->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                    </div>
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-[var(--color-zinc-900)] rounded-full"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white group-hover:text-teal-400 transition-colors">
                                        @if($agent->id === Auth::id())
                                            You <span class="text-xs text-zinc-500 font-normal">({{ $agent->name }})</span>
                                        @else
                                            {{ $agent->name }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $agent->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $agent->open_tickets_count }}</p>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-wider">Open</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:modal>
</div>
