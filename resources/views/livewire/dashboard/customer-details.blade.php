<div>
    <x-flash-message></x-flash-message>

    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center gap-3 text-sm text-zinc-400 mb-4">
            <a href="{{ route('customers', ['company' => Auth::user()->company->slug]) }}" 
               class="hover:text-white transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Customers
            </a>
            <span>/</span>
            <span class="text-zinc-300">Customer Details</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-lg shadow-teal-500/20">
                    {{ substr($customer->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                        {{ $customer->name }}
                        @if($customer->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                Deactivated
                            </span>
                        @endif
                    </h1>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:{{ $customer->email }}" class="hover:text-teal-400 transition-colors">{{ $customer->email }}</a>
                        </div>
                        @if($customer->phone)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a href="tel:{{ $customer->phone }}" class="hover:text-teal-400 transition-colors">{{ $customer->phone }}</a>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Joined {{ $customer->created_at->format('M j, Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button wire:click="toggleStatus"
                        wire:confirm="Are you sure you want to {{ $customer->is_active ? 'deactivate' : 'activate' }} this customer?"
                        class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors flex items-center gap-2 {{ $customer->is_active ? 'border-red-500/20 text-red-400 hover:bg-red-500/10' : 'border-green-500/20 text-green-400 hover:bg-green-500/10' }}">
                    @if($customer->is_active)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Deactivate Customer
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Activate Customer
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-zinc-800 mb-6 flex gap-6">
        <button wire:click="setTab('tickets')" 
                class="pb-4 text-sm font-medium transition-colors relative {{ $activeTab === 'tickets' ? 'text-teal-400' : 'text-zinc-400 hover:text-zinc-200' }}">
            Tickets 
            <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs {{ $activeTab === 'tickets' ? 'bg-teal-500/20 text-teal-300' : 'bg-zinc-800 text-zinc-400' }}">
                {{ $customer->tickets_count }}
            </span>
            @if($activeTab === 'tickets')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-400 rounded-t-full"></div>
            @endif
        </button>
        <button wire:click="setTab('conversations')" 
                class="pb-4 text-sm font-medium transition-colors relative {{ $activeTab === 'conversations' ? 'text-teal-400' : 'text-zinc-400 hover:text-zinc-200' }}">
            Conversation History
            @if($activeTab === 'conversations')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-400 rounded-t-full"></div>
            @endif
        </button>
    </div>

    <!-- Tab Content -->
    <div>
        @if($activeTab === 'tickets')
            <!-- Tickets Table -->
            <div class="rounded-lg border border-zinc-800 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-zinc-900/50 border-b border-zinc-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Ticket ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Agent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @forelse ($this->tickets as $ticket)
                            <tr class="hover:bg-zinc-900/30 transition-colors">
                                <td class="px-4 py-3 text-sm text-zinc-300 font-mono">{{ $ticket->ticket_number }}</td>
                                <td class="px-4 py-3 text-sm text-white font-medium">{{ Str::limit($ticket->subject, 40) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $statusBg = match ($ticket->status) {
                                            'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                            'resolved' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                            'closed' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                            'in_progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                            default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ $statusBg }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $priorityBg = match ($ticket->priority) {
                                            'low' => 'text-green-400',
                                            'medium' => 'text-blue-400',
                                            'high' => 'text-orange-400',
                                            'urgent' => 'text-red-400',
                                            default => 'text-gray-400',
                                        };
                                    @endphp
                                    <span class="flex items-center gap-1.5 {{ $priorityBg }}">
                                        @if($ticket->priority === 'urgent')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        @elseif($ticket->priority === 'low')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        @endif
                                        <span class="text-xs font-medium">{{ ucfirst($ticket->priority) }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($ticket->user)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-white text-[10px] font-bold">
                                                {{ substr($ticket->user->name, 0, 1) }}
                                            </div>
                                            <span class="text-zinc-300">{{ $ticket->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-zinc-500 italic text-xs">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-400">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}" 
                                       wire:navigate
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-medium rounded-md transition-colors border border-zinc-700">
                                        View
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-4 text-zinc-400">This customer hasn't submitted any tickets yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'conversations')
            <!-- Conversation History -->
            <div class="space-y-6">
                @forelse ($this->conversations as $reply)
                    <div class="flex gap-4 p-5 rounded-lg border {{ $reply->user_id ? 'bg-zinc-800/50 border-zinc-800' : 'bg-teal-500/5 border-teal-500/10' }}">
                        @if ($reply->user_id)
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                                {{ substr($reply->user->name, 0, 1) }}
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-zinc-700 flex items-center justify-center text-zinc-300 font-bold flex-shrink-0">
                                {{ substr($customer->name, 0, 1) }}
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-white">
                                            {{ $reply->user_id ? $reply->user->name : $customer->name }}
                                        </span>
                                        @if ($reply->user_id)
                                            <span class="px-2 py-0.5 rounded bg-teal-500/20 text-teal-400 text-[10px] font-medium tracking-wide uppercase">
                                                Agent
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-zinc-700 text-zinc-300 text-[10px] font-medium tracking-wide uppercase">
                                                Customer
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-zinc-500 mt-0.5 flex items-center gap-2">
                                        <span>in <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $reply->ticket]) }}" class="hover:text-teal-400 underline">{{ $reply->ticket->ticket_number }}</a></span>
                                        <span>&bull;</span>
                                        <span>{{ $reply->created_at->format('M j AT g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-zinc-300 prose prose-invert prose-sm max-w-none">
                                {!! nl2br(e($reply->message)) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center rounded-lg border border-zinc-800 bg-zinc-900/50">
                        <svg class="mx-auto h-12 w-12 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        <p class="mt-4 text-zinc-400">No conversation history yet.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
