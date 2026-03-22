@props([
    'model' => 'message',
    'kbSearch' => '',
    'kbResults' => collect(),
])
<div x-data="tiptapEditor({ model: '{{ $model }}' })"
    class="w-full bg-zinc-50 dark:bg-zinc-800 border-2 border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden focus-within:ring-1 focus-within:ring-zinc-500 dark:focus-within:ring-zinc-500">
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 p-2 border-b border-zinc-200 dark:border-zinc-700/50">
        <button type="button" @mousedown.prevent="" @click="bold()"
            :class="isActive('bold') ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
            class="p-1.5 rounded transition" title="Bold">
            <flux:icon.bold variant="micro" />
        </button>
        {{-- ↓ ADDED: Remove image button (only shows when image is selected) --}}
        <button type="button" @mousedown.prevent="" @click="removeImage()" x-show="isActive('image')"
            class="p-1.5 rounded text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition"
            title="Remove Image">
            <flux:icon.trash variant="micro" />
        </button>
        <button type="button" @mousedown.prevent="" @click="italic()"
            :class="isActive('italic') ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
            class="p-1.5 rounded transition" title="Italic">
            <flux:icon.italic variant="micro" />
        </button>
        <button type="button" @mousedown.prevent="" @click="underline()"
            :class="isActive('underline') ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
            class="p-1.5 rounded transition" title="Underline">
            <flux:icon.underline variant="micro" />
        </button>
        <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
        <button type="button" @mousedown.prevent="" @click="bulletList()"
            :class="isActive('bulletList') ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
            class="p-1.5 rounded transition" title="Bullet List">
            <flux:icon.list-bullet variant="micro" />
        </button>
        <button type="button" @mousedown.prevent="" @click="orderedList()"
            :class="isActive('orderedList') ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
            class="p-1.5 rounded transition" title="Numbered List">
            <flux:icon.numbered-list variant="micro" />
        </button>
        <button type="button" @mousedown.prevent="" @click="codeBlock()"
            :class="isActive('codeBlock') ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
            class="p-1.5 rounded transition" title="Code Block">
            <flux:icon.code-bracket variant="micro" />
        </button>
        <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
        <button type="button" @mousedown.prevent="" @click="setLink(prompt('URL', getLinkUrl()))"
            class="p-1.5 rounded text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 transition"
            title="Link">
            <flux:icon.link variant="micro" />
        </button>
        {{-- ↓ ADDED: Image upload button --}}
        <label
            class="p-1.5 rounded text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 transition cursor-pointer"
            title="Insert Image">
            <flux:icon.photo variant="micro" />
            <input type="file" accept="image/*" multiple class="hidden"
                @change="
        Array.from($event.target.files).forEach((file, index) => {
            setTimeout(() => {
                const reader = new FileReader();
                reader.onload = e => {
                    window.dispatchEvent(new CustomEvent('media-selected', { detail: { url: e.target.result } }));
                };
                reader.readAsDataURL(file);
            }, index * 80);
        });
        $event.target.value = '';
    " />
        </label>
        <button type="button" @mousedown.prevent="" @click="$dispatch('open-media-library')"
            class="p-1.5 rounded text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 transition"
            title="Insert from Library">
            <flux:icon.rectangle-stack variant="micro" />
        </button>
        {{-- ↑ ADDED --}}

        @if ($model === 'message')
            {{-- KB Article Search --}}
            <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
            <div class="relative" x-data="{ kbOpen: false }" @click.outside="kbOpen = false">
                <button type="button" @mousedown.prevent="" @click="kbOpen = !kbOpen"
                    :class="kbOpen ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' :
                        'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900'"
                    class="p-1.5 rounded transition" title="Insert KB Article">
                    <flux:icon.book-open variant="micro" />
                </button>
                <div x-show="kbOpen" x-transition.opacity
                    class="absolute left-0 top-full mt-1 z-50 w-72 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg p-2">
                    <input type="text" wire:model.live.debounce.300ms="kbSearch"
                        placeholder="Search by article title..."
                        class="w-full px-2.5 py-1.5 text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-md text-zinc-900 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                    @if (strlen($kbSearch ?? '') >= 2)
                        <div class="mt-1.5 max-h-48 overflow-y-auto">
                            @forelse ($kbResults as $article)
                                <button type="button"
                                    wire:click="insertKbArticle('{{ $article['slug'] }}', '{{ addslashes($article['title']) }}')"
                                    @click="kbOpen = false"
                                    class="w-full text-left px-2.5 py-1.5 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md truncate">
                                    {{ $article['title'] }}
                                </button>
                            @empty
                                <p class="px-2.5 py-1.5 text-sm text-zinc-400">No articles found.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    {{-- Editor Body --}}
    <div wire:ignore>
        <div x-ref="editorEl"></div>
    </div>
</div>
