<div>
    <x-ui.flash-message />

    <!-- Filters Section -->
    <div class="mb-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Search</h3>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <!-- Search Input -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text"
                    placeholder="Search by name or description..."
                    class="w-full pl-10 pr-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Name
                            @if ($sortBy === 'name')
                                <span class="text-emerald-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Description
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('default_priority')">
                        <div class="flex items-center gap-1">
                            Default Priority
                            @if ($sortBy === 'default_priority')
                                <span class="text-emerald-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800" x-data="{ openMap: {} }">
                @php
                    $priorityColors = [
                        'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                        'medium' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                        'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                    ];
                @endphp

                @forelse ($this->categories as $category)
                    @if ($search)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition-colors"
                            wire:key="category-search-{{ $category->id }}">
                            <td class="px-4 py-3 text-sm">
                                <div>
                                    <span
                                        class="font-medium text-zinc-900 dark:text-zinc-100">{{ $category->name }}</span>
                                    @if ($category->parent)
                                        <p class="mt-1 text-xs text-zinc-500">Child of {{ $category->parent->name }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ Str::limit($category->description, 50) ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full border {{ $priorityColors[$category->default_priority] }}">
                                    {{ ucfirst($category->default_priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="editCategory({{ $category->id }})"
                                        class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $category->id }})"
                                        class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-red-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                        title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition-colors {{ $category->children->isNotEmpty() ? 'cursor-pointer select-none' : '' }}"
                            wire:key="category-parent-{{ $category->id }}"
                            @if ($category->children->isNotEmpty()) @click="openMap[{{ $category->id }}] = !(openMap[{{ $category->id }}] ?? true)" @endif>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    @if ($category->children->isNotEmpty())
                                        <span
                                            class="text-zinc-400 dark:text-zinc-500 transition-transform duration-200 inline-flex shrink-0"
                                            :class="(openMap[{{ $category->id }}] ?? true) ? 'rotate-90' : 'rotate-0'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="w-3.5 h-3.5 inline-block shrink-0"></span>
                                    @endif
                                    <span
                                        class="font-medium text-zinc-900 dark:text-zinc-100">{{ $category->name }}</span>
                                    @if ($category->children->isNotEmpty())
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                            ({{ $category->children->count() }}
                                            {{ Str::plural('sub', $category->children->count()) }})
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ Str::limit($category->description, 50) ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full border {{ $priorityColors[$category->default_priority] }}">
                                    {{ ucfirst($category->default_priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right" @click.stop>
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openCreateModal({{ $category->id }})"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-emerald-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                        + Add subcategory
                                    </button>
                                    <button wire:click="editCategory({{ $category->id }})"
                                        class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $category->id }})"
                                        class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-red-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                        title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @foreach ($category->children as $childCategory)
                            <tr class="bg-zinc-50/70 hover:bg-zinc-50 dark:bg-zinc-900/20 dark:hover:bg-zinc-900/40 transition-colors"
                                wire:key="category-child-{{ $childCategory->id }}"
                                x-show="openMap[{{ $category->id }}] ?? true"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-start gap-3 pl-6">
                                        <span class="mt-2 h-px w-4 bg-zinc-300 dark:bg-zinc-700"></span>
                                        <div>
                                            <span
                                                class="font-medium text-zinc-900 dark:text-zinc-100">{{ $childCategory->name }}</span>
                                            <p class="mt-1 text-xs text-zinc-500">Under {{ $category->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ Str::limit($childCategory->description, 50) ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full border {{ $priorityColors[$childCategory->default_priority] }}">
                                        {{ ucfirst($childCategory->default_priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="editCategory({{ $childCategory->id }})"
                                            class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $childCategory->id }})"
                                            class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-red-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <p class="text-zinc-500 dark:text-zinc-400">No categories found</p>
                                <button x-data @click="$dispatch('open-create-category-modal')"
                                    class="mt-2 px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                                    Add your first category
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- Create Modal -->
    @if ($showCreateModal)
        <flux:modal wire:model="showCreateModal" class="md:w-96">
            <div class="space-y-6">
                <flux:heading size="lg">Add New Category</flux:heading>

                <form wire:submit="createCategory" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g., Network, Software, Hardware" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description"
                            placeholder="Brief description of this category (optional)" rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Parent Category</flux:label>
                        @if ($lockParentSelection && $parent_id)
                            <flux:input :value="$this->parentCategoriesForSelect->firstWhere('id', $parent_id)?->name"
                                readonly />
                        @else
                            <flux:select wire:model="parent_id">
                                <flux:select.option value="">No parent</flux:select.option>
                                @foreach ($this->parentCategoriesForSelect as $parentCategory)
                                    <flux:select.option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <p class="mt-1 text-xs text-zinc-500">Categories can only be nested one level deep.</p>
                        <flux:error name="parent_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Default Priority</flux:label>
                        <flux:select wire:model="default_priority">
                            <flux:select.option value="low">Low</flux:select.option>
                            <flux:select.option value="medium">Medium</flux:select.option>
                            <flux:select.option value="high">High</flux:select.option>
                            <flux:select.option value="urgent">Urgent</flux:select.option>
                        </flux:select>
                        <flux:error name="default_priority" />
                    </flux:field>

                    <div class="flex gap-3 pt-4">
                        <flux:button type="button" wire:click="closeCreateModal" variant="ghost" class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Create Category
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    @endif

    <!-- Edit Modal -->
    @if ($showEditModal)
        <flux:modal wire:model="showEditModal" class="md:w-96">
            <div class="space-y-6">
                <flux:heading size="lg">Edit Category</flux:heading>

                <form wire:submit="updateCategory" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g., Network, Software, Hardware" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description"
                            placeholder="Brief description of this category (optional)" rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Parent Category</flux:label>
                        <flux:select wire:model="parent_id">
                            <flux:select.option value="">No parent</flux:select.option>
                            @foreach ($this->parentCategoriesForSelect as $parentCategory)
                                <flux:select.option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <p class="mt-1 text-xs text-zinc-500">Categories can only be nested one level deep.</p>
                        <flux:error name="parent_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Default Priority</flux:label>
                        <flux:select wire:model="default_priority">
                            <flux:select.option value="low">Low</flux:select.option>
                            <flux:select.option value="medium">Medium</flux:select.option>
                            <flux:select.option value="high">High</flux:select.option>
                            <flux:select.option value="urgent">Urgent</flux:select.option>
                        </flux:select>
                        <flux:error name="default_priority" />
                    </flux:field>

                    <div class="flex gap-3 pt-4">
                        <flux:button type="button" wire:click="closeEditModal" variant="ghost" class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Update Category
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteConfirmation)
        <flux:modal wire:model="showDeleteConfirmation" class="md:w-96">
            <div class="space-y-6">
                <flux:heading size="lg">Delete Category</flux:heading>

                <p class="text-zinc-500 dark:text-zinc-400">
                    {{ $deleteConfirmationMessage }}
                </p>

                <div class="flex gap-3 pt-4">
                    <flux:button type="button" wire:click="cancelDelete" variant="ghost" class="flex-1">
                        Cancel
                    </flux:button>
                    <flux:button type="button" wire:click="deleteCategory" variant="danger" class="flex-1">
                        Delete Category
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
