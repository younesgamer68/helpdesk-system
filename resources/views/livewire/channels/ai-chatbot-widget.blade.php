<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <x-ui.flash-message />

    <div class="space-y-6 xl:col-span-2">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Widget Status</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ $ai_chatbot_enabled ? 'Widget is live' : 'Widget is inactive' }}
                    </p>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="ai_chatbot_enabled" class="w-4 h-4 rounded border-zinc-300">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">Enabled</span>
                </label>
            </div>
        </div>

        @if ($ai_chatbot_enabled)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Chatbot Settings</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Chatbot
                            Greeting</label>
                        <input type="text" wire:model.blur="chatbot_greeting"
                            class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        @error('chatbot_greeting')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">
                            Offer ticket form after X unanswered questions
                        </label>
                        <input type="number" min="1" max="10" wire:model.blur="chatbot_fallback_threshold"
                            class="w-40 rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        @error('chatbot_fallback_threshold')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="saveSettings" type="button"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Save Settings
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">FAQ Knowledge Base</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">These are the questions the bot uses to answer
                        customers.</p>
                </div>
                <button wire:click="openAddFaq" type="button"
                    class="border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Add FAQ
                </button>
            </div>

            <div class="p-6">
                @if ($this->faqs->isEmpty())
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">No FAQs yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-500">
                                    <th class="py-2 pr-4 font-medium">Question</th>
                                    <th class="py-2 pr-4 font-medium">Answer</th>
                                    <th class="py-2 text-right font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->faqs as $faq)
                                    <tr wire:key="faq-{{ $faq->id }}"
                                        class="border-b border-zinc-100 dark:border-zinc-800/70">
                                        <td class="py-3 pr-4 text-zinc-900 dark:text-zinc-100">{{ $faq->question }}</td>
                                        <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-300">
                                            {{ \Illuminate\Support\Str::limit($faq->answer, 100) }}
                                        </td>
                                        <td class="py-3 text-right space-x-2">
                                            <button wire:click="openEditFaq({{ $faq->id }})" type="button"
                                                class="px-3 py-1.5 rounded border border-zinc-200 dark:border-zinc-700 text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                                Edit
                                            </button>
                                            <button wire:click="deleteFaq({{ $faq->id }})"
                                                wire:confirm="Delete this FAQ?" type="button"
                                                class="px-3 py-1.5 rounded border border-red-300 text-red-500 text-xs hover:bg-red-50 dark:hover:bg-red-900/20">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-6 xl:col-span-1">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Embed Code</h2>
            </div>
            <div class="p-6">
                @if ($ai_chatbot_enabled)
                    @php
                        $embedCode =
                            '<iframe src="' .
                            $this->chatbotUrl .
                            '" width="400" height="600" frameborder="0" style="border:none;border-radius:12px;"></iframe>';
                    @endphp
                    <div class="relative">
                        <pre
                            class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto"><code>{{ $embedCode }}</code></pre>
                        <button wire:click="copyToClipboard(@js($embedCode), 'chatbot_embed')"
                            type="button"
                            class="absolute top-2 right-2 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 px-3 py-1 rounded text-sm transition-colors bg-white dark:bg-zinc-900">
                            {{ $copiedKey === 'chatbot_embed' ? 'Copied!' : 'Copy' }}
                        </button>
                    </div>
                @else
                    <div
                        class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-100/70 dark:bg-zinc-900/50 px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                        Enable the chatbot to get your embed code
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Preview</h2>
            </div>
            <div class="p-6 space-y-4">
                @php
                    $isDark = $this->widgetSetting->theme_mode === 'dark';
                    $bubbleClass = $isDark
                        ? 'bg-zinc-900 text-white border border-zinc-700'
                        : 'bg-white text-zinc-900 border border-zinc-300';
                @endphp

                <div class="flex items-center gap-3">
                    <button type="button"
                        class="w-12 h-12 rounded-2xl shadow-md flex items-center justify-center {{ $bubbleClass }}"
                        aria-label="Chatbot bubble preview">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </button>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This is how your chatbot bubble will appear on
                        your website</p>
                </div>

                <a href="{{ $this->chatbotUrl }}" target="_blank" rel="noopener"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $ai_chatbot_enabled ? 'bg-teal-600 text-white hover:bg-teal-700' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-500 cursor-not-allowed pointer-events-none' }}">
                    Test Chatbot
                </a>
            </div>
        </div>
    </div>

    <flux:modal wire:model="showFaqModal" class="md:w-160">
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $editingFaqId ? 'Edit FAQ' : 'Add FAQ' }}
            </h3>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Question</label>
                <textarea wire:model="faqQuestion" rows="2"
                    class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100"></textarea>
                @error('faqQuestion')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Answer</label>
                <textarea wire:model="faqAnswer" rows="5"
                    class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100"></textarea>
                @error('faqAnswer')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" wire:click="$set('showFaqModal', false)"
                    class="px-4 py-2 rounded border border-zinc-200 dark:border-zinc-700 text-sm">
                    Cancel
                </button>
                <button type="button" wire:click="saveFaq"
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                    Save
                </button>
            </div>
        </div>
    </flux:modal>

    <script>
        window.addEventListener('copy-to-clipboard', event => {
            const text = event.detail.text;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
        });
    </script>
</div>
