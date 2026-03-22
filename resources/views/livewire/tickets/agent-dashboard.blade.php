<div class="animate-enter">
    {{-- Greeting Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $this->greeting }}, {{ Auth::user()->name }}
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $this->subtitle }}</p>
    </div>

    {{-- Filter Pills: Urgent | Needs Reply | Mentions | Unassigned | All Mine --}}
    <div class="flex items-center gap-2 mb-4 flex-wrap">
        <button wire:click="setPill('urgent')"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ $activePill === 'urgent'
                      ? 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/30'
                      : ($this->urgentCount > 0
                          ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700'
                          : 'bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 dark:text-zinc-600 border border-transparent') }}">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            Urgent
            <span class="text-xs opacity-70">({{ $this->urgentCount }})</span>
        </button>

        <button wire:click="setPill('needs-reply')"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ $activePill === 'needs-reply'
                      ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30'
                      : ($this->needsReplyCount > 0
                          ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700'
                          : 'bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 dark:text-zinc-600 border border-transparent') }}">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Needs Reply
            <span class="text-xs opacity-70">({{ $this->needsReplyCount }})</span>
        </button>

        @if ($this->mentionCount > 0)
            <button wire:click="setPill('mentions')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors
                      {{ $activePill === 'mentions'
                          ? 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/30'
                          : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                Mentions
                <span class="text-xs opacity-70">({{ $this->mentionCount }})</span>
            </button>
        @endif

        @if ($this->unassignedCount > 0)
            <button wire:click="setPill('unassigned')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors
                      {{ $activePill === 'unassigned'
                          ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30'
                          : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                Unassigned
                <span class="text-xs opacity-70">({{ $this->unassignedCount }})</span>
            </button>
        @endif

        <button wire:click="setPill('all')"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ $activePill === 'all'
                      ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/30'
                      : ($this->allMyCount > 0
                          ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700'
                          : 'bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 dark:text-zinc-600 border border-transparent') }}">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            All Mine
            <span class="text-xs opacity-70">({{ $this->allMyCount }})</span>
        </button>
    </div>

    {{-- Mentions view (special non-table layout) --}}
    @if ($activePill === 'mentions')
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            @if ($this->mentionTickets->isNotEmpty())
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach ($this->mentionTickets as $mention)
                        <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $mention->ticket]) }}#note-{{ $mention->ticket_reply_id }}"
                            wire:click="markMentionRead({{ $mention->id }})" wire:navigate
                            wire:key="mention-{{ $mention->id }}"
                            class="flex items-center gap-4 px-5 py-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group no-underline">
                            <div class="w-1 self-stretch rounded-full bg-violet-500"></div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-sm text-zinc-800 dark:text-zinc-200 truncate group-hover:text-zinc-900 dark:group-hover:text-white transition-colors font-medium">
                                    {{ $mention->ticket->subject ?? 'Ticket' }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    Mentioned by {{ $mention->mentionedByUser->name ?? 'Unknown' }}
                                </p>
                                @if ($mention->reply)
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 italic truncate">
                                        {{ Str::limit(strip_tags($mention->reply->message), 80) }}
                                    </p>
                                @endif
                            </div>
                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500 whitespace-nowrap shrink-0">
                                {{ $mention->created_at->diffForHumans(short: true) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-16 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No unread mentions</p>
                </div>
            @endif
        </div>
    @else
        {{-- Filters Bar --}}
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
                    <div class="relative">
                        <select wire:model.live="statusFilter"
                            class="appearance-none rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 pr-8 text-xs text-zinc-600 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div class="relative">
                        <select wire:model.live="priorityFilter"
                            class="appearance-none rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 pr-8 text-xs text-zinc-600 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            <option value="">All Priorities</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div class="relative">
                        <select wire:model.live="categoryFilter"
                            class="appearance-none rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 pr-8 text-xs text-zinc-600 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            <option value="">All Categories</option>
                            @foreach ($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            @if ($this->hasActiveFilters)
                <div class="flex items-center gap-3 text-xs">
                    <button wire:click="resetFilters"
                        class="text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>

        {{-- Bulk Actions Bar --}}
        @if (!empty($selectedTickets))
            <div
                class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ count($selectedTickets) }}
                        tickets selected</span>
                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                    {{-- Bulk Status --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 hover:text-teal-500 transition-colors">
                            Set Status
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
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

                    {{-- Bulk Priority --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 hover:text-teal-500 transition-colors">
                            Set Priority
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute left-0 mt-2 w-40 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl z-20 overflow-hidden">
                            @foreach ($priorities as $priority)
                                <button wire:click="bulkSetPriority('{{ $priority }}')" @click="open = false"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                                    {{ ucfirst($priority) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button wire:click="$set('selectedTickets', [])"
                    class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    Cancel selection
                </button>
            </div>
        @endif

        {{-- Ticket Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <th class="w-12 px-3 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="w-4 h-4 bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-teal-500 rounded focus:ring-teal-500 focus:ring-offset-white dark:focus:ring-offset-zinc-900">
                        </th>
                        <th wire:click="setSortBy('subject')"
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer hover:text-zinc-600 dark:hover:text-zinc-300">
                            Subject
                            @if ($sortBy === 'subject')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                            Customer
                        </th>
                        @if ($activePill === 'unassigned')
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                                Assigned To
                            </th>
                        @endif
                        <th wire:click="setSortBy('status')"
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer hover:text-zinc-600 dark:hover:text-zinc-300">
                            Status
                            @if ($sortBy === 'status')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="w-14 px-4 py-3"></th>
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
                            <td class="px-3 py-4 text-left" wire:click.stop>
                                <input type="checkbox" wire:model.live="selectedTickets" value="{{ $ticket->id }}"
                                    class="w-4 h-4 bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-teal-500 rounded focus:ring-teal-500 focus:ring-offset-white dark:focus:ring-offset-zinc-900">
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ Str::limit($ticket->subject, 65) }}
                                </p>
                                <p class="mt-0.5 text-xs text-zinc-400">
                                    {{ $ticket->category->name ?? 'No category' }} · {{ $ticket->ticket_number }}
                                </p>
                            </td>
                            <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $ticket->customer->name ?? 'Unknown' }}
                            </td>
                            @if ($activePill === 'unassigned')
                                <td class="px-4 py-4 text-sm" wire:click.stop>
                                    <button wire:click="takeTicket({{ $ticket->id }})" wire:loading.attr="disabled"
                                        wire:target="takeTicket({{ $ticket->id }})"
                                        class="px-3 py-1.5 text-xs font-medium text-teal-600 dark:text-teal-400 border border-teal-500/30 rounded-lg hover:bg-teal-500/10 transition-colors disabled:opacity-50">
                                        <span wire:loading.remove wire:target="takeTicket({{ $ticket->id }})">Take
                                            this</span>
                                        <span wire:loading
                                            wire:target="takeTicket({{ $ticket->id }})">Taking...</span>
                                    </button>
                                </td>
                            @endif
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
                                                SLA breached
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-right">
                                <span class="text-[11px] text-zinc-400 dark:text-zinc-500 whitespace-nowrap">
                                    {{ $ticket->updated_at->diffForHumans(short: true) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activePill === 'unassigned' ? 6 : 5 }}" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                @if ($activePill === 'urgent')
                                    <p class="mt-4 text-zinc-500 dark:text-zinc-400">No urgent tickets right now 🎉</p>
                                @elseif ($activePill === 'needs-reply')
                                    <p class="mt-4 text-zinc-500 dark:text-zinc-400">No clients waiting for a reply</p>
                                @elseif ($activePill === 'unassigned')
                                    <p class="mt-4 text-zinc-500 dark:text-zinc-400">No unassigned tickets in your team
                                    </p>
                                @else
                                    <p class="mt-4 text-zinc-500 dark:text-zinc-400">No tickets found. Try adjusting
                                        your filters.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
            {{ $this->tickets->links('pagination.tickets-compact') }}
        </div>
    @endif
</div>
