<div>
    <flux:separator class="mb-5" />
    <x-ai.layout heading="Usage Stats" subheading="AI performance analytics for the last 30 days.">
        <div class="space-y-8">
            {{-- Headline stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-5">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Acceptance Rate</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $this->acceptanceRate }}%</p>
                    <p class="text-xs text-zinc-400 mt-1">{{ $this->suggestionsAccepted }} accepted /
                        {{ $this->suggestionsAccepted + $this->suggestionsDismissed }} decisions</p>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-5">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Tickets Deflected</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $this->ticketsDeflected }}
                    </p>
                    <p class="text-xs text-zinc-400 mt-1">{{ $this->deflectionRate }}% deflection rate</p>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-5">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Time Saved</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                        @if ($this->timeSavedMinutes >= 60)
                            {{ round($this->timeSavedMinutes / 60, 1) }}h
                        @else
                            {{ $this->timeSavedMinutes }}m
                        @endif
                    </p>
                    <p class="text-xs text-zinc-400 mt-1">Estimated from suggestions + deflections</p>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-5">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Suggestions Generated</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                        {{ $this->suggestionsGenerated }}</p>
                    <p class="text-xs text-zinc-400 mt-1">Last 30 days</p>
                </div>
            </div>

            {{-- Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Suggestions breakdown --}}
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">AI Suggestions</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">Generated</span>
                            <span
                                class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->suggestionsGenerated }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">Accepted</span>
                            <span class="text-sm font-medium text-green-600">{{ $this->suggestionsAccepted }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">Dismissed</span>
                            <span class="text-sm font-medium text-red-500">{{ $this->suggestionsDismissed }}</span>
                        </div>

                        @if ($this->suggestionsAccepted + $this->suggestionsDismissed > 0)
                            <div class="pt-2">
                                <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full"
                                        style="width: {{ $this->acceptanceRate }}%"></div>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">{{ $this->acceptanceRate }}% acceptance rate</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chatbot breakdown --}}
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Chatbot Conversations</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">Total conversations</span>
                            <span
                                class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->chatbotConversationsThisMonth }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">Deflected (resolved)</span>
                            <span class="text-sm font-medium text-green-600">{{ $this->ticketsDeflected }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">Escalated to ticket</span>
                            <span class="text-sm font-medium text-yellow-600">{{ $this->ticketsEscalated }}</span>
                        </div>

                        @if ($this->chatbotConversationsThisMonth > 0)
                            <div class="pt-2">
                                <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full"
                                        style="width: {{ $this->deflectionRate }}%"></div>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">{{ $this->deflectionRate }}% deflection rate</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-ai.layout>
</div>
