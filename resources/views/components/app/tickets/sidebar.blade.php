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
                        {{ strtoupper(substr($ticket->customer_name ?? $ticket->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $ticket->customer_name ?? $ticket->user->name ?? 'Unknown' }}
                        </p>
                        <p class="text-xs text-zinc-500">{{ $ticket->customer_email ?? $ticket->user->email ?? 'no-email@example.com' }}</p>
                    </div>
                </div>
            </div>

            {{-- Assigned Agent --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-zinc-500">Assigned agent</p>
                    @if(auth()->user()->isAdmin())
                        <flux:dropdown>
                            <button type="button" class="text-[10px] text-teal-400 font-medium hover:text-teal-300 transition flex items-center gap-1">
                                {{ $ticket->assignedTo ? 'Reassign' : 'Assign' }}
                                <flux:icon.chevron-down variant="micro" />
                            </button>

                            <flux:menu class="min-w-[200px]">
                                <flux:menu.item wire:click="assign(null)">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 text-[10px]">?</div>
                                        <span>Unassigned</span>
                                    </div>
                                </flux:menu.item>

                                <flux:separator />

                                @foreach($agents as $agent)
                                    <flux:menu.item wire:click="assign({{ $agent->id }})">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-teal-500 flex items-center justify-center text-white text-[10px]">
                                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-medium">{{ $agent->name }}</span>
                                                <span class="text-[10px] text-zinc-500">{{ $agent->email }}</span>
                                            </div>
                                        </div>
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                </div>
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
            <flux:dropdown class="w-full">
                <flux:button variant="ghost" class="w-full justify-center gap-2" icon="exclamation-triangle">
                    Change Priority
                </flux:button>

                <flux:menu>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                        <flux:menu.item wire:click="changePriority('{{ $priority }}')" wire:confirm="Change priority to {{ $priority }}?">
                            {{ ucfirst($priority) }}
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>

            <flux:dropdown class="w-full">
                <flux:button variant="ghost" class="w-full justify-center gap-2" icon="adjustments-horizontal">
                    Change Status
                </flux:button>

                <flux:menu>
                    @foreach(['pending', 'open', 'in_progress', 'resolved', 'closed'] as $status)
                        <flux:menu.item wire:click="changeStatus('{{ $status }}')" wire:confirm="Change status to {{ str_replace('_', ' ', $status) }}?">
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>

            <flux:button wire:click="closeTicket" wire:confirm="Are you sure you want to close this ticket?" variant="ghost" class="w-full justify-center gap-2 text-red-500 dark:text-red-400 hover:text-red-600" icon="x-mark">
                Close ticket
            </flux:button>
        </div>
    </div>
</div>
