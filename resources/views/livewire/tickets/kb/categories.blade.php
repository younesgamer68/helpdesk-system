<div>
    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />

        <x-dashboard.kb-layout heading="Categories" subheading="Available categories for your Knowledge Base">
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900 rounded-lg">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    KB categories are now managed in <a
                        href="{{ route('categories', ['company' => Auth::user()->company->slug]) }}"
                        class="font-semibold underline hover:no-underline">Settings > Categories</a>.
                    Create and manage categories there, then use them for your KB articles.
                </p>
            </div>

            @if ($categories->isEmpty())
                <div
                    class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 px-6 py-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-700 mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-1">No categories yet</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">Create categories in Settings to organize your KB
                        articles.</p>
                    <a href="{{ route('categories', ['company' => Auth::user()->company->slug]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Go to Settings
                    </a>
                </div>
            @else
                <div
                    class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                    <table class="w-full text-left text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                        <thead
                            class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 border-b border-zinc-100 dark:border-zinc-800">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-xs font-medium uppercase tracking-wider">Name
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium uppercase tracking-wider w-1/3">
                                    Description</th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-center">Published
                                    Articles</th>
                            </tr>
                        </thead>
                        @foreach ($categories as $category)
                            {{-- One tbody per category group so Alpine x-data scopes correctly --}}
                            <tbody x-data="{ open: true }"
                                class="divide-y divide-zinc-100 dark:divide-zinc-800 border-b border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                                {{-- Parent category row --}}
                                <tr wire:key="category-{{ $category->id }}"
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ $category->children->isNotEmpty() ? 'cursor-pointer select-none' : '' }}"
                                    @if ($category->children->isNotEmpty()) @click="open = !open" @endif>
                                    <td class="px-4 py-4 font-medium text-zinc-900 dark:text-zinc-100">
                                        <div class="flex items-center gap-2">
                                            @if ($category->children->isNotEmpty())
                                                <span
                                                    class="text-zinc-400 dark:text-zinc-500 transition-transform duration-200 inline-flex"
                                                    :class="open ? 'rotate-90' : 'rotate-0'">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="w-3.5 h-3.5 inline-block shrink-0"></span>
                                            @endif
                                            {{ $category->name }}
                                            @if ($category->children->isNotEmpty())
                                                <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">
                                                    ({{ $category->children->count() }}
                                                    {{ Str::plural('subcategory', $category->children->count()) }})
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                        {{ $category->description ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            {{ $category->kb_articles_count }}
                                        </span>
                                    </td>
                                </tr>
                                {{-- Child category rows --}}
                                @foreach ($category->children as $child)
                                    <tr wire:key="category-{{ $child->id }}" x-show="open"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="bg-zinc-50/50 dark:bg-zinc-800/20 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                            <div class="flex items-center gap-1.5 pl-8">
                                                <span
                                                    class="text-zinc-300 dark:text-zinc-600 text-sm leading-none">└</span>
                                                {{ $child->name }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-zinc-400 dark:text-zinc-500 truncate max-w-xs text-sm">
                                            {{ $child->description ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-500">
                                                {{ $child->kb_articles_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endforeach
                    </table>
                </div>
            @endif
        </x-dashboard.kb-layout>
    </section>
</div>
