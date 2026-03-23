<div>
    <section class="w-full">
        <x-dashboard.kb-layout :heading="$article ? 'Edit Article' : 'Create Article'" subheading="Manage your Knowledge Base article content">
            <div class="mb-5 flex justify-end">
                <div class="flex items-center gap-3">
                    <a href="{{ route('kb.articles', Auth::user()->company->slug) }}" wire:navigate
                        class="px-4 py-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">Cancel</a>
                    <button wire:click="saveDraft"
                        class="px-4 py-2 bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white rounded-lg transition">Save
                        Draft</button>
                    <button wire:click="publish"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">Publish</button>
                </div>
            </div>

            <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - 2/3 width -->
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                        <!-- Large Title Input -->
                        <div class="mb-6">
                            <input type="text" wire:model="title" placeholder="Article title"
                                class="w-full text-4xl font-semibold bg-transparent border-0 border-b border-transparent hover:border-zinc-200 dark:hover:border-zinc-700 focus:border-emerald-500 focus:ring-0 px-0 py-2 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-600 transition-colors">
                            @error('title')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- TipTap Editor -->
                        <div class="prose dark:prose-invert max-w-none">
                            <x-tiptap-editor model="body" />
                        </div>



                    </div>
                </div>

                <!-- Right Column - Settings Panel -->
                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm flex flex-col gap-6">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Publish
                                Status</label>
                            <div
                                class="flex items-center gap-4 bg-zinc-50 dark:bg-zinc-800/50 p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" wire:model="status" value="draft" class="peer sr-only">
                                    <div
                                        class="text-center px-3 py-1.5 text-sm font-medium rounded-md peer-checked:bg-white dark:peer-checked:bg-zinc-600 peer-checked:text-zinc-900 dark:peer-checked:text-white peer-checked:shadow-sm text-zinc-500 dark:text-zinc-400 transition">
                                        Draft
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" wire:model="status" value="published" class="peer sr-only">
                                    <div
                                        class="text-center px-3 py-1.5 text-sm font-medium rounded-md peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm text-zinc-500 dark:text-zinc-400 transition">
                                        Published
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Category</label>
                            <select wire:model="ticket_category_id"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-emerald-500">
                                <option value="">Select a category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('ticket_category_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center justify-between">
                                URL Slug
                                <span class="text-xs text-zinc-400 font-normal">Auto-generated if empty</span>
                            </label>
                            <div class="flex rounded-lg shadow-sm">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-500 sm:text-sm">
                                    /kb/... /
                                </span>
                                <input type="text" wire:model="slug" placeholder="e.g. how-to-login"
                                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-lg bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 focus:outline-none focus:border-emerald-500 sm:text-sm text-zinc-900 dark:text-zinc-100">
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Tags</label>
                            <input type="text" wire:model="tags" placeholder="e.g. billing, account, update"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-emerald-500 sm:text-sm">
                            <p class="text-xs text-zinc-500 mt-1">Comma-separated tags</p>
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center justify-between">
                                Meta Description
                            </label>
                            <textarea wire:model="meta_description" rows="3" placeholder="Brief description for SEO"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-emerald-500 sm:text-sm"></textarea>
                            @error('meta_description')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Schedule Publish -->
                        <div>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center justify-between">
                                Schedule Publish
                                <span class="text-xs text-zinc-400 font-normal">Optional</span>
                            </label>
                            <input type="datetime-local" wire:model="schedule_publish_date"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-emerald-500 sm:text-sm">
                            <p class="text-xs text-zinc-500 mt-1">If set, article will be automatically published on
                                this date.</p>
                            @error('schedule_publish_date')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    @if ($article && $article->exists && $article->versions->count() > 0)
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm flex flex-col gap-4">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Version History</h3>
                            <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                @foreach ($article->versions as $version)
                                    <div
                                        class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $version->created_at->format('M d, Y H:i') }}</span>
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            By {{ optional($version->creator)->name ?? 'System' }}
                                        </div>
                                        <button type="button" wire:click="revertToVersion({{ $version->id }})"
                                            wire:confirm="Are you sure you want to revert to this version? Unsaved changes will be lost."
                                            class="mt-2 text-left text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                            Revert to this version
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button
                        class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-medium text-sm flex justify-center items-center gap-2">
                        Save &amp; Publish
                    </button>
                </div>
            </form>

            @livewire('tickets.kb.media-library')
        </x-dashboard.kb-layout>
    </section>

</div>
