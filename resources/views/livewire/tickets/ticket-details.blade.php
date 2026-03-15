<div class="min-h-screen">
    <x-ui.flash-message />

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header Component --}}
        <x-app.tickets.header :ticket="$ticket" :state="$state" />

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column - Content Tabs --}}
            <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: 'conversation' }">
                
                {{-- Tab Switcher --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex space-x-1 p-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg w-full max-w-[320px]">
                        <button @click="activeTab = 'conversation'"
                            :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow': activeTab === 'conversation', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'conversation' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-all">
                            <flux:icon.chat-bubble-left-right variant="micro" />
                            Conversation
                        </button>
                        <button @click="activeTab = 'internal-notes'"
                            :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow': activeTab === 'internal-notes', 'text-zinc-500 hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'internal-notes' }"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-all">
                            <flux:icon.document-text variant="micro" />
                            Notes
                        </button>
                    </div>

                    <button @click="activeTab = 'logs'"
                        :class="{ 'text-zinc-600 dark:text-zinc-300 bg-zinc-200 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700': activeTab === 'logs', 'text-zinc-500 bg-transparent border-transparent hover:text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/50': activeTab !== 'logs' }"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border border-transparent transition-all">
                        <flux:icon.clock variant="micro" />
                        Activity Logs
                    </button>
                </div>

                {{-- Conversation Tab --}}
                <div x-show="activeTab === 'conversation'" class="space-y-6">
                    @if($showSummary)
                        <x-app.tickets.ai-summary />
                    @endif
                    
                    <x-app.tickets.conversation 
                        :replies="$replies" 
                        :ticket="$ticket" 
                        :senderId="$senderId" 
                        :showAiSuggestion="$showAiSuggestion" 
                        :aiTone="$aiTone" 
                        :attachments="$attachments" 
                    />
                </div>

                {{-- Internal Notes Tab --}}
                <div x-show="activeTab === 'internal-notes'" style="display: none;">
                    <x-app.tickets.internal-notes :notes="$internal_notes" :ticket="$ticket" />
                </div>

                {{-- Activity Logs Tab --}}
                <div x-show="activeTab === 'logs'" style="display: none;">
                    <x-app.tickets.activity-log :logs="$logs" />
                </div>
            </div>

            {{-- Right Column - Sidebar --}}
            <x-app.tickets.sidebar :ticket="$ticket" :agents="$agents" />
        </div>
    </div>
</div>
