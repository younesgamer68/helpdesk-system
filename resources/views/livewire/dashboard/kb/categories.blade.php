<div>
    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />
        
        <x-dashboard.kb-layout heading="Categories" subheading="Manage your Knowledge Base categories">
            <div class="mb-5 flex justify-end">
                <button wire:click="openCreateModal"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Category
                </button>
            </div>

            <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400 w-10"></th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400">Name</th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400 w-1/3">Description</th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400 text-center">Articles</th>
                            <th scope="col" class="px-6 py-4 font-medium text-zinc-500 dark:text-zinc-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800"
                           x-data="{
                               initSortable() {
                                   let el = this.$el;
                                   Sortable.create(el, {
                                       handle: '.drag-handle',
                                       animation: 150,
                                       onEnd: (evt) => {
                                           let items = Array.from(el.children).map((item, index) => {
                                               return { value: item.dataset.id, order: index };
                                           });
                                           $wire.updateOrder(items);
                                       }
                                   });
                               }
                           }"
                           x-init="initSortable()">
                        @forelse($categories as $category)
                        <tr wire:key="category-{{ $category->id }}" data-id="{{ $category->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition bg-zinc-50/30 dark:bg-zinc-800/30">
                            <td class="px-6 py-4 text-zinc-400 cursor-move drag-handle">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </td>
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                @if($category->icon)
                                    <span class="text-xl">{!! $category->icon !!}</span>
                                @else
                                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                @endif
                                {{ $category->name }}
                            </td>
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                    {{ $category->articles_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="openEditModal({{ $category->id }})" class="text-zinc-400 hover:text-teal-500 transition">
                                        <span class="sr-only">Edit</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?" class="text-zinc-400 hover:text-red-500 transition">
                                        <span class="sr-only">Delete</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @foreach($category->children as $child)
                            <tr wire:key="category-{{ $child->id }}" data-id="{{ $child->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-4 text-zinc-400 cursor-move drag-handle pl-8">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                </td>
                                <td class="px-6 py-4 font-medium text-zinc-700 dark:text-zinc-300 pl-12 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    @if($child->icon)
                                        <span class="text-lg">{!! $child->icon !!}</span>
                                    @endif
                                    {{ $child->name }}
                                </td>
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                    {{ $child->description ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                        {{ $child->articles_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button wire:click="openEditModal({{ $child->id }})" class="text-zinc-400 hover:text-teal-500 transition">
                                            <span class="sr-only">Edit</span>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button wire:click="delete({{ $child->id }})" wire:confirm="Are you sure you want to delete this category?" class="text-zinc-400 hover:text-red-500 transition">
                                            <span class="sr-only">Delete</span>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                No categories found. Start by creating one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-dashboard.kb-layout>
    </section>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="md:w-[500px]">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $isEditing ? 'Edit Category' : 'Create Category' }}</flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="name" placeholder="e.g. Policies, Guides, Announcements" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Brief description of this category (optional)" rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Parent Category (Optional)</flux:label>
                    <flux:select wire:model="parentId" placeholder="None (Top Level)">
                        <option value="">None (Top Level)</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="parentId" />
                </flux:field>

                <flux:field>
                    <flux:label>Icon (Emoji or SVG)</flux:label>
                    <flux:input wire:model="icon" placeholder="e.g. 📚 or SVG code" />
                    <flux:error name="icon" />
                </flux:field>

                <div class="flex gap-3 pt-4">
                    <flux:button type="button" wire:click="$set('showModal', false)" variant="ghost" class="flex-1">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="flex-1">
                        Save
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>