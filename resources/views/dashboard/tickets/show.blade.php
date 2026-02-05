<x-layouts::app :title="__('Tickets')">
    <div class="min-h-screen">
        <div class="max-w-[1600px] mx-auto">
            {{-- Header --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('tickets', ['company' => request()->route('company')]) }}"
                            class="text-zinc-400 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
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
                            <livewire:dashboard.mark-as-resolved :ticket="$ticket" />

                    </div>
                </div>

                <div class="flex items-center gap-2">
                  <livewire:dashboard.status :ticket="$ticket" />
                  <livewire:dashboard.priority :ticket="$ticket" />
                  
                    @if ($ticket->category)
                        <span
                            class="px-2.5 py-1 bg-zinc-800 text-zinc-400 text-xs font-medium rounded-full border border-zinc-700">
                            <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                            {{-- Customer Message --}}
                            {{-- <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr($ticket->customer_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $ticket->customer_name }} <span class="text-zinc-500 font-normal">- Customer</span></p>
                                    <p class="text-xs text-zinc-500">{{ $ticket->created_at->format('M d, H:i') }} CET</p>
                                </div>
                            </div>
                            <div class="ml-11 bg-zinc-800/50 rounded-lg p-4 border border-zinc-700/50">
                                <p class="text-zinc-300 text-sm leading-relaxed">{{ $ticket->description }}</p>
                            </div>
                        </div>

                        {{-- Agent Response Example 
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr($ticket->user->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $ticket->user->name ?? 'Agent' }} <span class="text-zinc-500 font-normal">- Agent</span></p>
                                    <p class="text-xs text-zinc-500">Today - 09:25 CET · Public reply</p>
                                </div>
                            </div>
                            <div class="ml-11 bg-emerald-950/30 rounded-lg p-4 border border-emerald-900/30">
                                <p class="text-zinc-300 text-sm leading-relaxed">Thanks for reaching out, Alex. We are seeing some timeouts with our EU payment processor. Could you confirm if this affects both Visa and Mastercard, or just one of them?</p>
                            </div>
                        </div>

                        {{-- Customer Reply Example
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr($ticket->customer_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $ticket->customer_name }} <span class="text-zinc-500 font-normal">- Customer</span></p>
                                    <p class="text-xs text-zinc-500">Today - 09:31 CET</p>
                                </div>
                            </div>
                            <div class="ml-11 bg-zinc-800/50 rounded-lg p-4 border border-zinc-700/50">
                                <p class="text-zinc-300 text-sm leading-relaxed">Thanks for the quick response. It seems to affect both Visa and Mastercard. PayPal still works, but most of our customers are using cards.</p>
                            </div>
                        </div> --}}

                            {{-- Internal Note Example 
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-sm font-medium">
                                    MR
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Mike Ross <span class="text-zinc-500 font-normal">- Agent</span></p>
                                    <p class="text-xs text-zinc-500">Today - 09:40 CET · Internal note shared</p>
                                </div>
                            </div>
                            <div class="ml-11 bg-zinc-800 rounded-lg p-4 border border-zinc-700">
                                <p class="text-zinc-400 text-sm leading-relaxed italic">Looping in our payments squad now and checking if this correlates with the latest EU SCA deploy. We'll keep you updated, Alex.</p>
                            </div>
                        </div> --}}
                        </div>

                        {{-- Reply Form --}}
                        <div class="p-6 border-t border-zinc-800">
                            <div class="mb-4">
                                <div class="flex items-center gap-2 mb-3">
                                  <flux:dropdown>
                                        <flux:button variant="ghost" class="px-3 py-1.5 text-sm bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition border border-zinc-700" size="sm">
                                            Reply as {{ Auth::user()->name }} {{ ucwords(Auth::user()->role) }}
                                        </flux:button>
                                        <flux:menu>
                                            @foreach ($agents as $agent)
                                                <flux:menu.item>{{ $agent->name }}</flux:menu.item>
                                            @endforeach
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                                <div class="relative">
                                    <textarea rows="4" placeholder="Write a reply to Alex... You can use @ to mention a teammate."
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
                            {{-- <button class="text-zinc-400 hover:text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button> --}}
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
                        <livewire:dashboard.assigned-agent :ticket="$ticket" />

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
                                        <p class="text-sm text-white">{{ $ticket->category->name ?? 'Uncategorized' }}
                                        </p>
                                        @if ($ticket->category)
                                            <p class="text-xs text-zinc-500">
                                                {{ $ticket->category->description ?? 'Checkout / Card' }}</p>
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
                                <p class="text-xs text-zinc-500">via {{ $ticket->user->name ?? 'System' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-zinc-800 space-y-2">
                       <livewire:dashboard.reassignment :ticket="$ticket"/>
                       <livewire:dashboard.change-priority :ticket="$ticket"/>
                         
                            <button
                                class="w-full px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                                Change status
                            </button>
                            <button
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
                            <span class="text-xs text-zinc-500">2 notes</span>
                        </div>
                        <p class="text-xs text-zinc-500 mb-4">Visible only to your team</p>

                        <div class="space-y-4 mb-4">
                            {{-- Internal Note 1 --}}
                            <div class="p-3 bg-zinc-800/50 rounded-lg border border-zinc-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <p class="text-xs font-medium text-white">Sarah Admin</p>
                                    <p class="text-xs text-zinc-500">Today - 09:20 CET</p>
                                </div>
                                <p class="text-xs text-zinc-400 leading-relaxed">Confirmed spike in error rate from EU
                                    gateway, impacting multiple merchants. Priority escalated to Urgent.</p>
                            </div>

                            {{-- Internal Note 2 --}}
                            <div class="p-3 bg-zinc-800/50 rounded-lg border border-zinc-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <p class="text-xs font-medium text-white">Mike Ross</p>
                                    <p class="text-xs text-zinc-500">Today - 09:38 CET</p>
                                </div>
                                <p class="text-xs text-zinc-400 leading-relaxed">Rollback candidate prepared. Waiting
                                    for SRE go-ahead before deploying to EU region.</p>
                            </div>
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
</x-layouts::app>
