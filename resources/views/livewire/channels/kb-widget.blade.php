<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Description + Mockup + Embed Code --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Description --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Knowledge Base Widget</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                A floating search bubble that your customers can use to search your knowledge base from any page on your
                website. Once embedded, visitors can instantly find answers without leaving the page.
            </p>
        </div>

        {{-- Visual Mockup --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Preview</h3>
            <div class="relative bg-zinc-100 dark:bg-zinc-900 rounded-lg p-8 min-h-[260px] overflow-hidden">
                {{-- Simulated page content --}}
                <div class="space-y-3 opacity-40">
                    <div class="h-4 w-3/4 bg-zinc-300 dark:bg-zinc-700 rounded"></div>
                    <div class="h-3 w-full bg-zinc-300 dark:bg-zinc-700 rounded"></div>
                    <div class="h-3 w-5/6 bg-zinc-300 dark:bg-zinc-700 rounded"></div>
                    <div class="h-3 w-2/3 bg-zinc-300 dark:bg-zinc-700 rounded"></div>
                </div>

                {{-- Simulated popup panel --}}
                <div
                    class="absolute bottom-16 right-6 w-72 bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="bg-emerald-600 px-4 py-3">
                        <p class="text-white text-sm font-medium">Search Knowledge Base</p>
                    </div>
                    <div class="p-3 space-y-2">
                        <div
                            class="flex items-center gap-2 px-3 py-2 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.35-4.35" />
                            </svg>
                            <span class="text-xs text-zinc-400">Search articles…</span>
                        </div>
                        <div class="space-y-1.5">
                            <div
                                class="px-3 py-2 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-700">
                                <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Getting Started Guide
                                </p>
                                <p class="text-[10px] text-zinc-400 truncate">Learn how to set up your account…</p>
                            </div>
                            <div
                                class="px-3 py-2 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-700">
                                <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">FAQ & Troubleshooting
                                </p>
                                <p class="text-[10px] text-zinc-400 truncate">Answers to common questions…</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating bubble --}}
                <div
                    class="absolute bottom-4 right-6 w-12 h-12 bg-emerald-600 rounded-full shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Embed Code --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-4">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Embed Code</h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Paste this script tag into the <code
                    class="text-xs bg-zinc-100 dark:bg-zinc-900 px-1.5 py-0.5 rounded">&lt;head&gt;</code> or before
                the closing <code
                    class="text-xs bg-zinc-100 dark:bg-zinc-900 px-1.5 py-0.5 rounded">&lt;/body&gt;</code> tag of
                your website.
            </p>

            <div class="relative">
                <pre
                    class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto font-mono text-zinc-800 dark:text-zinc-200"><code>{{ $this->scriptTag }}</code></pre>
                <button wire:click="copyToClipboard(@js($this->scriptTag), 'script')" type="button"
                    class="absolute top-2 right-2 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 px-3 py-1 rounded text-sm transition-colors bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    {{ $copiedKey === 'script' ? __('Copied!') : __('Copy') }}
                </button>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ $this->widgetUrl }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                    Test Widget
                </a>
            </div>
        </div>
    </div>

    {{-- Right: Info Card --}}
    <div class="space-y-6">
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">How It Works</h3>
            </div>
            <div class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                <div class="flex items-start gap-2">
                    <span
                        class="mt-0.5 w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                    <p>Paste the script tag into your website's HTML.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span
                        class="mt-0.5 w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                    <p>A floating search icon appears in the bottom-right corner.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span
                        class="mt-0.5 w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                    <p>Visitors click it to search your published KB articles instantly.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span
                        class="mt-0.5 w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs font-bold shrink-0">4</span>
                    <p>Results link to your public knowledge base portal for full reading.</p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-sm">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
                <div class="text-blue-800 dark:text-blue-200">
                    <p class="font-medium mb-1">No configuration needed</p>
                    <p class="text-blue-700 dark:text-blue-300 text-xs">The widget automatically searches your published
                        articles. Just paste the script tag and it works out of the box.</p>
                </div>
            </div>
        </div>
    </div>

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
