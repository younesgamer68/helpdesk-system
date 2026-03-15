<div>
    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />

        <x-dashboard.kb-layout :heading="$article ? 'Edit Article' : 'Create Article'" subheading="Manage your Knowledge Base article content">
            <div class="mb-5 flex justify-end">
                <div class="flex items-center gap-3">
                    <a href="{{ route('kb.articles', Auth::user()->company->slug) }}" wire:navigate
                        class="px-4 py-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">Cancel</a>
                    <button wire:click="saveDraft"
                        class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition">Save
                        Draft</button>
                    <button wire:click="publish"
                        class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition">Publish</button>
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
                                class="w-full text-4xl font-semibold bg-transparent border-0 border-b border-transparent hover:border-zinc-200 dark:hover:border-zinc-700 focus:border-teal-500 focus:ring-0 px-0 py-2 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-600 transition-colors">
                            @error('title')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- TipTap Editor -->
                        <div class="prose dark:prose-invert max-w-none" wire:ignore>
                            <div x-data="{
                                editor: null,
                                content: @entangle('body'),
                                async initEditor(element) {
                                    const { Editor } = await import('https://esm.sh/@tiptap/core');
                                    const StarterKit = (await import('https://esm.sh/@tiptap/starter-kit')).default;
                                    const Link = (await import('https://esm.sh/@tiptap/extension-link')).default;
                                    const Underline = (await import('https://esm.sh/@tiptap/extension-underline')).default;
                            
                                    this.editor = new Editor({
                                        element: element,
                                        extensions: [
                                            StarterKit,
                                            Link.configure({
                                                openOnClick: false,
                                            }),
                                            Underline,
                                        ],
                                        content: this.content,
                                        onUpdate: ({ editor }) => {
                                            this.content = editor.getHTML();
                                        },
                                        editorProps: {
                                            attributes: {
                                                class: 'prose dark:prose-invert max-w-none focus:outline-none min-full',
                                                style: 'min-height: 400px;'
                                            },
                                        },
                                    });
                            
                                    this.$watch('content', value => {
                                        if (value !== this.editor.getHTML()) {
                                            this.editor.commands.setContent(value, false);
                                        }
                                    });
                            
                                    window.addEventListener('media-selected', (event) => {
                                        const url = event.detail.url;
                                        if (this.editor && url) {
                                            this.editor.chain().focus().setImage({ src: url }).run();
                                        }
                                    });
                                },
                                isActive(type) {
                                    if (!this.editor) return false;
                                    return this.editor.isActive(type);
                                },
                                setLink() {
                                    const previousUrl = this.editor.getAttributes('link').href;
                                    const url = window.prompt('URL', previousUrl);
                                    if (url === null) return;
                                    if (url === '') {
                                        this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
                                        return;
                                    }
                                    this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                                }
                            }" x-init="initEditor($refs.editorBox)"
                                class="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden flex flex-col">

                                <!-- Toolbar -->
                                <div
                                    class="flex flex-wrap items-center gap-1 bg-zinc-50 dark:bg-zinc-800/50 p-2 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                                    <button type="button" @click="editor.chain().focus().toggleBold().run()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                            'bold') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Bold">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                                'italic') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Italic">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                                'underline') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Underline">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3m0 13v5H6v-5"></path>
                                        </svg>
                                    </button>

                                    <div class="w-px h-6 bg-zinc-300 dark:bg-zinc-700 mx-1"></div>

                                    <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                                'bulletList') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Bullet List">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                                'orderedList') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Ordered List">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path>
                                        </svg>
                                    </button>

                                    <div class="w-px h-6 bg-zinc-300 dark:bg-zinc-700 mx-1"></div>

                                    <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                                'codeBlock') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Code">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="setLink()"
                                        :class="{ 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white': isActive(
                                            'link') }"
                                        class="p-1.5 rounded text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        title="Link">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Editor Content Box -->
                                <div x-ref="editorBox"
                                    class="p-4 outline-none text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 flex-1 overflow-y-auto min-h-[400px]">
                                </div>

                                <!-- Hidden Sync Input -->
                                <input type="hidden" wire:model="body">
                            </div>
                        </div>

                        <!-- Media Library Integration -->
                        <div class="mt-4 flex justify-end">
                            <flux:button type="button" x-on:click="$dispatch('open-media-library')" variant="ghost"
                                class="text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Insert Image from Library
                            </flux:button>
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
                                    <input type="radio" wire:model="status" value="published"
                                        class="peer sr-only">
                                    <div
                                        class="text-center px-3 py-1.5 text-sm font-medium rounded-md peer-checked:bg-teal-500 peer-checked:text-white peer-checked:shadow-sm text-zinc-500 dark:text-zinc-400 transition">
                                        Published
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Category</label>
                            <select wire:model="kb_category_id"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-teal-500">
                                <option value="">Select a category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('kb_category_id')
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
                                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-lg bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 focus:outline-none focus:border-teal-500 sm:text-sm text-zinc-900 dark:text-zinc-100">
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Tags</label>
                            <input type="text" wire:model="tags" placeholder="e.g. billing, account, update"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-teal-500 sm:text-sm">
                            <p class="text-xs text-zinc-500 mt-1">Comma-separated tags</p>
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center justify-between">
                                Meta Description
                            </label>
                            <textarea wire:model="meta_description" rows="3" placeholder="Brief description for SEO"
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-teal-500 sm:text-sm"></textarea>
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
                                class="w-full bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-teal-500 sm:text-sm">
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
                                            class="mt-2 text-left text-xs text-teal-600 hover:text-teal-700 font-medium">
                                            Revert to this version
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button
                        class="w-full px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition font-medium text-sm flex justify-center items-center gap-2">
                        Save &amp; Publish
                    </button>
                </div>
            </form>

            @livewire('tickets.kb.media-library')
        </x-dashboard.kb-layout>
    </section>

</div>
