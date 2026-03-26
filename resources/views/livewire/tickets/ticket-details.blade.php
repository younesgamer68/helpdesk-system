<div class="min-h-screen lg:h-screen lg:overflow-hidden flex flex-col bg-zinc-50 dark:bg-zinc-950 animate-enter">
    <x-ui.flash-message />

    {{-- Sticky Top Header --}}
    <x-app.tickets.header :ticket="$ticket" :state="$state" />

    {{-- Body: main column + right sidebar --}}
    <div class="flex flex-1 min-h-0 overflow-hidden" x-data="{ activeTab: 'conversation' }">

        {{-- Main Column --}}
        <div class="flex flex-col flex-1 min-h-0">

            {{-- Teammate banner --}}
            @if ($this->isTeammate)
                <div x-data="{ dismissed: false }" x-show="!dismissed" x-transition
                    class="flex items-center justify-between gap-3 px-6 py-2.5 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-700/40 shrink-0">
                    <div class="flex items-center gap-2">
                        <flux:icon.information-circle class="w-4 h-4 text-amber-500 shrink-0" />
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            Collaborating — ticket assigned to
                            <span class="font-semibold">{{ $ticket->assignedTo?->name ?? 'someone' }}</span>.
                            Use internal notes to communicate.
                        </p>
                    </div>
                    <button @click="dismissed = true"
                        class="shrink-0 p-0.5 rounded text-amber-400 hover:text-amber-600 dark:hover:text-amber-200 transition">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </button>
                </div>
            @endif

            {{-- Tab Bar --}}
            <div
                class="shrink-0 flex items-center gap-0 px-6 bg-white dark:bg-zinc-900 border-b-2 border-zinc-300 dark:border-zinc-700">
                <button @click="activeTab = 'conversation'"
                    :class="activeTab === 'conversation'
                        ?
                        'text-zinc-900 dark:text-zinc-100 border-b-2 border-teal-600 dark:border-teal-400' :
                        'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 border-b-2 border-transparent'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors">
                    <flux:icon.chat-bubble-left-right variant="micro" />
                    Conversation
                </button>
                <button @click="activeTab = 'internal-notes'"
                    :class="activeTab === 'internal-notes'
                        ?
                        'text-zinc-900 dark:text-zinc-100 border-b-2 border-amber-500 dark:border-amber-400' :
                        'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 border-b-2 border-transparent'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors">
                    <flux:icon.lock-closed variant="micro" />
                    Internal Notes
                </button>
                <button @click="activeTab = 'logs'"
                    :class="activeTab === 'logs'
                        ?
                        'text-zinc-900 dark:text-zinc-100 border-b-2 border-zinc-500 dark:border-zinc-400' :
                        'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 border-b-2 border-transparent'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors">
                    <flux:icon.clock variant="micro" />
                    Activity
                </button>
            </div>

            {{-- Conversation Tab --}}
            <div x-show="activeTab === 'conversation'" class="flex flex-col flex-1 min-h-0"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">
                <x-app.tickets.conversation :replies="$this->replies" :ticket="$ticket" :senderId="$senderId" :showAiSuggestion="$showAiSuggestion"
                    :aiTone="$aiTone" :attachments="$attachments" :kbSearch="$kbSearch" :kbResults="$this->kbResults" :aiSuggestionsEnabled="$this->aiSettings->ai_suggestions_enabled"
                    :isTeammate="$this->isTeammate" :isCustomerTyping="$isCustomerTyping" />
            </div>

            {{-- Internal Notes Tab --}}
            <div x-show="activeTab === 'internal-notes'" style="display: none;" class="flex flex-col flex-1 min-h-0"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">
                <x-app.tickets.internal-notes :notes="$this->internalNotes" :ticket="$ticket" />
            </div>

            {{-- Activity Logs Tab --}}
            <div x-show="activeTab === 'logs'" style="display: none;"
                class="flex flex-col flex-1 min-h-0 overflow-y-auto"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">
                <x-app.tickets.activity-log :logs="$this->ticketLogs" />
            </div>
        </div>

        {{-- Right Sidebar Panel --}}
        <div
            class="w-[300px] xl:w-[340px] shrink-0 overflow-y-auto bg-white dark:bg-zinc-900 border-l-2 border-zinc-300 dark:border-zinc-700">
            <x-app.tickets.sidebar :ticket="$ticket" :agents="$agents" :teams="$teams" :isTeammate="$this->isTeammate"
                :isAssignee="$this->isAssignee" />
        </div>
    </div>

    <flux:modal wire:model="showActionConfirmationModal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $confirmationTitle }}</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $confirmationMessage }}</p>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button wire:click="cancelActionConfirmation" variant="ghost" size="sm">
                    Cancel
                </flux:button>

                <flux:button wire:click="confirmActionConfirmation" size="sm"
                    class="{{ $confirmationButtonStyle === 'danger' ? '!bg-red-600 hover:!bg-red-700 !text-white' : '!bg-emerald-600 hover:!bg-emerald-700 !text-white' }}">
                    {{ $confirmationButtonLabel }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
