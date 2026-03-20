@props(['ticket', 'agents', 'teams' => collect()])

<div class="lg:sticky lg:top-8 h-fit space-y-6">
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Ticket details</h3>
        </div>
        <p class="text-xs text-zinc-500 mb-4">Context for this request</p>

        <div class="space-y-4">
            {{-- Customer --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Customer</p>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                        {{ strtoupper(substr($ticket->customer_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $ticket->customer_name ?? 'Unknown' }}
                        </p>
                        <p class="text-xs text-zinc-500">{{ $ticket->customer_email ?? 'no-email@example.com' }}</p>
                    </div>
                </div>
            </div>

            {{-- Assigned Agent --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Assigned agent</p>
                <div class="flex items-center gap-2">
                    @if ($ticket->assignedTo)
                        <div
                            class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                            {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $ticket->assignedTo->id === auth()->id() ? 'You' : $ticket->assignedTo->name }}
                                @if ($ticket->assignedTo->id === auth()->id())
                                    <span class="text-xs text-zinc-500 font-normal">({{ auth()->user()->name }})</span>
                                @endif
                            </p>
                            <p class="text-xs text-zinc-500">{{ $ticket->assignedTo->email }}</p>
                        </div>
                    @else
                        <div
                            class="w-6 h-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-xs text-center leading-none">
                            ?
                        </div>
                        <p class="text-sm text-zinc-500 italic">Unassigned</p>
                    @endif
                </div>
            </div>

            {{-- Category --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Category</p>
                <div class="flex items-start gap-2">
                    <flux:icon.tag class="w-4 h-4 text-zinc-500 dark:text-zinc-400 mt-0.5" />
                    <div>
                        <p class="text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $ticket->category->name ?? 'No category' }}
                        </p>
                        @if ($ticket->category && $ticket->category->description)
                            <p class="text-xs text-zinc-500">{{ $ticket->category->description }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Team --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Team</p>
                <div class="flex items-center gap-2">
                    @if ($ticket->team)
                        <span class="w-3 h-3 rounded-full shrink-0"
                            style="background-color: {{ $ticket->team->color }}"></span>
                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $ticket->team->name }}</p>
                    @else
                        <flux:icon.user-group class="w-4 h-4 text-zinc-400" />
                        <p class="text-sm text-zinc-500 italic">No team</p>
                    @endif
                </div>
            </div>

            {{-- Created --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Created</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                    {{ $ticket->created_at->setTimezone($ticket->company->timezone ?? config('app.timezone'))->format('l - H:i') }}
                    {{ $ticket->company->timezone ?? config('app.timezone') }}
                </p>
                <p class="text-xs text-zinc-500">via
                    {{ match ($ticket->source) {'widget' => 'Widget','agent' => 'Created by agent',default => 'Web form'} }}
                </p>
            </div>

            {{-- Last Updated --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Last updated</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                    {{ $ticket->updated_at->diffForHumans() }}
                </p>
            </div>

        </div>
    </div>

    {{-- SLA Information Panel --}}
    @php
        $slaPolicy = optional($ticket->company)->slaPolicy;
        $slaEnabled = (bool) optional($slaPolicy)->is_enabled;
        $slaMinutes = match ($ticket->priority) {
            'urgent' => $slaPolicy->urgent_minutes ?? 30,
            'high' => $slaPolicy->high_minutes ?? 120,
            'medium' => $slaPolicy->medium_minutes ?? 480,
            'low' => $slaPolicy->low_minutes ?? 1440,
            default => null,
        };
        $tz = $ticket->company->timezone ?? config('app.timezone');
    @endphp

    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 space-y-4">
        <div class="flex items-center gap-2">
            <flux:icon.clock class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">SLA Information</h3>
        </div>

        @if (!$slaEnabled)
            <div>
                <p class="text-xs text-zinc-500 mb-1">SLA Status</p>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-slate-500/10 text-slate-400 border-slate-500/20">
                    Disabled
                </span>
            </div>
        @elseif (!$ticket->due_time)
            <p class="text-sm text-zinc-500 italic">No SLA deadline scheduled.</p>
        @else
            {{-- Response Time Limit --}}
            <div>
                <p class="text-xs text-zinc-500 mb-1">Response Time Limit</p>
                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ ucfirst($ticket->priority) }} Priority
                </p>
                <p class="text-xs text-zinc-500">
                    Due: {{ $ticket->due_time->setTimezone($tz)->format('l - H:i') }} {{ $tz }}
                </p>
            </div>

            {{-- Remaining Time --}}
            <div x-data="{
                dueTime: new Date('{{ is_string($ticket->due_time) ? \Carbon\Carbon::parse($ticket->due_time)->toISOString() : $ticket->due_time->toISOString() }}').getTime(),
                now: new Date().getTime(),
                status: '{{ $ticket->status }}',
                slaStatus: '{{ $ticket->sla_status }}',
                get isStopped() { return ['resolved', 'closed'].includes(this.status); },
                get remaining() { return Math.max(0, this.dueTime - this.now); },
                get isBreached() { return this.slaStatus === 'breached' || (!this.isStopped && this.remaining === 0); },
                get isAtRisk() { return !this.isStopped && !this.isBreached && this.slaStatus === 'at_risk'; },
                get formatted() {
                    if (this.isStopped) return 'Completed';
                    if (this.isBreached) return 'Breached';
                    let totalSeconds = Math.floor(this.remaining / 1000);
                    let h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
                    let m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
                    let s = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
                    return `${h}h ${m}m ${s}s`;
                },
                get statusLabel() {
                    if (this.isStopped) return 'Completed';
                    if (this.isBreached) return 'Breached';
                    if (this.isAtRisk) return 'At risk';
                    return 'On time';
                }
            }" x-init="if (!isStopped) setInterval(() => now = new Date().getTime(), 1000)">
                <p class="text-xs text-zinc-500 mb-1">Remaining Time</p>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
                    :class="{
                        'bg-slate-500/10 text-slate-400 border-slate-500/20': isStopped,
                        'bg-red-500/10 text-red-400 border-red-500/20': !isStopped && isBreached,
                        'bg-amber-500/10 text-amber-400 border-amber-500/20': !isStopped && !isBreached && isAtRisk,
                        'bg-green-500/10 text-green-400 border-green-500/20': !isStopped && !isBreached && !isAtRisk
                    }">
                    <template x-if="!isStopped && !isBreached">
                        <flux:icon.clock class="w-3 h-3" />
                    </template>
                    <span x-text="formatted"></span>
                </div>

                {{-- SLA Status --}}
                <div class="mt-3">
                    <p class="text-xs text-zinc-500 mb-1">SLA Status</p>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border"
                        :class="{
                            'bg-slate-500/10 text-slate-400 border-slate-500/20': isStopped,
                            'bg-red-500/10 text-red-400 border-red-500/20': !isStopped && isBreached,
                            'bg-amber-500/10 text-amber-400 border-amber-500/20': !isStopped && !isBreached && isAtRisk,
                            'bg-green-500/10 text-green-400 border-green-500/20': !isStopped && !isBreached && !isAtRisk
                        }"
                        x-text="statusLabel">
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700 space-y-2">
        @if (auth()->user()->isAdmin())
            <x-ui.dropdown-btn>
                <x-slot:title>
                    <flux:icon.user-plus class="w-4 h-4 shrink-0" />
                    {{ $ticket->assignedTo ? 'Reassign' : 'Assign' }}
                </x-slot:title>
                <button type="button" wire:click="assign(null)" wire:confirm="Unassign this ticket?"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg">
                    <span class="flex items-center gap-2">
                        <span
                            class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-[10px]">?</span>
                        Unassigned
                    </span>
                </button>
                @foreach ($agents as $agent)
                    <button type="button" wire:click="assign({{ $agent->id }})"
                        wire:confirm="Assign to {{ $agent->name }}?" @click="open = false"
                        class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition last:rounded-b-lg">
                        <span class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px]">{{ strtoupper(substr($agent->name, 0, 1)) }}</span>
                            <span class="flex flex-col">
                                <span class="font-medium">{{ $agent->name }}</span>
                                <span class="text-[10px] text-zinc-500">{{ $agent->email }}</span>
                            </span>
                        </span>
                    </button>
                @endforeach
            </x-ui.dropdown-btn>
        @endif

        @if (auth()->user()->isAdmin())
            <x-ui.dropdown-btn>
                <x-slot:title>
                    <flux:icon.user-group class="w-4 h-4 shrink-0" />
                    {{ $ticket->team ? 'Change Team' : 'Assign Team' }}
                </x-slot:title>
                <button type="button" wire:click="assignToTeam(null)" wire:confirm="Remove from team?"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg">
                    <span class="flex items-center gap-2">
                        <span
                            class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-[10px]">?</span>
                        No team
                    </span>
                </button>
                @foreach ($teams as $team)
                    <button type="button" wire:click="assignToTeam({{ $team->id }})"
                        wire:confirm="Assign to {{ $team->name }}?" @click="open = false"
                        class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition last:rounded-b-lg">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full shrink-0"
                                style="background-color: {{ $team->color }}"></span>
                            {{ $team->name }}
                        </span>
                    </button>
                @endforeach
            </x-ui.dropdown-btn>
        @endif

        <x-ui.dropdown-btn>
            <x-slot:title>
                <flux:icon.exclamation-triangle class="w-4 h-4 shrink-0" />
                Change Priority
            </x-slot:title>
            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                <button type="button" wire:click="changePriority('{{ $priority }}')"
                    wire:confirm="Change priority to {{ $priority }}?" @click="open = false"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                    {{ ucfirst($priority) }}
                </button>
            @endforeach
        </x-ui.dropdown-btn>

        <x-ui.dropdown-btn>
            <x-slot:title>
                <flux:icon.adjustments-horizontal class="w-4 h-4 shrink-0" />
                Change Status
            </x-slot:title>
            @foreach (['pending', 'open', 'in_progress', 'resolved', 'closed'] as $status)
                <button type="button" wire:click="changeStatus('{{ $status }}')"
                    wire:confirm="Change status to {{ str_replace('_', ' ', $status) }}?" @click="open = false"
                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                    {{ str_replace('_', ' ', ucfirst($status)) }}
                </button>
            @endforeach
        </x-ui.dropdown-btn>

        @if (Auth::user()->isAdmin())
            <button type="button" wire:click="closeTicket"
                wire:confirm="Are you sure you want to close this ticket?"
                class="w-full px-4 py-2 bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-red-500 dark:text-red-400 rounded-lg transition text-sm flex items-center justify-center gap-2">
                <flux:icon.x-mark class="w-4 h-4 shrink-0" />
                Close ticket
            </button>
        @endif
    </div>
</div>
</div>
