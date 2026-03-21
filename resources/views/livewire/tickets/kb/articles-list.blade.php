<div>
    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />

        <x-dashboard.kb-layout heading="Articles" subheading="Manage your Knowledge Base articles">
            @if (Auth::user()->isAdmin())
                <div class="mb-5 flex justify-end">
                    <a href="{{ route('kb.articles.create', Auth::user()->company->slug) }}" wire:navigate
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        New Article
                    </a>
                </div>
            @endif

            <div class="mb-6 flex gap-4 items-center">
                <div class="relative w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search title..."
                        class="pl-10 w-full bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg px-4 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <select wire:model.live="status"
                    class="bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg px-4 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <div
                class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400">Title</th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400">Category
                            </th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400">Status
                            </th>
                            <th scope="col"
                                class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400 text-center">Views</th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400">Last
                                Updated</th>
                            <th scope="col"
                                class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($articles as $article)
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-zinc-300',
                                    'published' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'archived' => 'bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                                ];
                            @endphp
                            <tr wire:key="article-{{ $article->id }}"
                                class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100 truncate max-w-xs">
                                    {{ $article->title }}
                                </td>
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                    {{ $article->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$article->status] }}">
                                        {{ ucfirst($article->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-zinc-900 dark:text-zinc-100 font-medium">{{ number_format($article->views) }}
                                            views</span>
                                        @php
                                            $totalVotes = $article->helpful_yes + $article->helpful_no;
                                            $helpfulPercent =
                                                $totalVotes > 0
                                                    ? round(($article->helpful_yes / $totalVotes) * 100)
                                                    : 0;
                                        @endphp
                                        <span
                                            class="mt-1 flex items-center gap-1 {{ $helpfulPercent >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($totalVotes > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-400') }}">
                                            @if ($totalVotes > 0)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5">
                                                    </path>
                                                </svg>
                                                {{ $helpfulPercent }}% helpful
                                            @else
                                                No votes yet
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                    {{ $article->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if (Auth::user()->isAdmin())
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('kb.articles.edit', ['company' => Auth::user()->company->slug, 'article' => $article->id]) }}"
                                                wire:navigate class="text-zinc-400 hover:text-emerald-500 transition">
                                                <span class="sr-only">Edit</span>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                            @if ($article->status !== 'archived')
                                                <button wire:click="togglePublish({{ $article->id }})"
                                                    class="text-zinc-400 hover:text-blue-500 transition"
                                                    title="Toggle Publish">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </button>
                                                <button wire:click="archive({{ $article->id }})"
                                                    wire:confirm="Archive this article?"
                                                    class="text-zinc-400 hover:text-yellow-500 transition"
                                                    title="Archive">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                                        </path>
                                                    </svg>
                                                </button>
                                            @endif
                                            <button wire:click="delete({{ $article->id }})"
                                                wire:confirm="Are you sure you want to delete this article?"
                                                class="text-zinc-400 hover:text-red-500 transition" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    No articles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $articles->links() }}
            </div>
        </x-dashboard.kb-layout>
    </section>
</div>
