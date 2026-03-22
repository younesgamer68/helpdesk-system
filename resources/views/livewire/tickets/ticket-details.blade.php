<div class="min-h-screen lg:h-screen lg:overflow-hidden flex flex-col">
    <x-ui.flash-message />

    <div class="w-full animate-enter flex flex-col flex-1 min-h-0">
        {{-- Header Component --}}
        <x-app.tickets.header :ticket="$ticket" :state="$state" />

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-[7fr_3fr] gap-4 flex-1 min-h-0">

            {{-- Left Column - Content Tabs --}}
            <div class="lg:overflow-y-auto space-y-4 lg:pr-2" x-data="{ activeTab: 'conversation' }">

                {{-- Teammate Banner --}}
                @if ($this->isTeammate)
                    <div x-data="{ dismissed: false }" x-show="!dismissed" x-transition
                        class="flex items-center justify-between gap-3 px-4 py-3 border-l-4 border-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-r-lg">
                        <div class="flex items-center gap-2">
                            <flux:icon.information-circle class="w-5 h-5 text-amber-500 shrink-0" />
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                This ticket is assigned to <span
                                    class="font-semibold">{{ $ticket->assignedTo?->name ?? 'someone' }}</span>. You can
                                collaborate via internal notes.
                            </p>
                        </div>
                        <button @click="dismissed = true"
                            class="text-amber-400 hover:text-amber-600 dark:hover:text-amber-200 transition">
                            <flux:icon.x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                {{-- Tab Switcher --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex space-x-1 w-full max-w-[320px]">
                        <button @click="activeTab = 'conversation'"
                            :class="{ 'text-zinc-900 dark:text-zinc-100 border-b-2 border-zinc-900 dark:border-zinc-100': activeTab === 'conversation', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300': activeTab !== 'conversation' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium transition-all">
                            <flux:icon.chat-bubble-left-right variant="micro" />
                            Conversation
                        </button>
                        <button @click="activeTab = 'internal-notes'"
                            :class="{ 'text-zinc-900 dark:text-zinc-100 border-b-2 border-zinc-900 dark:border-zinc-100': activeTab === 'internal-notes', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300': activeTab !== 'internal-notes' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium transition-all">
                            <flux:icon.document-text variant="micro" />
                            Notes
                        </button>
                    </div>

                    <button @click="activeTab = 'logs'"
                        :class="{ 'text-zinc-900 dark:text-zinc-100 border-b-2 border-zinc-900 dark:border-zinc-100': activeTab === 'logs', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300': activeTab !== 'logs' }"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium transition-all">
                        <flux:icon.clock variant="micro" />
                        Activity Logs
                    </button>
                </div>

                {{-- Conversation Tab --}}
                <div x-show="activeTab === 'conversation'" class="space-y-6"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    @if ($this->aiSettings->ai_summary_enabled)
                        <x-app.tickets.ai-summary />
                    @endif

                    <x-app.tickets.conversation :replies="$this->replies" :ticket="$ticket" :senderId="$senderId" :showAiSuggestion="$showAiSuggestion"
                        :aiTone="$aiTone" :attachments="$attachments" :kbSearch="$kbSearch" :kbResults="$this->kbResults" :aiSuggestionsEnabled="$this->aiSettings->ai_suggestions_enabled"
                        :isTeammate="$this->isTeammate" />
                </div>

                {{-- Internal Notes Tab --}}
                <div x-show="activeTab === 'internal-notes'" style="display: none;"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <x-app.tickets.internal-notes :notes="$this->internalNotes" :ticket="$ticket" />
                </div>

                {{-- Activity Logs Tab --}}
                <div x-show="activeTab === 'logs'" style="display: none;"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <x-app.tickets.activity-log :logs="$this->ticketLogs" />
                </div>
            </div>

            {{-- Right Column - Sidebar --}}
            <div class="lg:overflow-y-auto lg:pl-4 lg:border-l border-black/10 dark:border-white/10">
                <x-app.tickets.sidebar :ticket="$ticket" :agents="$agents" :teams="$teams" :isTeammate="$this->isTeammate"
                    :isAssignee="$this->isAssignee" />
            </div>
        </div>
    </div>
</div>
