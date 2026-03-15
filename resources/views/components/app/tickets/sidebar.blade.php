@props([
    'ticket',
    'agents',
])

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
                    @if($ticket->assignedTo)
                        <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                            {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $ticket->assignedTo->id === auth()->id() ? 'You' : $ticket->assignedTo->name }}
                                @if($ticket->assignedTo->id === auth()->id())
                                    <span class="text-xs text-zinc-500 font-normal">({{ auth()->user()->name }})</span>
                                @endif
                            </p>
                            <p class="text-xs text-zinc-500">{{ $ticket->assignedTo->email }}</p>
                        </div>
                    @else
                        <div class="w-6 h-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-xs text-center leading-none">
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
                        @if($ticket->category && $ticket->category->description)
                            <p class="text-xs text-zinc-500">{{ $ticket->category->description }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Created --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Created</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                    {{ $ticket->created_at->format('l - H:i') }} {{ config('app.timezone') }}
                </p>
                <p class="text-xs text-zinc-500">via {{ $ticket->source ?? 'Web form' }}</p>
            </div>

            {{-- Last Updated --}}
            <div>
                <p class="text-xs text-zinc-500 mb-2">Last updated</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                    {{ $ticket->updated_at->diffForHumans() }}
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700 space-y-2">
            @if(auth()->user()->isAdmin())
                <x-ui.dropdown-btn>
                    <x-slot:title>
                        <flux:icon.user-plus class="w-4 h-4 shrink-0" />
                        {{ $ticket->assignedTo ? 'Reassign' : 'Assign' }}
                    </x-slot:title>
                    <button type="button" wire:click="assign(null)" wire:confirm="Unassign this ticket?"
                        class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg">
                        <span class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-[10px]">?</span>
                            Unassigned
                        </span>
                    </button>
                    @foreach($agents as $agent)
                        <button type="button" wire:click="assign({{ $agent->id }})" wire:confirm="Assign to {{ $agent->name }}?" @click="open = false"
                            class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition last:rounded-b-lg">
                            <span class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-teal-500 flex items-center justify-center text-white text-[10px]">{{ strtoupper(substr($agent->name, 0, 1)) }}</span>
                                <span class="flex flex-col">
                                    <span class="font-medium">{{ $agent->name }}</span>
                                    <span class="text-[10px] text-zinc-500">{{ $agent->email }}</span>
                                </span>
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
                @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                    <button type="button" wire:click="changePriority('{{ $priority }}')" wire:confirm="Change priority to {{ $priority }}?" @click="open = false"
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
                @foreach(['pending', 'open', 'in_progress', 'resolved', 'closed'] as $status)
                    <button type="button" wire:click="changeStatus('{{ $status }}')" wire:confirm="Change status to {{ str_replace('_', ' ', $status) }}?" @click="open = false"
                        class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                        {{ str_replace('_', ' ', ucfirst($status)) }}
                    </button>
                @endforeach
            </x-ui.dropdown-btn>

            <button type="button" wire:click="closeTicket" wire:confirm="Are you sure you want to close this ticket?"
                class="w-full px-4 py-2 bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-red-500 dark:text-red-400 rounded-lg transition text-sm flex items-center justify-center gap-2">
                <flux:icon.x-mark class="w-4 h-4 shrink-0" />
                Close ticket
            </button>
        </div>
    </div>
</div>
