<div>
    <x-ui.flash-message />

    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />

        <x-dashboard.kb-layout heading="API Reference" subheading="Public REST API for your Knowledge Base">
            <div class="space-y-6">

                {{-- Base URL --}}
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Base URL</h2>
                    <div class="flex items-center gap-2">
                        <code
                            class="flex-1 text-sm font-mono bg-zinc-100 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 truncate">{{ $this->baseUrl }}</code>
                        <button wire:click="copyToClipboard(@js($this->baseUrl), 'base')" type="button"
                            class="shrink-0 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 px-4 py-2 rounded-lg text-sm transition-colors text-zinc-700 dark:text-zinc-300">
                            {{ $copiedKey === 'base' ? __('Copied!') : __('Copy') }}
                        </button>
                    </div>
                </div>

                {{-- Auth Note --}}
                <div
                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        <div class="text-green-800 dark:text-green-200">
                            <p class="font-medium">No authentication required</p>
                            <p class="text-green-700 dark:text-green-300 text-xs mt-0.5">All endpoints are public and
                                return only published articles. No API keys or tokens needed.</p>
                        </div>
                    </div>
                </div>

                {{-- Widget Integration --}}
                <div
                    class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-4">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">KB Widget Integration</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        Choose the default destination for widget article clicks, then copy the generated script.
                    </p>

                    <div class="space-y-2">
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                            Default Link Destination
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label
                                class="flex items-start gap-2 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 cursor-pointer">
                                <input type="radio" wire:model.live="widgetDefaultLinkMode" value="portal"
                                    class="mt-0.5 text-emerald-600 focus:ring-emerald-500" />
                                <span>
                                    <span class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">Hosted
                                        Portal</span>
                                    <span class="block text-xs text-zinc-500 dark:text-zinc-400">Uses subdomain KB links
                                        by default</span>
                                </span>
                            </label>
                            <label
                                class="flex items-start gap-2 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 cursor-pointer">
                                <input type="radio" wire:model.live="widgetDefaultLinkMode" value="custom"
                                    class="mt-0.5 text-emerald-600 focus:ring-emerald-500" />
                                <span>
                                    <span class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">Custom
                                        Website URL</span>
                                    <span class="block text-xs text-zinc-500 dark:text-zinc-400">Uses your own site KB
                                        links by default</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300"
                            for="widget-article-base-url">
                            Article Base URL (optional)
                        </label>
                        <input id="widget-article-base-url" type="url" wire:model.blur="widgetArticleBaseUrl"
                            placeholder="https://yourcompany.com/kb/article"
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Required format: <span class="font-mono">https://yourcompany.com/kb/article</span>. The
                            widget appends the article slug automatically.
                        </p>
                        @error('widgetArticleBaseUrl')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model.live="widgetOpenInNewTab"
                            class="rounded border-zinc-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500" />
                        Open widget results in a new tab
                    </label>

                    <div class="relative">
                        <pre
                            class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto font-mono text-zinc-700 dark:text-zinc-300"><code>{{ $this->widgetScriptTag }}</code></pre>
                        <button wire:click="copyToClipboard(@js($this->widgetScriptTag), 'widget-script')"
                            type="button"
                            class="absolute top-2 right-2 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 px-3 py-1 rounded text-xs transition-colors text-zinc-600 dark:text-zinc-300 bg-white dark:bg-zinc-800">
                            {{ $copiedKey === 'widget-script' ? __('Copied!') : __('Copy') }}
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="saveWidgetDefaults" type="button"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                            Save Widget Defaults
                        </button>
                    </div>
                </div>

                {{-- Endpoints --}}
                <div class="space-y-4">

                    {{-- List Articles --}}
                    @php $listUrl = $this->baseUrl . '/articles'; @endphp
                    <div
                        class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2 py-0.5 text-xs font-bold rounded bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">GET</span>
                                <code
                                    class="text-sm font-mono text-zinc-800 dark:text-zinc-200 truncate">{{ $listUrl }}</code>
                                <button wire:click="copyToClipboard(@js($listUrl), 'list')"
                                    type="button"
                                    class="ml-auto shrink-0 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 px-3 py-1 rounded text-xs transition-colors text-zinc-600 dark:text-zinc-400">
                                    {{ $copiedKey === 'list' ? __('Copied!') : __('Copy') }}
                                </button>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">List all published articles.</p>
                        </div>
                        <div class="px-6 py-4">
                            <p
                                class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">
                                Response</p>
                            <pre
                                class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto font-mono text-zinc-700 dark:text-zinc-300"><code>[
  {
    "id": 1,
    "title": "Getting Started",
    "slug": "getting-started",
    "excerpt": "...",
    "category": "General",
    "published_at": "2026-03-18T12:00:00Z"
  }
]</code></pre>
                        </div>
                    </div>

                    {{-- Get Single Article --}}
                    @php $singleUrl = $this->baseUrl . '/articles/{slug}'; @endphp
                    <div
                        class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2 py-0.5 text-xs font-bold rounded bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">GET</span>
                                <code
                                    class="text-sm font-mono text-zinc-800 dark:text-zinc-200 truncate">{{ $singleUrl }}</code>
                                <button wire:click="copyToClipboard(@js($this->baseUrl . '/articles/{slug}'), 'single')"
                                    type="button"
                                    class="ml-auto shrink-0 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 px-3 py-1 rounded text-xs transition-colors text-zinc-600 dark:text-zinc-400">
                                    {{ $copiedKey === 'single' ? __('Copied!') : __('Copy') }}
                                </button>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Get a single article by its slug.
                            </p>
                        </div>
                        <div class="px-6 py-4">
                            <p
                                class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">
                                Response</p>
                            <pre
                                class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto font-mono text-zinc-700 dark:text-zinc-300"><code>{
  "id": 1,
  "title": "Getting Started",
  "slug": "getting-started",
  "body": "&lt;p&gt;Full HTML content...&lt;/p&gt;",
  "category": "General",
  "published_at": "2026-03-18T12:00:00Z"
}</code></pre>
                        </div>
                    </div>

                    {{-- Search --}}
                    @php $searchUrl = $this->baseUrl . '/search?q={query}'; @endphp
                    <div
                        class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2 py-0.5 text-xs font-bold rounded bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">GET</span>
                                <code
                                    class="text-sm font-mono text-zinc-800 dark:text-zinc-200 truncate">{{ $searchUrl }}</code>
                                <button wire:click="copyToClipboard(@js($this->baseUrl . '/search?q={query}'), 'search')"
                                    type="button"
                                    class="ml-auto shrink-0 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 px-3 py-1 rounded text-xs transition-colors text-zinc-600 dark:text-zinc-400">
                                    {{ $copiedKey === 'search' ? __('Copied!') : __('Copy') }}
                                </button>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Search published articles by
                                keyword. Pass the search term as the <code
                                    class="text-xs bg-zinc-100 dark:bg-zinc-900 px-1.5 py-0.5 rounded">q</code>
                                query parameter.</p>
                        </div>
                        <div class="px-6 py-4">
                            <p
                                class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">
                                Response</p>
                            <pre
                                class="bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 text-xs overflow-x-auto font-mono text-zinc-700 dark:text-zinc-300"><code>[
  {
    "id": 1,
    "title": "Getting Started",
    "slug": "getting-started",
    "excerpt": "Matched content snippet...",
    "category": "General"
  }
]</code></pre>
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
        </x-dashboard.kb-layout>
    </section>
</div>
