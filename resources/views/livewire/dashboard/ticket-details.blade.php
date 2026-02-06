<div class="min-h-screen">
        <x-flash-message />
    

  
    <div class="max-w-[1600px] mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('tickets', ['company' => $ticket->company->slug]) }}"
                        class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-sm text-zinc-500">Ticket #{{ $ticket->ticket_number }}</span>
                            <span class="text-xs text-zinc-600">Last updated
                                {{ $ticket->updated_at->diffForHumans() }}</span>
                        </div>
                        <h1 class="text-2xl font-semibold text-white">{{ $ticket->subject }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if ($state !== 'resolved')
                        <button wire:click="resolve"
                            wire:confirm="Are you sure you want to mark this ticket as resolved?"
                            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Mark as Resolved
                        </button>
                    @else
                        <button wire:click="unresolve" wire:confirm="Are you sure you want to unresolve this ticket?"
                            class="px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l3-3m0 0l3 3m-3-3v9" />
                            </svg>
                            Unresolve
                        </button>
                    @endif

                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $statusBg = match ($ticket->status) {
                        'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'on-hold', 'on_hold' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        'resolved' => 'bg-green-500/10 text-green-400 border-green-500/20',
                        'closed' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                        'in_progress', 'in progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                    };

                    $priorityBg = match ($ticket->priority) {
                        'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                        'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                        'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                    };
                @endphp

                <span class="px-2.5 py-1 {{ $statusBg }} text-xs font-medium rounded-full border">
                    {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                </span>
                <span class="px-2.5 py-1 {{ $priorityBg }} text-xs font-medium rounded-full border">
                    {{ ucfirst($ticket->priority) }}
                </span>

                @if ($ticket->category)
                    <span
                        class="px-2.5 py-1 bg-zinc-800 text-zinc-400 text-xs font-medium rounded-full border border-zinc-700">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ $ticket->category->name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Left Column - Conversation --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Conversation Section --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                    <div class="p-6 border-b border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white">Conversation</h2>
                            <span class="text-sm text-zinc-500">Visible to customer</span>
                        </div>
                        <p class="text-sm text-zinc-500 mt-1">0 messages · First response in 0 min</p>
                    </div>

                    <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
                        {{-- Messages will go here --}}
                    </div>

                    {{-- Reply Form --}}
                    <div class="p-6 border-t border-zinc-800">
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost"
                                        class="px-3 py-1.5 text-sm bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition border border-zinc-700"
                                        size="sm">
                                        Reply as {{ Auth::user()->name }} {{ ucwords(Auth::user()->role) }}
                                    </flux:button>
                                    <flux:menu>
                                        @foreach ($agents as $agent)
                                            <flux:menu.item>{{  $agent->name === Auth::user()->name ? $agent->name . ' (You)' : $agent->name }}</flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                            <div class="relative">
                                <textarea rows="4" placeholder="Write a reply..."
                                    class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-zinc-600 resize-none"></textarea>
                                <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                    <button class="p-2 hover:bg-zinc-700 rounded-lg transition">
                                        <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </button>
                                    <button class="p-2 hover:bg-zinc-700 rounded-lg transition">
                                        <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                        class="rounded border-zinc-700 bg-zinc-800 text-teal-600 focus:ring-teal-500">
                                    <span class="text-sm text-zinc-400">Reply & keep as Open</span>
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition">
                                    Save draft
                                </button>
                                <button
                                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Send reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Ticket Details & Internal Notes --}}
            <div class="space-y-6">
                {{-- Ticket Details --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-white">Ticket details</h3>
                    </div>
                    <p class="text-xs text-zinc-500 mb-4">Context for this request</p>

                    <div class="space-y-4">
                        {{-- Customer --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Customer</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    {{ substr($ticket->customer_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm text-white">{{ $ticket->customer_name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $ticket->customer_email }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Assigned Agent --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Assigned agent</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                                    {{ substr($ticket->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm text-white">{{ $ticket->user->name ?? 'Unassigned' }}</p>
                                    @if ($ticket->user)
                                        <p class="text-xs text-zinc-500">{{ $ticket->user->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Category</p>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-zinc-400 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <div>
                                    <p class="text-sm text-white">{{ $ticket->category->name ?? 'Uncategorized' }}</p>
                                    @if ($ticket->category)
                                        <p class="text-xs text-zinc-500">{{ $ticket->category->description ?? '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Created --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Created</p>
                            <p class="text-sm text-white">{{ $ticket->created_at->format('l - H:i') }} CET</p>
                            <p class="text-xs text-zinc-500">via Web form</p>
                        </div>

                        {{-- Last Updated --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Last updated</p>
                            <p class="text-sm text-white">{{ $ticket->updated_at->diffForHumans() }}</p>
                            <p class="text-xs text-zinc-500">via {{ Auth::user()->name ?? 'System' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-zinc-800 space-y-2">
                        {{-- Assign / Reassign --}}

                        <x-dropdown-btn>
                            <x-slot:title>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Assign / Reassign
                                </span>
                            </x-slot:title>
                            @foreach ($agents as $agent)
                                <button wire:click="assign({{ $agent->id }})"
                                    wire:confirm="Assign to {{ $agent->name }}?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ $agent->name === Auth::user()->name ? $agent->name . ' (You)' : $agent->name }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Change Priority --}}

                        <x-dropdown-btn>
                            <x-slot:title>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Change Priority
                                </span>
                            </x-slot:title>
                            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                                <button wire:click="changePriority('{{ $priority }}')"
                                    wire:confirm="Change priority to {{ $priority }}?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ ucfirst($priority) }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Change Status --}}

                        <x-dropdown-btn>
                            <x-slot:title>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                                Change Status
                                </span>
                            </x-slot:title>
                            @foreach (['pending', 'open', 'in progress', 'resolved', 'closed'] as $status)
                                <button wire:click="changeStatus({{ json_encode($status) }})"
                                    wire:confirm="Change status to {{ $status }}?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ $status }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Close Ticket --}}
                        <button wire:click="closeTicket" wire:confirm="Are you sure you want to close this ticket?"
                            class="w-full px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-red-400 rounded-lg transition text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Close ticket
                        </button>
                    </div>
                </div>

                {{-- Internal Notes --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-white">Internal notes</h3>
                        <span class="text-xs text-zinc-500">0 notes</span>
                    </div>
                    <p class="text-xs text-zinc-500 mb-4">Visible only to your team</p>

                    <div class="space-y-4 mb-4">
                        {{-- Notes will appear here --}}
                    </div>

                    {{-- Add Note Form --}}
                    <div>
                        <textarea rows="3" placeholder="Add an internal note for your team..."
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-zinc-600 resize-none mb-2"></textarea>
                        <div class="flex gap-2">
                            <button
                                class="flex-1 px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition text-sm">
                                Save note
                            </button>
                            <button
                                class="flex-1 px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition text-sm">
                                Add note
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
