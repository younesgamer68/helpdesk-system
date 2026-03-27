@props(['ticket', 'agents', 'teams' => collect(), 'isTeammate' => false, 'isAssignee' => false])

@php
    $slaPolicy = optional($ticket->company)->slaPolicy;
    $slaEnabled = (bool) optional($slaPolicy)->is_enabled;
    $tz = $ticket->company->timezone ?? config('app.timezone');
@endphp

<div class="divide-y divide-zinc-200 dark:divide-zinc-700">
    {{-- AI Summary --}}
    @if ($this->aiSettings->ai_summary_enabled)
        <div class="p-4">
            <x-app.tickets.ai-summary />
        </div>
    @endif

    {{-- Customer --}}
    <div class="p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">Customer</p>
        <div class="flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold shrink-0 shadow-sm">
                {{ strtoupper(substr($ticket->customer_name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                    {{ $ticket->customer_name ?? 'Unknown' }}
                </p>
                <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate">
                    {{ $ticket->customer_email ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Ticket Details --}}
    <div class="p-4 space-y-4">
        <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Details</p>

        {{-- Assigned Agent --}}
        <div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Assigned to</p>
            @if ($ticket->assignedTo)
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                            {{ $ticket->assignedTo->id === auth()->id() ? 'You' : $ticket->assignedTo->name }}
                            @if ($ticket->assignedTo->id === auth()->id())
                                <span class="font-normal text-zinc-400">({{ auth()->user()->name }})</span>
                            @endif
                        </p>
                        <p class="text-xs text-zinc-400 truncate">{{ $ticket->assignedTo->email }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <div
                        class="w-7 h-7 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-xs font-bold">
                        ?</div>
                    <p class="text-sm italic text-zinc-400">Unassigned</p>
                </div>
            @endif
        </div>

        {{-- Category --}}
        <div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Category</p>
            <div class="flex items-start gap-1.5">
                <flux:icon.tag class="w-3.5 h-3.5 text-zinc-400 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $ticket->category->name ?? '—' }}</p>
                    @if ($ticket->category && $ticket->category->description)
                        <p class="text-xs text-zinc-400 mt-0.5">{{ $ticket->category->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Team --}}
        <div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Team</p>
            <div class="flex items-center gap-1.5">
                @if ($ticket->team)
                    <span class="w-3 h-3 rounded-full shrink-0"
                        style="background-color: {{ $ticket->team->color }}"></span>
                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $ticket->team->name }}</p>
                @else
                    <flux:icon.user-group class="w-3.5 h-3.5 text-zinc-400" />
                    <p class="text-sm italic text-zinc-400">No team</p>
                @endif
            </div>
        </div>

        {{-- Source --}}
        <div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Source</p>
            <p class="text-sm text-zinc-700 dark:text-zinc-300">
                {{ match ($ticket->source) {'widget' => 'Widget','agent' => 'Created by agent',default => 'Web form'} }}
            </p>
        </div>

        {{-- Created --}}
        <div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Created</p>
            <p class="text-sm text-zinc-700 dark:text-zinc-300">
                {{ $ticket->created_at->setTimezone($tz)->format('M d, Y · H:i') }}
            </p>
            <p class="text-xs text-zinc-400 mt-0.5">{{ $tz }}</p>
        </div>

        {{-- Last Updated --}}
        <div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Last updated</p>
            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $ticket->updated_at->diffForHumans() }}</p>
        </div>
    </div>

    {{-- SLA Information --}}
    <div class="p-4 space-y-3">
        <div class="flex items-center gap-1.5">
            <flux:icon.clock class="w-3.5 h-3.5 text-zinc-500" />
            <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">SLA</p>
        </div>

        @if (!$slaEnabled)
            <div>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700">
                    Disabled
                </span>
            </div>
        @elseif (!$ticket->due_time)
            <p class="text-sm text-zinc-400 italic">No deadline scheduled</p>
        @else
            <div>
                <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Due</p>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    {{ $ticket->due_time->setTimezone($tz)->format('M d, Y · H:i') }}
                </p>
                <p class="text-xs text-zinc-400">{{ $tz }}</p>
            </div>

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
                    let s = Math.floor(this.remaining / 1000);
                    let h = Math.floor(s / 3600).toString().padStart(2, '0');
                    let m = Math.floor((s % 3600) / 60).toString().padStart(2, '0');
                    let sec = Math.floor(s % 60).toString().padStart(2, '0');
                    return `${h}h ${m}m ${sec}s`;
                },
                get statusLabel() {
                    if (this.isStopped) return 'Completed';
                    if (this.isBreached) return 'Breached';
                    if (this.isAtRisk) return 'At risk';
                    return 'On time';
                }
            }" x-init="if (!isStopped) setInterval(() => now = new Date().getTime(), 1000)">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Time remaining</p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold font-mono border"
                            :class="{
                                'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700': isStopped,
                                'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20': !isStopped &&
                                    isBreached,
                                'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20': !isStopped &&
                                    !isBreached && isAtRisk,
                                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': !
                                    isStopped && !isBreached && !isAtRisk
                            }">
                            <template x-if="!isStopped && !isBreached">
                                <flux:icon.clock class="w-3 h-3" />
                            </template>
                            <span x-text="formatted"></span>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>

    {{-- Actions --}}
    @if (!$isTeammate)
        <div class="p-4 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">Actions</p>

            @if (auth()->user()->isAdmin())
                <x-ui.dropdown-btn>
                    <x-slot:title>
                        <flux:icon.user-plus class="w-4 h-4 shrink-0" />
                        {{ $ticket->assignedTo ? 'Reassign' : 'Assign' }}
                    </x-slot:title>
                    <button type="button" wire:click="promptActionConfirmation('unassign')"
                        class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg">
                        <span class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-[10px]">?</span>
                            Unassigned
                        </span>
                    </button>
                    @foreach ($agents as $agent)
                        <button type="button" wire:click="assign({{ $agent->id }})" @click="open = false"
                            class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition last:rounded-b-lg">
                            <span class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px]">{{ strtoupper(substr($agent->name, 0, 1)) }}</span>
                                <span class="flex flex-col">
                                    <span class="font-medium">{{ $agent->name }}</span>
                                    @if ($agent->teams->isNotEmpty())
                                        <span class="flex items-center gap-1 flex-wrap">
                                            @foreach ($agent->teams as $agentTeam)
                                                <span
                                                    class="inline-flex items-center gap-0.5 text-[10px] text-zinc-500">
                                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                                        style="background-color: {{ $agentTeam->color ?? '#71717a' }}"></span>
                                                    {{ $agentTeam->name }}
                                                </span>
                                            @endforeach
                                        </span>
                                    @else
                                        <span class="text-[10px] text-zinc-500">No team</span>
                                    @endif
                                </span>
                            </span>
                        </button>
                    @endforeach
                </x-ui.dropdown-btn>
            @endif

            @if (!$isAssignee)
                <x-ui.dropdown-btn>
                    <x-slot:title>
                        <flux:icon.exclamation-triangle class="w-4 h-4 shrink-0" />
                        Change Priority
                    </x-slot:title>
                    @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                        <button type="button" wire:click="promptActionConfirmation('priority', '{{ $priority }}')"
                            @click="open = false"
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
                        <button type="button" wire:click="promptActionConfirmation('status', '{{ $status }}')"
                            @click="open = false"
                            class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </button>
                    @endforeach
                </x-ui.dropdown-btn>
            @endif

            @if ($isAssignee)
                <x-ui.dropdown-btn>
                    <x-slot:title>
                        <flux:icon.exclamation-triangle class="w-4 h-4 shrink-0" />
                        Change Priority
                    </x-slot:title>
                    @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                        <button type="button" wire:click="promptActionConfirmation('priority', '{{ $priority }}')"
                            @click="open = false"
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
                        <button type="button" wire:click="promptActionConfirmation('status', '{{ $status }}')"
                            @click="open = false"
                            class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </button>
                    @endforeach
                </x-ui.dropdown-btn>
            @endif

            @if (Auth::user()->isAdmin())
                <button type="button" wire:click="promptActionConfirmation('close')"
                    class="w-full px-4 py-2 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg transition text-sm font-medium flex items-center justify-center gap-2 border border-red-200 dark:border-red-800/50">
                    <flux:icon.x-mark class="w-4 h-4 shrink-0" />
                    Close ticket
                </button>
            @endif


        </div>
    @endif

    {{-- Team Picker Modal (for multi-team agents) --}}
    @if ($this->showTeamPickerModal && $this->pendingAssignAgentId)
        <flux:modal wire:model="showTeamPickerModal" class="md:w-[400px]">
            <div class="space-y-4">
                <flux:heading size="lg">Select Team</flux:heading>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    This agent belongs to multiple teams. Which team should this ticket be assigned to?
                </p>
                <div class="space-y-2">
                    @foreach ($this->pendingAgentTeams as $agentTeam)
                        <button type="button" wire:click="confirmAssignWithTeam({{ $agentTeam->id }})"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition text-left">
                            <span class="w-3 h-3 rounded-full shrink-0"
                                style="background-color: {{ $agentTeam->color ?? '#71717a' }}"></span>
                            <span
                                class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $agentTeam->name }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="flex justify-end">
                    <flux:button wire:click="cancelAssign" variant="ghost" size="sm">Cancel</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
