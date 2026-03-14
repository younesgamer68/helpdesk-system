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
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Ticket
                                #{{ $ticket->ticket_number }}</span>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">Last updated
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: 'conversation' }" data-has-alpine-state="true">

                <div class="flex items-center justify-between mb-4">
                    <div
                        class="flex space-x-1 p-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg max-w-[320px]">
                        <button @click="activeTab = 'conversation'"
                            :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow': activeTab === 'conversation', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'conversation' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-all bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            Conversation
                        </button>
                        <button @click="activeTab = 'internal-notes'"
                            :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow': activeTab === 'internal-notes', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'internal-notes' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-all text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Notes
                        </button>
                    </div>

                    <button @click="activeTab = 'logs'"
                        :class="{ 'text-zinc-600 dark:text-zinc-300 bg-zinc-200 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700': activeTab === 'logs', 'text-zinc-500 bg-transparent border-transparent hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'logs' }"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border transition-all text-zinc-500 bg-transparent border-transparent hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Activity Logs
                    </button>
                </div>

                <div x-show="activeTab === 'conversation'">

                    <div
                        class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Conversation</h2>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Visible to customer</span>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">35 messages
                            </p>
                        </div>


                        <!--[if BLOCK]><![endif]-->
                        <div x-data="{
                            showSummary: window.Livewire.find('sppZ10DHR7gXpcgoSOsA').entangle('showSummary'),
                            summaryText: window.Livewire.find('sppZ10DHR7gXpcgoSOsA').entangle('aiSummary'),
                            displayedSummary: '',
                            isLoading: window.Livewire.find('sppZ10DHR7gXpcgoSOsA').entangle('summaryLoading'),
                            isTyping: false,
                            typingInterval: null,
                            startTyping() {
                                this.stopTyping();
                                this.displayedSummary = '';
                                if (!this.summaryText) return;
                        
                                this.isTyping = true;
                                let i = 0;
                                this.typingInterval = setInterval(() = & gt;
                                {
                                    this.displayedSummary += this.summaryText.charAt(i);
                                    i++;
                                    if (i & gt; = this.summaryText.length) {
                                        this.stopTyping();
                                    }
                                }, 15);
                            },
                            stopTyping() {
                                this.isTyping = false;
                                if (this.typingInterval) clearInterval(this.typingInterval);
                            },
                            getIssue() {
                                return this.displayedSummary.split('\n').find(line = & gt; line.toLowerCase().startsWith('issue:'))?.replace(/issue:\s*/i, '') || '-';
                            },
                            getProgress() {
                                return this.displayedSummary.split('\n').find(line = & gt; line.toLowerCase().startsWith('progress:'))?.replace(/progress:\s*/i, '') || '-';
                            },
                            getNextStep() {
                                return this.displayedSummary.split('\n').find(line = & gt; line.toLowerCase().startsWith('next step:'))?.replace(/next step:\s*/i, '') || '-';
                            },
                            isSectionActive(type) {
                                if (!this.isTyping) return false;
                                const lower = this.displayedSummary.toLowerCase();
                                if (type === 'issue') return !lower.includes('progress:');
                                if (type === 'progress') return lower.includes('progress:') & amp; & amp;
                                !lower.includes('next step:');
                                if (type === 'next step') return lower.includes('next step:');
                                return false;
                            }
                        }" x-init="if (summaryText === '') {
                            isLoading = true;
                            $wire.generateAiSummary()
                        }
                        $watch('summaryText', value = & gt; { if (value & amp; & amp; !displayedSummary) startTyping(); });" data-has-alpine-state="true">
                            <div @clear-summary-display.window="stopTyping(); displayedSummary = '';">
                                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700"
                                    :class="{ 'border-b-0': showSummary }">
                                    <div class="flex items-center justify-between">
                                        <button @click="showSummary = !showSummary"
                                            class="flex items-center gap-2 text-left">
                                            <div class="flex items-center gap-1.5 text-teal-400">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                                    </path>
                                                </svg>
                                                <span class="text-sm font-medium">AI Summary</span>
                                            </div>
                                            <svg class="w-4 h-4 text-zinc-400 transition-transform"
                                                :class="{ 'rotate-180': showSummary }" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="regenerateSummary"
                                            @click="isLoading = true; stopTyping(); displayedSummary = '';"
                                            :disabled="isLoading"
                                            class="flex items-center gap-1.5 text-xs px-2.5 py-1 text-zinc-500 dark:text-zinc-400 hover:text-teal-400 dark:hover:text-teal-400 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg x-show="!isLoading" class="w-3.5 h-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            <svg x-show="isLoading" class="w-3.5 h-3.5 animate-spin" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Regenerate
                                        </button>
                                    </div>
                                    <div x-show="!showSummary &amp;&amp; displayedSummary" class="mt-1 ml-6">
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                            <span x-text="displayedSummary.split('\n')[0].replace('Issue: ', '')">The
                                                customer is reporting that their router is experiencing slow
                                                performance.</span>
                                            <span x-show="isTyping"
                                                class="animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400"
                                                style="display: none;"></span>
                                        </p>
                                    </div>
                                </div>

                                <div x-show="showSummary"
                                    class="p-4 bg-zinc-50 dark:bg-zinc-800/50 transition-all duration-300"
                                    style="display: none;">

                                    <div x-show="isLoading &amp;&amp; !displayedSummary" class="space-y-4"
                                        style="display: none;">
                                        <div class="animate-pulse">
                                            <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-16 mb-3">
                                            </div>
                                            <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-5/6"></div>
                                        </div>
                                        <div class="animate-pulse">
                                            <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-20 mb-3">
                                            </div>
                                            <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-4/6"></div>
                                        </div>
                                        <div class="animate-pulse">
                                            <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-24 mb-3">
                                            </div>
                                            <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-3/6"></div>
                                        </div>

                                        <div class="flex items-center gap-2 text-teal-400 mt-2">
                                            <span
                                                class="animate-pulse inline-block w-[8px] h-[1em] align-middle bg-teal-400"></span>
                                            <span class="text-xs">Generating summary...</span>
                                        </div>
                                    </div>


                                    <div x-show="displayedSummary || (!isLoading &amp;&amp; !displayedSummary)"
                                        class="border-l-2 border-teal-500 pl-4">
                                        <template x-if="displayedSummary">
                                            <div class="space-y-4 px-2 text-sm">
                                                <div>
                                                    <span
                                                        class="font-semibold text-teal-400 text-xs uppercase tracking-wide">Issue</span>
                                                    <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                                                        x-html="getIssue() + (isSectionActive('issue') ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')">
                                                    </p>
                                                </div>
                                                <div>
                                                    <span
                                                        class="font-semibold text-teal-400 text-xs uppercase tracking-wide">Progress</span>
                                                    <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                                                        x-html="getProgress() + (isSectionActive('progress') ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')">
                                                    </p>
                                                </div>
                                                <div>
                                                    <span
                                                        class="font-semibold text-teal-400 text-xs uppercase tracking-wide">Next
                                                        Step</span>
                                                    <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                                                        x-html="getNextStep() + (isSectionActive('next step') ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')">
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="space-y-4 px-2 text-sm">
                                            <div>
                                                <span
                                                    class="font-semibold text-teal-400 text-xs uppercase tracking-wide">Issue</span>
                                                <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                                                    x-html="getIssue() + (isSectionActive('issue') ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')">
                                                    The customer is reporting that their router is experiencing slow
                                                    performance.</p>
                                            </div>
                                            <div>
                                                <span
                                                    class="font-semibold text-teal-400 text-xs uppercase tracking-wide">Progress</span>
                                                <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                                                    x-html="getProgress() + (isSectionActive('progress') ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')">
                                                    Multiple support agents have requested clarification on the specific
                                                    hardware issues, but the customer has provided mostly non-responsive
                                                    or incoherent input.</p>
                                            </div>
                                            <div>
                                                <span
                                                    class="font-semibold text-teal-400 text-xs uppercase tracking-wide">Next
                                                    Step</span>
                                                <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                                                    x-html="getNextStep() + (isSectionActive('next step') ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')">
                                                    A final attempt to gather actionable details regarding the
                                                    connection speed or error lights is required to determine if
                                                    troubleshooting or a replacement is necessary.</p>
                                            </div>
                                        </div>
                                        <div x-show="!displayedSummary &amp;&amp; !isLoading"
                                            class="text-sm px-1 text-zinc-500 dark:text-zinc-400"
                                            style="display: none;">
                                            <span
                                                class="animate-pulse inline-block mr-1 w-[8px] h-[1em] align-middle bg-teal-400"></span>
                                            Waiting for summary...
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--[if ENDBLOCK]><![endif]-->
                            <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                            B
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            cclass="bg-zinc-50 dark:bg-zinc-700/40 rounded-lg p-4 border border-zinc-200 dark:border-zinc-600/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        Bethany Benson
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 1:41 AM</div>
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                            </div>
                                            <div class="text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">
                                                In recusandae Delec In recusandae Delec In recusandae Delec In
                                                recusandae Delec In recusandae Delec In recusandae Delec In recusandae
                                                Delec
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!--[if BLOCK]><![endif]-->
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 1:42 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>Hello</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            A
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> <!--[if BLOCK]><![endif]--> Admin
                                                        User
                                                        <!--[if ENDBLOCK]><![endif]--> <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 1:42 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                                    Support Team
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>Hello, thank you for reaching out to Example Helpdesk. Could you
                                                    please provide more details regarding the specific hardware issue
                                                    you are experiencing so we can best assist you?</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 1:43 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 1:49 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>werf</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 2:53 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 2:57 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>AS</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:06 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>sdf</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:06 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>df</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:08 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:08 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>AS</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:08 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>ASD</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:16 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:16 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>ASasd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:16 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:17 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>as</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:21 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>EEEE</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:42 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            n
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> <!--[if BLOCK]><![endif]--> You
                                                        <span
                                                            class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">(nass5)</span>
                                                        <!--[if ENDBLOCK]><![endif]--> <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:42 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                                    Support Team
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:42 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:43 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            n
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> <!--[if BLOCK]><![endif]--> You
                                                        <span
                                                            class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">(nass5)</span>
                                                        <!--[if ENDBLOCK]><![endif]--> <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:43 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                                    Support Team
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 3:43 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>sad</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 6:09 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 6:09 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 6:11 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 6:13 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 6:22 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 11, 2026 11:18 PM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asdasd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:19 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>sdf</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:20 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            n
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> <!--[if BLOCK]><![endif]--> You
                                                        <span
                                                            class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">(nass5)</span>
                                                        <!--[if ENDBLOCK]><![endif]--> <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:20 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                                    Support Team
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>Thank you for reaching out. To ensure we provide the correct
                                                    technical support, could you please describe the specific hardware
                                                    problem or error message you are encountering?</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:21 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>Yeah man my stupid router is slow</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:33 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>xdf</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <!--[if BLOCK]><![endif]-->
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-sm">
                                            B
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700/50">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Bethany Benson
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:33 AM</div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--> <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Customer
                                                </span>
                                                <!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div
                                                class="prose prose-sm prose-zinc dark:prose-invert max-w-none mt-1 text-zinc-600 dark:text-zinc-300">
                                                <p>asd</p>
                                            </div>


                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                </div>
                                <!--[if ENDBLOCK]><![endif]-->
                            </div>


                            <!--[if BLOCK]><![endif]-->
                            <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                                <form wire:submit="addReply">
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <ui-dropdown position="bottom start" data-flux-dropdown="">
                                                    <button type="button"
                                                        class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-8 text-sm rounded-md px-3 inline-flex  bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-800 dark:text-white      px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-lg transition border border-zinc-200 dark:border-zinc-700"
                                                        data-flux-button="data-flux-button" aria-haspopup="true"
                                                        aria-controls="lofi-dropdown-afe88cd83f9998"
                                                        aria-expanded="false">
                                                        Reply as
                                                        <!--[if BLOCK]><![endif]--> You <span
                                                            class="text-xs opacity-75">(nass5)</span>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </button>
                                                    <ui-menu
                                                        class="[:where(&amp;)]:min-w-48 p-[.3125rem] rounded-lg shadow-xs border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700 focus:outline-hidden max-h-80 overflow-y-auto"
                                                        popover="manual" data-flux-menu=""
                                                        id="lofi-dropdown-afe88cd83f9998" role="menu"
                                                        tabindex="-1">
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <button type="button"
                                                            class="flex items-center px-2 py-1.5 w-full focus:outline-hidden rounded-md text-start text-sm font-medium [&amp;[disabled]]:opacity-50 text-zinc-800 data-active:bg-zinc-50 dark:text-white dark:data-active:bg-zinc-600 **:data-flux-menu-item-icon:text-zinc-400 dark:**:data-flux-menu-item-icon:text-white/60 [&amp;[data-active]_[data-flux-menu-item-icon]]:text-current"
                                                            data-flux-menu-item="data-flux-menu-item"
                                                            wire:click="$set('senderId', null)"
                                                            id="lofi-menu-item-37c0b66f6283a" role="menuitem"
                                                            tabindex="-1">
                                                            <div
                                                                class="w-7 hidden [[data-flux-menu]:has(&gt;[data-flux-menu-item-has-icon])_&amp;]:block">
                                                            </div>

                                                            You <span class="text-xs opacity-75">(nass5)</span>
                                                        </button>

                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </ui-menu>
                                                </ui-dropdown>
                                            </div>

                                            <button type="button" wire:click="startAiSuggestion"
                                                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-teal-400 hover:text-teal-300 hover:bg-teal-500/10 rounded-lg transition-colors border border-transparent hover:border-teal-500/30">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                                Generate AI Suggestion
                                            </button>
                                        </div>

                                        <div wire:init="startAiSuggestion" x-data="{
                                            suggestionText: window.Livewire.find('sppZ10DHR7gXpcgoSOsA').entangle('aiSuggestion'),
                                            displayedSuggestion: '',
                                            isTyping: false,
                                            typingInterval: null,
                                            startTyping() {
                                                this.stopTyping();
                                                this.displayedSuggestion = '';
                                                if (!this.suggestionText) return;
                                        
                                                this.isTyping = true;
                                                let i = 0;
                                                this.typingInterval = setInterval(() = & gt;
                                                {
                                                    this.displayedSuggestion += this.suggestionText.charAt(i);
                                                    i++;
                                                    if (i & gt; = this.suggestionText.length) {
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
                                            x-init="$watch('suggestionText', value = & gt; { if (value) startTyping(); })" data-has-alpine-state="true">
                                            <!--[if BLOCK]><![endif]-->
                                            <div class="mb-4 p-4 border border-teal-500/30 bg-teal-500/5 rounded-lg flex flex-col gap-3 transition-opacity duration-300"
                                                :class="{ 'opacity-50': $wire.aiLoading & amp; & amp;suggestionText }">


                                                <div class="flex items-center gap-2 text-xs text-zinc-500 font-medium">
                                                    <span class="text-teal-400">✨ AI Suggestion</span>
                                                    <span>·</span>
                                                    <span>Tone: <span class="capitalize">professional</span></span>
                                                    <span>·</span>
                                                    <span>Based on 35
                                                        replies</span>
                                                </div>


                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->

                                                <div x-show="suggestionText || isTyping" x-transition="">
                                                    <div x-ref="editableSuggestion" :contenteditable="!isTyping"
                                                        class="text-zinc-600 dark:text-zinc-300 text-sm whitespace-pre-wrap outline-none p-2 -mx-2 rounded border border-transparent focus:border-teal-500/50 focus:bg-zinc-50 dark:focus:bg-zinc-800/50 transition-colors"
                                                        x-html="displayedSuggestion + (isTyping ? '&lt;span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-teal-400\'&gt;&lt;/span&gt;' : '')"
                                                        contenteditable="true">I apologize for the frustration you are
                                                        experiencing with your router’s speed. To help me investigate
                                                        this further, could you please confirm if you have already tried
                                                        restarting the device?</div>
                                                    <div x-show="!isTyping &amp;&amp; suggestionText"
                                                        class="text-xs text-zinc-500 mt-1 italic">
                                                        ✏️ Click to edit
                                                    </div>
                                                </div>

                                                <!--[if BLOCK]><![endif]-->
                                                <div class="-mx-1">
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span
                                                            class="text-xs font-medium text-zinc-500 px-1 uppercase tracking-wider">Tone:</span>
                                                        <button wire:click="regenerateWithTone('professional')"
                                                            type="button"
                                                            class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium bg-teal-500/20 text-teal-400">Professional</button>
                                                        <button wire:click="regenerateWithTone('friendly')"
                                                            type="button"
                                                            class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300">Friendly</button>
                                                        <button wire:click="regenerateWithTone('formal')"
                                                            type="button"
                                                            class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300">Formal</button>
                                                    </div>
                                                </div>
                                                <!--[if ENDBLOCK]><![endif]-->
                                                <div class="flex gap-3 mt-1">
                                                    <button @click="useSuggestion()"
                                                        :disabled="isTyping || $wire.aiLoading" type="button"
                                                        class="text-xs px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                        Use this
                                                    </button>

                                                    <button wire:click="regenerateWithTone($wire.aiTone)"
                                                        :disabled="isTyping || $wire.aiLoading" type="button"
                                                        class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <svg x-show="$wire.aiLoading &amp;&amp; suggestionText"
                                                            class="w-3.5 h-3.5 animate-spin" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                            style="display: none;">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                            </path>
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
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>

                                        <div class="relative">
                                            <div x-data="tiptapEditor"
                                                class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden focus-within:ring-1 focus-within:ring-zinc-500 dark:focus-within:ring-zinc-600"
                                                data-has-alpine-state="true">


                                                <div class="flex items-center gap-1 p-2 border-b border-zinc-200 dark:border-zinc-700/50 flex-wrap relative"
                                                    x-data="{ showLinkInput: false, linkUrl: '' }" data-has-alpine-state="true">
                                                    <button type="button" @mousedown.prevent="" @click="bold()"
                                                        :class="isActive('bold') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Bold">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M14 12a4 4 0 0 0 0-8H6v8"></path>
                                                            <path d="M15 20a4 4 0 0 0 0-8H6v8"></path>
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent="" @click="italic()"
                                                        :class="isActive('italic') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Italic">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <line x1="19" y1="4" x2="10"
                                                                y2="4"></line>
                                                            <line x1="14" y1="20" x2="5"
                                                                y2="20"></line>
                                                            <line x1="15" y1="4" x2="9"
                                                                y2="20"></line>
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent="" @click="underline()"
                                                        :class="isActive('underline') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Underline">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path>
                                                            <line x1="4" y1="21" x2="20"
                                                                y2="21"></line>
                                                        </svg>
                                                    </button>
                                                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
                                                    <button type="button" @mousedown.prevent=""
                                                        @click="bulletList()"
                                                        :class="isActive('bulletList') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Bullet List">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M3 6h.01"></path>
                                                            <path d="M3 12h.01"></path>
                                                            <path d="M3 18h.01"></path>
                                                            <path d="M8 6h13"></path>
                                                            <path d="M8 12h13"></path>
                                                            <path d="M8 18h13"></path>
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent=""
                                                        @click="orderedList()"
                                                        :class="isActive('orderedList') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Numbered List">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path d="M10 6h11"></path>
                                                            <path d="M10 12h11"></path>
                                                            <path d="M10 18h11"></path>
                                                            <path d="M4 6h1v4"></path>
                                                            <path d="M4 10h2"></path>
                                                            <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path>
                                                        </svg>
                                                    </button>
                                                    <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
                                                    <button type="button" @mousedown.prevent="" @click="codeBlock()"
                                                        :class="isActive('codeBlock') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Code Block">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <polyline points="16 18 22 12 16 6"></polyline>
                                                            <polyline points="8 6 2 12 8 18"></polyline>
                                                        </svg>
                                                    </button>
                                                    <button type="button" @mousedown.prevent=""
                                                        @click="showLinkInput = !showLinkInput; if(showLinkInput) { $nextTick(() =&gt; $refs.linkInput.focus()); linkUrl = getLinkUrl(); }"
                                                        :class="isActive('link') ?
                                                            'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                                                            'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100'"
                                                        class="p-1.5 rounded transition text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:text-zinc-100"
                                                        title="Link">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2">
                                                            <path
                                                                d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71">
                                                            </path>
                                                            <path
                                                                d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71">
                                                            </path>
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


                                                <div wire:ignore="">
                                                    <div x-ref="editorEl">
                                                        <div contenteditable="true" role="textbox" translate="no"
                                                            class="tiptap ProseMirror prose prose-sm prose-invert focus:outline-none max-w-none min-h-[120px] px-3 py-2 text-zinc-200"
                                                            tabindex="0">
                                                            <p><br class="ProseMirror-trailingBreak"></p>
                                                        </div>
                                                    </div>
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
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                        </path>
                                                    </svg>
                                                    <input type="file" wire:model="attachments" multiple=""
                                                        class="hidden" accept="image/*,.pdf,.doc,.docx">
                                                </label>
                                            </div>
                                        </div>
                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                    </div>


                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">

                                            <div wire:loading="" wire:target="attachments"
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
                                                    class="rounded bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700 text-teal-600 focus:ring-teal-500 focus:ring-offset-white dark:focus:ring-offset-zinc-900">
                                                Keep open
                                            </label>
                                            <button type="submit" wire:loading.attr="disabled"
                                                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2 disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                                <span wire:loading.remove="" wire:target="addReply">Send reply</span>
                                                <span wire:loading="" wire:target="addReply">Sending...</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <div x-show="activeTab === 'internal-notes'" style="display: none;">
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Internal Notes
                                    </h2>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Visible only to your
                                        team</span>
                                </div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">1
                                    notes</p>
                            </div>

                            <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
                                <!--[if BLOCK]><![endif]-->
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold shadow-sm">
                                            A
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 border border-indigo-200 dark:border-indigo-500/30">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        <!--[if BLOCK]><![endif]--> Admin User
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Mar 14, 2026 6:33 AM</div>
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                                    Internal Note
                                                </span>
                                            </div>
                                            <div class="text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">
                                                fg</div>
                                        </div>
                                    </div>
                                </div>
                                <!--[if ENDBLOCK]><![endif]-->
                            </div>


                            <!--[if BLOCK]><![endif]-->
                            <div
                                class="p-6 border-t border-zinc-200 dark:border-zinc-700 bg-indigo-50/50 dark:bg-indigo-900/10">
                                <form wire:submit="addInternalNote">
                                    <div class="relative">
                                        <textarea wire:model="internalNote" rows="3" placeholder="Add a new internal note..." required=""
                                            class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-3 text-zinc-600 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none disabled:opacity-50"
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
                                            <span wire:loading.remove="" wire:target="addInternalNote">Add note</span>
                                            <span wire:loading="" wire:target="addInternalNote">Adding...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <div x-show="activeTab === 'logs'" style="display: none;">

                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Activity Log
                                    </h2>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">12
                                        events</span>
                                </div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Timeline of ticket
                                    interactions
                                </p>
                            </div>
                            <div class="p-6">
                                <div
                                    class="relative space-y-4 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-zinc-300 dark:before:via-zinc-800 before:to-transparent">
                                    <!--[if BLOCK]><![endif]-->
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-orange-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> You <span
                                                        class="text-[10px] text-zinc-500 font-normal">(nass5)</span>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">10 hours ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Priority changed to High.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg
                                                class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> You <span
                                                        class="text-[10px] text-zinc-500 font-normal">(nass5)</span>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">11 hours ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Added a reply.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-emerald-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">1 day ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Ticket resolved.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-emerald-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">2 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Ticket resolved.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg
                                                class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> You <span
                                                        class="text-[10px] text-zinc-500 font-normal">(nass5)</span>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Added a reply.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg
                                                class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> You <span
                                                        class="text-[10px] text-zinc-500 font-normal">(nass5)</span>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Added a reply.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-indigo-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Assigned to nass5.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-emerald-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Ticket resolved.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-indigo-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Assigned to Mike Johnson.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-indigo-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Assigned to asd.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg class="w-4 h-4 text-emerald-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Status changed to Open.</div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow sm:mx-0 mx-auto z-10">
                                            <!--[if BLOCK]><![endif]--> <svg
                                                class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                                </path>
                                            </svg>
                                            <!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div
                                            class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                    <!--[if BLOCK]><![endif]--> Admin User
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <time class="text-[10px] text-zinc-500">3 days ago</time>
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Added a reply.</div>
                                        </div>
                                    </div>
                                    <!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
            <div class="lg:sticky lg:top-8 h-fit space-y-6">


                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Ticket details</h3>
                    </div>
                    <p class="text-xs text-zinc-500 mb-4">Context for this request</p>

                    <div class="space-y-4">

                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Customer</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    B
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">Bethany Benson
                                    </p>
                                    <p class="text-xs text-zinc-500">bdx206@gmail.com</p>
                                </div>
                            </div>
                        </div>


                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Assigned agent</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                                    n
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        <!--[if BLOCK]><![endif]--> You <span
                                            class="text-xs text-zinc-500 font-normal">(nass5)</span>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </p>
                                    <!--[if BLOCK]><![endif]-->
                                    <p class="text-xs text-zinc-500">nassribilal5@gmail.com</p>
                                    <!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>


                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Category</p>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        Hardware Issues</p>
                                    <!--[if BLOCK]><![endif]-->
                                    <p class="text-xs text-zinc-500">Problems with physical devices
                                    </p>
                                    <!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>


                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Created</p>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                Wednesday - 01:41 CET</p>
                            <p class="text-xs text-zinc-500">via Web form</p>
                        </div>


                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Last updated</p>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                9 hours ago</p>
                            <p class="text-xs text-zinc-500">via You <span
                                    class="text-[10px] text-zinc-600 font-normal">(nass5)</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700 space-y-2">




                        <div x-data="{ open: false }" class="relative w-full" data-has-alpine-state="true">
                            <!-- BUTTON -->
                            <button @click="open = !open" @click.outside="open = false" type="button"
                                class="w-full px-4 py-2 bg-zinc-200 dark:bg-zinc-900 hover:bg-zinc-300 dark:hover:bg-zinc-700 dark:text-white rounded-lg transition text-sm flex items-center justify-center gap-2">


                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Change Priority


                            </button>

                            <!-- DROPDOWN -->
                            <div x-show="open" x-transition=""
                                class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden shadow-lg"
                                style="display: none;">
                                <!--[if BLOCK]><![endif]--> <button wire:click="changePriority('low')"
                                    wire:confirm="Change priority to low?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    Low
                                </button>
                                <button wire:click="changePriority('medium')"
                                    wire:confirm="Change priority to medium?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    Medium
                                </button>
                                <button wire:click="changePriority('high')" wire:confirm="Change priority to high?"
                                    @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    High
                                </button>
                                <button wire:click="changePriority('urgent')"
                                    wire:confirm="Change priority to urgent?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    Urgent
                                </button>
                                <!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>



                        <div x-data="{ open: false }" class="relative w-full" data-has-alpine-state="true">
                            <!-- BUTTON -->
                            <button @click="open = !open" @click.outside="open = false" type="button"
                                class="w-full px-4 py-2 bg-zinc-200 dark:bg-zinc-900 hover:bg-zinc-300 dark:hover:bg-zinc-700 dark:text-white rounded-lg transition text-sm flex items-center justify-center gap-2">


                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                                    </path>
                                </svg>
                                Change Status


                            </button>

                            <!-- DROPDOWN -->
                            <div x-show="open" x-transition=""
                                class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden shadow-lg"
                                style="display: none;">
                                <!--[if BLOCK]><![endif]--> <button wire:click="changeStatus(&quot;pending&quot;)"
                                    wire:confirm="Change status to pending?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors first:rounded-t-lg last:rounded-b-lg">
                                    pending
                                </button>
                                <button wire:click="changeStatus(&quot;open&quot;)"
                                    wire:confirm="Change status to open?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors first:rounded-t-lg last:rounded-b-lg">
                                    open
                                </button>
                                <button wire:click="changeStatus(&quot;in progress&quot;)"
                                    wire:confirm="Change status to in progress?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors first:rounded-t-lg last:rounded-b-lg">
                                    in progress
                                </button>
                                <button wire:click="changeStatus(&quot;resolved&quot;)"
                                    wire:confirm="Change status to resolved?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors first:rounded-t-lg last:rounded-b-lg">
                                    resolved
                                </button>
                                <button wire:click="changeStatus(&quot;closed&quot;)"
                                    wire:confirm="Change status to closed?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors first:rounded-t-lg last:rounded-b-lg">
                                    closed
                                </button>
                                <!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>


                        <button wire:click="closeTicket" wire:confirm="Are you sure you want to close this ticket?"
                            class="w-full px-4 py-2 bg-zinc-200 dark:bg-zinc-900 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-red-500 dark:text-red-400 rounded-lg transition text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Close ticket
                        </button>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
