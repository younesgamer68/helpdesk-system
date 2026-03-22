<div class="animate-enter">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">My Team</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Your teammates and unassigned queue</p>
    </div>

    @if ($this->teams->isEmpty())
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-5 py-16 text-center">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">You are not a member of any team yet</p>
        </div>
    @else
        <div class="grid lg:grid-cols-5 gap-6">
            {{-- Left: Teammates --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach ($this->teams as $team)
                    <div
                        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                            @if ($team->color)
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                    style="background-color: {{ $team->color }}"></span>
                            @endif
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $team->name }}</h2>
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $team->members->count() }}
                                {{ $team->members->count() === 1 ? 'member' : 'members' }}</span>
                        </div>

                        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($team->members as $member)
                                <div class="flex items-center gap-3 px-5 py-3">
                                    {{-- Avatar --}}
                                    <div class="relative shrink-0">
                                        @if ($member->avatar)
                                            <img src="{{ Storage::url($member->avatar) }}" alt="{{ $member->name }}"
                                                class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div
                                                class="flex items-center justify-center w-8 h-8 bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-full text-xs font-semibold">
                                                {{ $member->initials() }}
                                            </div>
                                        @endif
                                        <span
                                            class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-zinc-900 {{ $member->is_available && $member->status === 'online' ? 'bg-green-500' : 'bg-zinc-400' }}"></span>
                                    </div>

                                    {{-- Name --}}
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-zinc-800 dark:text-zinc-200 truncate">
                                            {{ $member->name }}
                                            @if ($member->id === Auth::id())
                                                <span class="text-xs text-zinc-400">(you)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $member->assigned_tickets_count ?? 0 }} tickets</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Right: Team Queue --}}
            <div class="lg:col-span-3">
                <div
                    class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                    <div
                        class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Unassigned Queue</h2>
                            <p class="text-xs text-zinc-500 mt-0.5">Pick up tickets from your team</p>
                        </div>
                        <div class="w-56">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Search tickets..."
                                class="w-full px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 placeholder-zinc-400 dark:placeholder-zinc-500">
                        </div>
                    </div>

                    @if ($this->teamQueue->isNotEmpty())
                        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($this->teamQueue as $ticket)
                                <div wire:key="queue-{{ $ticket->id }}" class="flex items-center gap-4 px-5 py-3.5">
                                    {{-- Priority bar --}}
                                    @php
                                        $barColor = match ($ticket->priority) {
                                            'urgent' => 'bg-red-500',
                                            'high' => 'bg-orange-500',
                                            'medium' => 'bg-blue-500',
                                            'low' => 'bg-green-500',
                                            default => 'bg-zinc-400',
                                        };
                                    @endphp
                                    <div class="w-1 self-stretch rounded-full {{ $barColor }}"></div>

                                    {{-- Content --}}
                                    <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}"
                                        wire:navigate class="min-w-0 flex-1 no-underline group">
                                        <p
                                            class="text-sm text-zinc-800 dark:text-zinc-200 truncate group-hover:text-zinc-900 dark:group-hover:text-white transition-colors font-medium">
                                            {{ $ticket->subject }}
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 truncate">
                                            {{ $ticket->customer->name ?? $ticket->customer_name }}
                                            @if ($ticket->category)
                                                · {{ $ticket->category->name }}
                                            @endif
                                            @if ($ticket->team)
                                                · {{ $ticket->team->name }}
                                            @endif
                                        </p>
                                    </a>

                                    {{-- Time + Take button --}}
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-[11px] text-zinc-400 dark:text-zinc-500 whitespace-nowrap">
                                            {{ $ticket->created_at->diffForHumans(short: true) }}
                                        </span>
                                        <button wire:click="takeTicket({{ $ticket->id }})"
                                            wire:loading.attr="disabled" wire:target="takeTicket({{ $ticket->id }})"
                                            class="px-3 py-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 rounded-lg hover:bg-emerald-500/10 transition-colors disabled:opacity-50">
                                            <span wire:loading.remove
                                                wire:target="takeTicket({{ $ticket->id }})">Take</span>
                                            <span wire:loading wire:target="takeTicket({{ $ticket->id }})">...</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-5 py-16 text-center">
                            @if ($search !== '')
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">No tickets match your search</p>
                            @else
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">No unassigned tickets in your team
                                    queue</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
