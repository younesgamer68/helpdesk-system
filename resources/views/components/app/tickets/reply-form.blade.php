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

<div class="p-6 border-t border-black/10 dark:border-white/10">
    <form wire:submit="addReply">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    @if (Auth::user()->isAdmin())
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm"
                                class="px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-lg transition border border-zinc-200 dark:border-zinc-700">
                                Reply as
                                {{ $senderId ? App\Models\User::find($senderId)?->name ?? 'Unknown User' : 'You (' . auth()->user()->name . ')' }}
                                <flux:icon.chevron-down variant="micro" class="ml-2" />
                            </flux:button>

                            <flux:menu>
                                <flux:menu.item wire:click="$set('senderId', null)">
                                    You ({{ auth()->user()->name }})
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                </div>

                @if ($aiSuggestionsEnabled)
                    <button type="button" wire:click="startAiSuggestion"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 rounded-lg transition-colors border border-transparent hover:border-emerald-500/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Generate AI Suggestion
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
                    <div class="mb-4 p-4 border border-emerald-500/30 bg-emerald-500/5 rounded-lg flex flex-col gap-3 transition-opacity duration-300"
                        :class="{ 'opacity-50': $wire.aiLoading && suggestionText }">

                        <div class="flex items-center gap-2 text-xs text-zinc-500 font-medium">
                            <span class="text-emerald-400">✨ AI Suggestion</span>
                            <span>·</span>
                            <span>Tone: <span class="capitalize">{{ $aiTone }}</span></span>
                        </div>

                        <div x-show="suggestionText || isTyping" x-transition>
                            <div x-ref="editableSuggestion" :contenteditable="!isTyping"
                                class="text-zinc-600 dark:text-zinc-300 text-sm whitespace-pre-wrap outline-none p-2 -mx-2 rounded border border-transparent focus:border-emerald-500/50 focus:bg-zinc-50 dark:focus:bg-zinc-800/50 transition-colors"
                                x-html="displayedSuggestion + (isTyping ? '<span class=\'animate-pulse inline-block ml-0.5 w-[8px] h-[1em] align-middle bg-emerald-400\'></span>' : '')">
                            </div>
                            <div x-show="!isTyping && suggestionText" class="text-xs text-zinc-500 mt-1 italic">
                                ✏️ Click to edit
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs font-medium text-zinc-500 px-1 uppercase tracking-wider">Tone:</span>
                            <button wire:click="regenerateWithTone('professional')" type="button"
                                class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium {{ $aiTone === 'professional' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Professional</button>
                            <button wire:click="regenerateWithTone('friendly')" type="button"
                                class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium {{ $aiTone === 'friendly' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Friendly</button>
                            <button wire:click="regenerateWithTone('formal')" type="button"
                                class="text-xs px-2.5 py-1 transition-colors rounded-full font-medium {{ $aiTone === 'formal' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300' }}">Formal</button>
                        </div>

                        <div class="flex gap-3 mt-1">
                            <button @click="useSuggestion()" :disabled="isTyping || $wire.aiLoading" type="button"
                                class="text-xs px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Use this
                            </button>

                            <button wire:click="regenerateWithTone($wire.aiTone)"
                                :disabled="isTyping || $wire.aiLoading" type="button"
                                class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-100 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="$wire.aiLoading" class="w-3.5 h-3.5 animate-spin" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Regenerate
                            </button>

                            <button wire:click="dismissAiSuggestion" :disabled="isTyping || $wire.aiLoading"
                                type="button"
                                class="text-xs px-3 py-1.5 bg-transparent border border-zinc-200 dark:border-zinc-700 hover:border-zinc-500 text-zinc-500 dark:text-zinc-400 rounded font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Dismiss
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="relative">
                <x-tiptap-editor model="message" :kbSearch="$kbSearch" :kbResults="$kbResults" />

                <div class="absolute bottom-3 right-3 flex items-center gap-2">

                    <label class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition cursor-pointer"
                        title="Attach Files">
                        <flux:icon.paper-clip class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                        <input type="file" wire:model="attachments" multiple class="hidden"
                            accept="image/*,.pdf,.doc,.docx">
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div wire:loading wire:target="attachments"
                    class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                    <flux:icon.arrow-path class="animate-spin h-4 w-4" />
                    Uploading attachments...
                </div>
                @if ($attachments)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($attachments as $index => $attachment)
                            <div class="flex items-center gap-2 px-2 py-1 bg-zinc-100 dark:bg-zinc-800 rounded text-xs">
                                <span>{{ $attachment->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeAttachment({{ $index }})"
                                    class="text-red-500 hover:text-red-600">
                                    <flux:icon.x-mark variant="micro" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex gap-2 items-center">
                <label
                    class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 cursor-pointer mr-2">
                    <input type="checkbox" wire:model="keepOpen"
                        class="rounded bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500">
                    Keep open
                </label>
                <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addReply">Send reply</span>
                    <span wire:loading wire:target="addReply">Sending...</span>
                </flux:button>
            </div>
        </div>
        @livewire('tickets.kb.media-library')

    </form>
</div>
