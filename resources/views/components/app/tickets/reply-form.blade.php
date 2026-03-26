@props([
    'ticket',
    'senderId',
    'showAiSuggestion',
    'aiTone',
    'attachments',
    'kbSearch' => '',
    'kbResults' => collect(),
    'aiSuggestionsEnabled' => false,
])

<div>
    <form wire:submit="addReply">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between px-4 pt-3 pb-2.5 border-b-2 border-zinc-300 dark:border-zinc-700">
            <div class="flex items-center gap-2">
                @if (Auth::user()->isAdmin())
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm"
                            class="h-7 px-2.5 text-xs font-medium bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-md border border-zinc-200 dark:border-zinc-700">
                            <flux:icon.user-circle variant="micro" class="w-3.5 h-3.5 mr-1" />
                            Reply as:
                            <span class="font-semibold ml-1">
                                {{ $senderId ? App\Models\User::find($senderId)?->name ?? 'Unknown' : auth()->user()->name }}
                            </span>
                            <flux:icon.chevron-down variant="micro" class="ml-1" />
                        </flux:button>

                        <flux:menu class="max-h-64 overflow-y-auto">
                            <flux:menu.item wire:click="$set('senderId', null)">
                                <flux:icon.user-circle variant="micro" class="mr-1.5" />
                                You ({{ auth()->user()->name }})
                            </flux:menu.item>

                            @if ($ticket->assignedTo && $ticket->assignedTo->id !== auth()->id())
                                <flux:separator />
                                <div
                                    class="px-3 py-1.5 text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                                    Assigned Agent</div>
                                <flux:menu.item wire:click="$set('senderId', {{ $ticket->assignedTo->id }})">
                                    {{ $ticket->assignedTo->name }}
                                    <span class="text-zinc-400 text-xs ml-1">({{ $ticket->assignedTo->email }})</span>
                                </flux:menu.item>
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                @endif
            </div>

            @if ($aiSuggestionsEnabled)
                <button type="button" wire:click="startAiSuggestion"
                    class="flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-md border border-emerald-200 dark:border-emerald-700/50 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    AI Suggestion
                </button>
            @endif
        </div>

        {{-- AI Suggestion Helper --}}
        <div x-data="{
            suggestionText: $wire.entangle('aiSuggestion'),
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
                $wire.useAiSuggestion(content);
            }
        }" x-init="$watch('suggestionText', value => { if (value) startTyping(); })">
            @if ($showAiSuggestion)
                <div class="mx-4 mt-3 p-3.5 border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/60 dark:bg-emerald-900/10 rounded-lg flex flex-col gap-2.5"
                    :class="{ 'opacity-60': $wire.aiLoading && suggestionText }">
                    <div class="flex items-center gap-2 text-xs text-zinc-500 font-medium">
                        <span class="text-emerald-500">✨ AI Suggestion</span>
                        <span class="text-zinc-300 dark:text-zinc-600">·</span>
                        <span>Tone: <span
                                class="capitalize text-zinc-600 dark:text-zinc-400">{{ $aiTone }}</span></span>
                    </div>

                    <div x-show="suggestionText || isTyping" x-transition>
                        <div x-ref="editableSuggestion" :contenteditable="!isTyping"
                            class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap outline-none p-2 -mx-2 rounded border border-transparent focus:border-emerald-400 focus:bg-white dark:focus:bg-zinc-800/50 transition-colors"
                            x-html="displayedSuggestion + (isTyping ? '<span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-emerald-400\'></span>' : '')">
                        </div>
                        <div x-show="!isTyping && suggestionText" class="text-xs text-zinc-400 mt-1 italic">
                            ✏️ Click to edit before using
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider mr-1">Tone:</span>
                        <button wire:click="regenerateWithTone('professional')" type="button"
                            class="text-xs px-2 py-0.5 rounded-full font-medium transition {{ $aiTone === 'professional' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Professional</button>
                        <button wire:click="regenerateWithTone('friendly')" type="button"
                            class="text-xs px-2 py-0.5 rounded-full font-medium transition {{ $aiTone === 'friendly' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Friendly</button>
                        <button wire:click="regenerateWithTone('formal')" type="button"
                            class="text-xs px-2 py-0.5 rounded-full font-medium transition {{ $aiTone === 'formal' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Formal</button>
                    </div>

                    <div class="flex gap-2">
                        <button @click="useSuggestion()" :disabled="isTyping || $wire.aiLoading" type="button"
                            class="text-xs px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition disabled:opacity-50">Use
                            this</button>
                        <button wire:click="regenerateWithTone($wire.aiTone)" :disabled="isTyping || $wire.aiLoading"
                            type="button"
                            class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-md font-medium transition disabled:opacity-50">
                            <svg x-show="$wire.aiLoading" class="w-3 h-3 animate-spin" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Regenerate
                        </button>
                        <button wire:click="dismissAiSuggestion" :disabled="isTyping || $wire.aiLoading" type="button"
                            class="text-xs px-3 py-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 rounded-md font-medium transition disabled:opacity-50">Dismiss</button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Editor --}}
        <div class="relative p-4">
            <x-tiptap-editor model="message" :kbSearch="$kbSearch" :kbResults="$kbResults" />
            <div class="absolute bottom-7 right-7 flex items-center gap-2">
                <label class="p-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition cursor-pointer"
                    title="Attach Files">
                    <flux:icon.paper-clip class="w-4 h-4 text-zinc-400 dark:text-zinc-500" />
                    <input type="file" wire:model="attachments" multiple class="hidden"
                        accept="image/*,.pdf,.doc,.docx">
                </label>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-4 pb-4 gap-3">
            {{-- Attachments --}}
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <div wire:loading wire:target="attachments" class="text-xs text-zinc-400 flex items-center gap-1.5">
                    <flux:icon.arrow-path class="animate-spin h-3.5 w-3.5" />
                    Uploading...
                </div>
                @if ($attachments)
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($attachments as $index => $attachment)
                            <div
                                class="flex items-center gap-1.5 px-2 py-1 bg-zinc-100 dark:bg-zinc-800 rounded-md text-xs text-zinc-600 dark:text-zinc-400">
                                <span class="truncate max-w-[120px]">{{ $attachment->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeAttachment({{ $index }})"
                                    class="text-zinc-400 hover:text-red-500 transition">
                                    <flux:icon.x-mark variant="micro" class="w-3 h-3" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Controls --}}
            <div class="flex items-center gap-3 shrink-0">
                <label
                    class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer">
                    <input type="checkbox" wire:model="keepOpen"
                        class="rounded border-zinc-300 dark:border-zinc-600 text-teal-600 focus:ring-teal-500">
                    Keep open
                </label>
                <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled"
                    wire:target="addReply">
                    <span wire:loading.remove wire:target="addReply">Send reply</span>
                    <span wire:loading wire:target="addReply">Sending...</span>
                </flux:button>
            </div>
        </div>

        @livewire('tickets.kb.media-library')
    </form>
</div>
