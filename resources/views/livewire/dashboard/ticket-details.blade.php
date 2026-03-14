<div class="min-h-screen">
    <x-flash-message />



    <div class="max-w-[1600px] mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('tickets', ['company' => $ticket->company->slug]) }}"
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:text-zinc-100 transition">
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
                        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $ticket->subject }}</h1>
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
                            class="px-4 py-2 bg-zinc-200 dark:bg-zinc-600 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white rounded-lg transition flex items-center gap-2">
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
                        class="px-2.5 py-1  text-white dark:text-zinc-400 text-xs font-medium rounded-full border border-zinc-200 dark:border-zinc-700"
                        style="background-color:{{ $ticket->category->color }}">
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
            <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: 'conversation' }">
                {{-- Conversation & Notes Tabs --}}
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="flex space-x-1 p-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg max-w-[320px]">
                        <button @click="activeTab = 'conversation'"
                            :class="{ 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow': activeTab === 'conversation', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'conversation' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Conversation
                        </button>
                        <button @click="activeTab = 'internal-notes'"
                            :class="{ 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow': activeTab === 'internal-notes', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'internal-notes' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Notes
                        </button>
                    </div>

                    <button @click="activeTab = 'logs'"
                        :class="{ 'text-zinc-600 dark:text-zinc-300 bg-zinc-200 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700': activeTab === 'logs', 'text-zinc-500 bg-transparent border-transparent hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'logs' }"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Activity Logs
                    </button>
                </div>

                <div x-show="activeTab === 'conversation'">
                    {{-- Conversation Section --}}
                    <div
                        class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Conversation</h2>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Visible to customer</span>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ count($replies) + 1 }} messages
                            </p>
                        </div>

                        <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
                            {{-- Original Ticket (First Message) --}}
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                        {{ substr($ticket->customer_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                    {{ $ticket->customer_name }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $ticket->created_at->format('M d, Y g:i A') }}</div>
                                            </div>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                Customer
                                            </span>
                                        </div>
                                        <div class="text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">
                                            {{ $ticket->description }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Replies --}}
                            @foreach ($replies as $reply)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        @if ($reply->user_id || $reply->is_technician)
                                            <div
                                                class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                                {{ $reply->is_technician ? 'T' : substr($reply->user->name ?? 'T', 0, 1) }}
                                            </div>
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                                {{ substr($reply->customer_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        @if ($reply->is_technician)
                                                            Technician
                                                        @elseif ($reply->user_id)
                                                            @if ($reply->user->id === Auth::id())
                                                                You <span
                                                                    class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">({{ $reply->user->name }})</span>
                                                            @else
                                                                {{ $reply->user->name }}
                                                            @endif
                                                        @else
                                                            {{ $reply->customer_name }}
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $reply->created_at->format('M d, Y g:i A') }}</div>
                                                </div>
                                                @if ($reply->user_id || $reply->is_technician)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                                        Support Team
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                        Customer
                                                    </span>
                                                @endif
                                            </div>
                                            <div
                                                class="prose prose-sm prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                {!! $reply->message !!}</div>

                                            {{-- Attachments --}}
                                            @if ($reply->attachments)
                                                <div class="mt-4 flex flex-wrap gap-2">
                                                    @foreach (is_array($reply->attachments) ? $reply->attachments : json_decode($reply->attachments, true) ?? [] as $attachment)
                                                        @php
                                                            $isImage = str_starts_with(
                                                                $attachment['mime_type'],
                                                                'image/',
                                                            );
                                                        @endphp

                                                        @if ($isImage)
                                                            <a href="{{ Storage::url($attachment['path']) }}"
                                                                target="_blank"
                                                                class="block relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:border-zinc-500 transition-colors w-32 h-32">
                                                                <img src="{{ Storage::url($attachment['path']) }}"
                                                                    alt="{{ $attachment['name'] }}"
                                                                    class="w-full h-full object-cover">
                                                                <div
                                                                    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                                    <svg class="w-6 h-6 text-zinc-900 dark:text-zinc-100"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                                    </svg>
                                                                </div>
                                                            </a>
                                                        @else
                                                            <a href="{{ Storage::url($attachment['path']) }}"
                                                                target="_blank"
                                                                class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-zinc-500 bg-zinc-800 transition-colors w-full sm:w-auto">
                                                                <svg class="w-8 h-8 text-zinc-500 dark:text-zinc-400 flex-shrink-0"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                                <div class="flex flex-col min-w-0">
                                                                    <span
                                                                        class="text-sm font-medium text-zinc-600 dark:text-zinc-200 truncate">{{ $attachment['name'] }}</span>
                                                                    <span
                                                                        class="text-xs text-zinc-500">{{ number_format($attachment['size'] / 1024, 1) }}
                                                                        KB</span>
                                                                </div>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Reply Form --}}
                        @if (!in_array($state, ['closed']))
                            <div class="p-6 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800">
                                <form wire:submit="addReply">
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost"
                                                        class="px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-lg transition border border-zinc-200 dark:border-zinc-700"
                                                        size="sm">
                                                        Reply as
                                                        @if ($senderId && ($agent = $agents->find($senderId)))
                                                            @if ($agent->id === Auth::id())
                                                                You <span
                                                                    class="text-xs opacity-75">({{ Auth::user()->name }})</span>
                                                            @else
                                                                {{ $agent->name }}
                                                            @endif
                                                        @else
                                                            You <span
                                                                class="text-xs opacity-75">({{ Auth::user()->name }})</span>
                                                        @endif
                                                    </flux:button>
                                                    <flux:menu class="max-h-80 overflow-y-auto">
                                                        @if (Auth::user()->role === 'admin')
                                                            <div class="px-2 py-1 mb-1 sticky top-0 z-10 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700"
                                                                @keydown.stop>
                                                                <div class="flex items-center gap-2">
                                                                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                                    </svg>
                                                                    <input type="text"
                                                                        wire:model.live.debounce.300ms="agentSearch"
                                                                        placeholder="Search agents..."
                                                                        class="w-full bg-transparent border-none text-sm focus:ring-0 p-1 text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 dark:placeholder-zinc-400" />
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <flux:menu.item wire:click="$set('senderId', null)">
                                                            You <span
                                                                class="text-xs opacity-75">({{ Auth::user()->name }})</span>
                                                        </flux:menu.item>

                                                        @if (Auth::user()->role === 'admin')
                                                            <flux:menu.separator />
                                                            @foreach ($agents as $agent)
                                                                @if ($agent->id !== Auth::id())
                                                                    <flux:menu.item
                                                                        wire:click="$set('senderId', {{ $agent->id }})">
                                                                        {{ $agent->name }}
                                                                    </flux:menu.item>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </div>

                                            <button type="button" wire:click="startAiSuggestion"
                                                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-teal-400 hover:text-teal-300 hover:bg-teal-500/10 rounded-lg transition-colors border border-transparent hover:border-teal-500/30">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Generate AI Suggestion
                                            </button>
                                        </div>

                                        <div wire:init="startAiSuggestion" x-data="{
                                            suggestionText: @entangle('aiSuggestion'),
                                            displayedSuggestion: '',
                                            isTyping: false,
                                            typingInterval: null,
                                            startTyping() {
                                                this.stopTyping();
                                                this.displayedSuggestion = '';
                                                if (!this.suggestionText) return;
                                        
                                                this.isTyping = true;
                                                let i = 0;
                                                this.typingInterval = setInterval(() => {
                                                    this.displayedSuggestion += this.suggestionText.charAt(i);
                                                    i++;
                                                    if (i >= this.suggestionText.length) {
                                                        this.stopTyping();
                                                    }
                                                }, 18);
                                            },
                                            stopTyping() {
                                                this.isTyping = false;
                                                if (this.typingInterval) clearInterval(this.typingInterval);
                                            },
                                            useSuggestion() {
                                                const content = this.$refs.editableSuggestion.innerHTML;
                                                window.dispatchEvent(new CustomEvent('loadAiSuggestion', { detail: { content: content } }));
                                                $wire.dismissAiSuggestion();
                                            }
                                        }"
                                            x-init="$watch('suggestionText', value => { if (value) startTyping(); })">
                                            @if ($showAiSuggestion)
                                                <div class="mb-4 p-4 border border-teal-500/30 bg-teal-500/5 rounded-lg flex flex-col gap-3 transition-opacity duration-300"
                                                    :class="{ 'opacity-50': $wire.aiLoading && suggestionText }">

                                                    {{-- Metadata Bar --}}
                                                    <div
                                                        class="flex items-center gap-2 text-xs text-zinc-500 font-medium">
                                                        <span class="text-teal-400">✨ AI Suggestion</span>
                                                        <span>&middot;</span>
                                                        <span>Tone: <span
                                                                class="capitalize">{{ $aiTone }}</span></span>
                                                        <span>&middot;</span>
                                                        <span>Based on {{ $this->ticket->replies->count() }}
                                                            replies</span>
                                                    </div>

                                                    {{-- Header & Spinner --}}
                                                    @if ($aiLoading && !$aiSuggestion)
                                                        <div class="flex items-center gap-2 text-teal-500">
                                                            <svg class="w-5 h-5 animate-spin" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                            </svg>
                                                            <h3 class="font-medium text-sm">AI is generating a
                                                                suggestion...</h3>
                                                        </div>
                                                    @endif

                                                    {{-- Editable Content Block --}}
                                                    <div x-show="suggestionText || isTyping" x-transition>
                                                        <div x-ref="editableSuggestion" :contenteditable="!isTyping"
                                                            class="text-zinc-600 dark:text-zinc-300 text-sm whitespace-pre-wrap outline-none p-2 -mx-2 rounded border border-transparent focus:border-teal-500/50 focus:bg-zinc-50 dark:focus:bg-zinc-800/50 transition-colors"
                                                            x-html="displayedSuggestion + (isTyping ? '<span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'></span>' : '')">
                                                        </div>
                                                        <div x-show="!isTyping && suggestionText"
                                                            class="text-xs text-zinc-500 mt-1 italic">
                                                            ✏️ Click to edit
                                                        </div>
                                                    </div>

                                                    @if (!$aiLoading && $aiSuggestion)
                                                        <div class="-mx-1">
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span
                                                                    class="text-xs font-medium text-zinc-500 px-1 uppercase tracking-wider">Tone:</span>
                                                                <button wire:click="regenerateWithTone('professional')"
                                                                    type="button"
                                                                    class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium {{ $aiTone === 'professional' ? 'bg-teal-500/20 text-teal-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Professional</button>
                                                                <button wire:click="regenerateWithTone('friendly')"
                                                                    type="button"
                                                                    class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium {{ $aiTone === 'friendly' ? 'bg-teal-500/20 text-teal-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Friendly</button>
                                                                <button wire:click="regenerateWithTone('formal')"
                                                                    type="button"
                                                                    class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium {{ $aiTone === 'formal' ? 'bg-teal-500/20 text-teal-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Formal</button>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="flex gap-3 mt-1">
                                                        <button @click="useSuggestion()"
                                                            :disabled="isTyping || $wire.aiLoading" type="button"
                                                            class="text-xs px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            Use this
                                                        </button>

                                                        <button wire:click="regenerateWithTone($wire.aiTone)"
                                                            :disabled="isTyping || $wire.aiLoading" type="button"
                                                            class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <svg x-show="$wire.aiLoading && suggestionText"
                                                                class="w-3.5 h-3.5 animate-spin" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                            Regenerate
                                                        </button>

                                                        <button wire:click="dismissAiSuggestion"
                                                            :disabled="isTyping || $wire.aiLoading" type="button"
                                                            class="text-xs px-3 py-1.5 bg-transparent border border-zinc-200 dark:border-zinc-700 hover:border-zinc-500 text-zinc-500 dark:text-zinc-400 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            Dismiss
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="relative">
                                            <div x-data="tiptapEditor"
                                                class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden focus-within:ring-1 focus-within:ring-zinc-500 dark:focus-within:ring-zinc-600">

                                                {{-- Toolbar --}}
                                                <div class="flex items-center gap-1 p-2 border-b border-zinc-200 dark:border-zinc-700/50 flex-wrap relative"
                                                    x-data="{ showLinkInput: false, linkUrl: '' }">
                                                    <button type="button" @mousedown.prevent @click="bold()"
                                                        :class="isActive('bold') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Bold">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M14 12a4 4 0 0 0 0-8H6v8" />
                                                            <path d="M15 20a4 4 0 0 0 0-8H6v8" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent @click="italic()"
                                                        :class="isActive('italic') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Italic">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <line x1="19" y1="4" x2="10"
                                                                y2="4" />
                                                            <line x1="14" y1="20" x2="5"
                                                                y2="20" />
                                                            <line x1="15" y1="4" x2="9"
                                                                y2="20" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent @click="underline()"
                                                        :class="isActive('underline') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Underline">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3" />
                                                            <line x1="4" y1="21" x2="20"
                                                                y2="21" />
                                                        </svg>
                                                    </button>
                                                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
                                                    <button type="button" @mousedown.prevent @click="bulletList()"
                                                        :class="isActive('bulletList') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Bullet List">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M3 6h.01" />
                                                            <path d="M3 12h.01" />
                                                            <path d="M3 18h.01" />
                                                            <path d="M8 6h13" />
                                                            <path d="M8 12h13" />
                                                            <path d="M8 18h13" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent @click="orderedList()"
                                                        :class="isActive('orderedList') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Numbered List">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M10 6h11" />
                                                            <path d="M10 12h11" />
                                                            <path d="M10 18h11" />
                                                            <path d="M4 6h1v4" />
                                                            <path d="M4 10h2" />
                                                            <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1" />
                                                        </svg>
                                                    </button>
                                                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
                                                    <button type="button" @mousedown.prevent @click="codeBlock()"
                                                        :class="isActive('codeBlock') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Code Block">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <polyline points="16 18 22 12 16 6" />
                                                            <polyline points="8 6 2 12 8 18" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent
                                                        @click="showLinkInput = !showLinkInput; if(showLinkInput) { $nextTick(() => $refs.linkInput.focus()); linkUrl = getLinkUrl(); }"
                                                        :class="isActive('link') ?
                                                            'bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition" title="Link">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path
                                                                d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                                            <path
                                                                d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                                        </svg>
                                                    </button>

                                                    <!-- Link Input Popover -->
                                                    <div x-show="showLinkInput" @click.away="showLinkInput = false"
                                                        style="display: none;"
                                                        class="absolute top-full left-0 mt-1 z-10 w-72 p-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg flex gap-2 items-center">
                                                        <input x-ref="linkInput" type="url" x-model="linkUrl"
                                                            placeholder="https://example.com"
                                                            @keydown.enter.prevent="setLink(linkUrl); showLinkInput = false; linkUrl = ''"
                                                            class="flex-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm rounded px-2 py-1.5 focus:outline-none focus:border-zinc-500">
                                                        <button type="button"
                                                            @click="setLink(linkUrl); showLinkInput = false; linkUrl = ''"
                                                            class="bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 text-sm px-3 py-1.5 rounded transition">
                                                            Set
                                                        </button>
                                                        <button type="button"
                                                            @click="setLink(null); showLinkInput = false; linkUrl = ''"
                                                            class="text-zinc-500 dark:text-zinc-400 hover:text-red-400 p-1.5"
                                                            title="Remove Link">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Editor area --}}
                                                <div wire:ignore>
                                                    <div x-ref="editorEl"></div>
                                                </div>
                                            </div>

                                            <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                                <label
                                                    class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition cursor-pointer"
                                                    title="Attach Files">
                                                    <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                    <input type="file" wire:model="attachments" multiple
                                                        class="hidden" accept="image/*,.pdf,.doc,.docx" />
                                                </label>
                                            </div>
                                        </div>
                                        @error('message')
                                            <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Attachment Previews --}}
                                    @if ($attachments)
                                        <div class="mb-4">
                                            <p class="text-xs font-medium text-zinc-500 mb-2">Attached Files:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($attachments as $index => $attachment)
                                                    <div
                                                        class="flex items-center gap-2 px-3 py-1.5 bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                                        <span
                                                            class="text-sm text-zinc-600 dark:text-zinc-300 truncate max-w-[200px]">{{ $attachment->getClientOriginalName() }}</span>
                                                        <button type="button"
                                                            wire:click="removeAttachment({{ $index }})"
                                                            class="text-zinc-500 hover:text-red-400 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            {{-- Loading state indicator --}}
                                            <div wire:loading wire:target="attachments"
                                                class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Uploading attachments...
                                            </div>
                                        </div>
                                        <div class="flex gap-2 items-center">
                                            <label
                                                class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:text-zinc-100 cursor-pointer mr-2">
                                                <input type="checkbox" wire:model="keepOpen"
                                                    class="rounded bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-teal-600 focus:ring-teal-500 focus:ring-offset-zinc-900" />
                                                Keep open
                                            </label>
                                            <button type="submit" wire:loading.attr="disabled"
                                                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2 disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                                <span wire:loading.remove wire:target="addReply">Send reply</span>
                                                <span wire:loading wire:target="addReply">Sending...</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div
                                class="p-6 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 text-center">
                                <p class="text-zinc-500">This ticket is closed and cannot receive replies.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div x-show="activeTab === 'internal-notes'" x-cloak>
                    <div
                        class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Internal Notes</h2>
                                <span class="text-sm text-zinc-500">Visible only to your team</span>
                            </div>
                            <p class="text-sm text-zinc-500 mt-1">{{ count($internal_notes) }} notes</p>
                        </div>

                        <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
                            @forelse ($internal_notes as $note)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            {{ substr($note->user->name ?? 'A', 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-indigo-900/20 rounded-lg p-4 border border-indigo-500/30">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        @if ($note->user && $note->user->id === Auth::id())
                                                            You <span
                                                                class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">({{ $note->user->name }})</span>
                                                        @else
                                                            {{ $note->user->name ?? 'Unknown' }}
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $note->created_at->format('M d, Y g:i A') }}</div>
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                                    Internal Note
                                                </span>
                                            </div>
                                            <div class="text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">
                                                {{ $note->message }}</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-zinc-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-zinc-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p>No internal notes for this ticket yet.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Add Internal Note Form --}}
                        @if (!in_array($state, ['closed']))
                            <div class="p-6 border-t border-zinc-200 dark:border-zinc-800 bg-indigo-900/10">
                                <form wire:submit="addInternalNote">
                                    <div class="relative">
                                        <textarea wire:model="internalNote" rows="3" placeholder="Add a new internal note..." required
                                            class="w-full bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-3 text-zinc-600 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none disabled:opacity-50"
                                            wire:loading.attr="disabled"></textarea>
                                    </div>
                                    <div class="flex justify-end mt-3">
                                        <button type="submit" wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-2 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="addInternalNote">Add note</span>
                                            <span wire:loading wire:target="addInternalNote">Adding...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div
                                class="p-6 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 text-center">
                                <p class="text-zinc-500">This ticket is closed and cannot receive notes.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div x-show="activeTab === 'logs'" x-cloak style="display: none;">
                    {{-- Logs Section --}}
                    <div
                        class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Activity Log</h2>
                                <span class="text-sm text-zinc-500">{{ count($ticket->logs) }} events</span>
                            </div>
                            <p class="text-sm text-zinc-500 mt-1">Timeline of ticket interactions</p>
                        </div>
                        <div class="p-6">
                            <div
                                class="relative space-y-4 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-zinc-800 before:to-transparent">
                                @forelse($ticket->logs()->with('user')->latest()->get() as $log)
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            @if (str_contains($log->action, 'status'))
                                                <svg class="w-4 h-4 text-emerald-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @elseif(str_contains($log->action, 'assign'))
                                                <svg class="w-4 h-4 text-indigo-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            @elseif(str_contains($log->action, 'priority'))
                                                <svg class="w-4 h-4 text-orange-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    @if ($log->user && $log->user->id === Auth::id())
                                                        You <span
                                                            class="text-[10px] text-zinc-500 font-normal">({{ $log->user->name }})</span>
                                                    @else
                                                        {{ $log->user->name ?? 'System' }}
                                                    @endif
                                                </div>
                                                <time
                                                    class="text-[10px] text-zinc-500">{{ $log->created_at->diffForHumans() }}</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ $log->description }}</div>
                                        </div>
                                    </div>
                                @empty
                                    a
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Ticket Details & Actions --}}
            <div class="space-y-6">

                {{-- Ticket Details --}}
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Ticket details</h3>
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
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $ticket->customer_name }}
                                    </p>
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
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        @if ($ticket->user && $ticket->user->id === Auth::id())
                                            You <span
                                                class="text-xs text-zinc-500 font-normal">({{ $ticket->user->name }})</span>
                                        @else
                                            {{ $ticket->user->name ?? 'Unassigned' }}
                                        @endif
                                    </p>
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
                                <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <div>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $ticket->category->name ?? 'Uncategorized' }}</p>
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
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $ticket->created_at->format('l - H:i') }} CET</p>
                            <p class="text-xs text-zinc-500">via Web form</p>
                        </div>

                        {{-- Last Updated --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Last updated</p>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $ticket->updated_at->diffForHumans() }}</p>
                            <p class="text-xs text-zinc-500">via You <span
                                    class="text-[10px] text-zinc-600 font-normal">({{ Auth::user()->name }})</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-800 space-y-2">
                        {{-- Assign / Reassign --}}
                        @can('view-operators')
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
                                        class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                        @if ($agent->id === Auth::id())
                                            You <span class="text-xs opacity-75">({{ $agent->name }})</span>
                                        @else
                                            {{ $agent->name }}
                                        @endif
                                    </button>
                                @endforeach
                            </x-dropdown-btn>
                        @endcan

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
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
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
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ $status }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Close Ticket --}}
                        <button wire:click="closeTicket" wire:confirm="Are you sure you want to close this ticket?"
                            class="w-full px-4 py-2 bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-red-400 rounded-lg transition text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Close ticket
                        </button>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
