<div x-data="{
    showSummary: @entangle('showSummary'),
    summaryText: @entangle('aiSummary'),
    displayedSummary: '',
    isLoading: @entangle('summaryLoading'),
    isTyping: false,
    typingInterval: null,
    startTyping() {
        this.stopTyping();
        this.displayedSummary = '';
        if (!this.summaryText) return;

        this.isTyping = true;
        let i = 0;
        this.typingInterval = setInterval(() => {
            this.displayedSummary += this.summaryText.charAt(i);
            i++;
            if (i >= this.summaryText.length) {
                this.stopTyping();
            }
        }, 15);
    },
    stopTyping() {
        this.isTyping = false;
        if (this.typingInterval) clearInterval(this.typingInterval);
    },
    getIssue() {
        return this.displayedSummary.split('\n').find(line => line.toLowerCase().startsWith('issue:'))?.replace(/issue:\s*/i, '') || '-';
    },
    getProgress() {
        return this.displayedSummary.split('\n').find(line => line.toLowerCase().startsWith('progress:'))?.replace(/progress:\s*/i, '') || '-';
    },
    getNextStep() {
        return this.displayedSummary.split('\n').find(line => line.toLowerCase().startsWith('next step:'))?.replace(/next step:\s*/i, '') || '-';
    },
    isSectionActive(type) {
        if (!this.isTyping) return false;
        const lower = this.displayedSummary.toLowerCase();
        if (type === 'issue') return !lower.includes('progress:');
        if (type === 'progress') return lower.includes('progress:') && !lower.includes('next step:');
        if (type === 'next step') return lower.includes('next step:');
        return false;
    }
}" x-init="if (summaryText === '') {
    isLoading = true;
    $wire.generateAiSummary()
}
$watch('summaryText', value => { if (value && !displayedSummary) startTyping(); });" data-has-alpine-state="true">
    <div @clear-summary-display.window="stopTyping(); displayedSummary = '';">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700" :class="{ 'border-b-0': showSummary }">
            <div class="flex items-center justify-between">
                <button @click="showSummary = !showSummary" class="flex items-center gap-2 text-left">
                    <div class="flex items-center gap-1.5 text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                            </path>
                        </svg>
                        <span class="text-sm font-medium">AI Summary</span>
                    </div>
                    <svg class="w-4 h-4 text-zinc-400 transition-transform" :class="{ 'rotate-180': showSummary }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <button wire:click="regenerateSummary" @click="isLoading = true; stopTyping(); displayedSummary = '';"
                    :disabled="isLoading"
                    class="flex items-center gap-1.5 text-xs px-2.5 py-1 text-zinc-500 dark:text-zinc-400 hover:text-emerald-400 dark:hover:text-emerald-400 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="!isLoading" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <svg x-show="isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Regenerate
                </button>
            </div>
            <div x-show="!showSummary && displayedSummary" class="mt-1 ml-6">
                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    <span x-text="displayedSummary.split('\n')[0].replace('Issue: ', '')"></span>
                    <span x-show="isTyping"
                        class="animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-emerald-400"
                        style="display: none;"></span>
                </p>
            </div>
        </div>

        <div x-show="showSummary" class="p-4 bg-zinc-50 dark:bg-zinc-800/50 transition-all duration-300"
            style="display: none;">

            <div x-show="isLoading && !displayedSummary" class="space-y-4" style="display: none;">
                <div class="animate-pulse">
                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-16 mb-3"></div>
                    <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-5/6"></div>
                </div>
                <div class="animate-pulse">
                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-20 mb-3"></div>
                    <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-4/6"></div>
                </div>
                <div class="animate-pulse">
                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-24 mb-3"></div>
                    <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-3/6"></div>
                </div>

                <div class="flex items-center gap-2 text-emerald-400 mt-2">
                    <span class="animate-pulse inline-block w-[8px] h-[1em] align-middle bg-emerald-400"></span>
                    <span class="text-xs">Generating summary...</span>
                </div>
            </div>

            <div x-show="displayedSummary || (!isLoading && !displayedSummary)" class="border-l-2 border-emerald-500 pl-4">
                <div class="space-y-4 px-2 text-sm">
                    <div>
                        <span class="font-semibold text-emerald-400 text-xs uppercase tracking-wide">Issue</span>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                            x-html="getIssue() + (isSectionActive('issue') ? '<span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-emerald-400\'></span>' : '')">
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-emerald-400 text-xs uppercase tracking-wide">Progress</span>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                            x-html="getProgress() + (isSectionActive('progress') ? '<span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-emerald-400\'></span>' : '')">
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-emerald-400 text-xs uppercase tracking-wide">Next Step</span>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-1 leading-relaxed whitespace-pre-wrap"
                            x-html="getNextStep() + (isSectionActive('next step') ? '<span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-emerald-400\'></span>' : '')">
                        </p>
                    </div>
                </div>
                <div x-show="!displayedSummary && !isLoading" class="text-sm px-1 text-zinc-500 dark:text-zinc-400"
                    style="display: none;">
                    <span class="animate-pulse inline-block mr-1 w-[8px] h-[1em] align-middle bg-emerald-400"></span>
                    Waiting for summary...
                </div>
            </div>
        </div>
    </div>
</div>
