<div>
    <x-ui.flash-message />

    <!-- Search -->
    <div class="mb-6 space-y-3">
        <div class="relative">
            <svg class="absolute left-0 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400 dark:text-zinc-500" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search teams..."
                class="w-full pl-8 pr-4 py-2 bg-transparent border-0 border-b border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-0 transition-colors">
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-clip">
        <table class="w-full text-left text-sm text-zinc-500 dark:text-zinc-400">
            <thead class="bg-zinc-50/50 dark:bg-zinc-900/50 border-b border-zinc-200/50 dark:border-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors group"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Team
                            @if ($sortBy === 'name')
                                <span
                                    class="text-emerald-500 dark:text-emerald-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span
                                    class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Description</th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Members</th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400 text-right">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->teams as $team)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group"
                        wire:key="team-{{ $team->id }}">
                        <td class="px-4 py-4 font-medium text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full ring-2 ring-zinc-100 dark:ring-zinc-800"
                                    style="background-color: {{ $team->color ?? '#14b8a6' }}"></span>
                                {{ $team->name }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-zinc-600 dark:text-zinc-400">
                            {{ Str::limit($team->description, 50) ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-zinc-600 dark:text-zinc-400">
                            <span
                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                {{ $team->members_count }} {{ Str::plural('member', $team->members_count) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="manageMembers({{ $team->id }})"
                                    class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline transition-colors">
                                    Manage Members
                                </button>
                                <div class="h-3 w-px bg-zinc-200 dark:bg-zinc-700"></div>
                                <button wire:click="editTeam({{ $team->id }})"
                                    class="p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button @click="confirmDeletion($wire, {{ $team->id }}, 'deleteTeam', 'team')"
                                    class="p-1 text-zinc-400 hover:text-red-500 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-zinc-500">
                                <svg class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-3" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1">No teams found</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Get started by creating a new
                                    team.</p>
                                <button wire:click="openCreateModal"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors shadow-sm">
                                    Create Team
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
                <flux:heading size="lg">Create Team</flux:heading>

                <form wire:submit="createTeam" class="space-y-4">
                    <flux:field>
                        <flux:label>Team Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g., Tier 1 Support" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Brief description (optional)"
                            rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Color</flux:label>
                        <input type="color" wire:model="color"
                            class="h-10 w-20 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                        <flux:error name="color" />
                    </flux:field>

                    <div class="flex gap-3 pt-4">
                        <flux:button type="button" wire:click="closeCreateModal" variant="ghost" class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Create Team
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
                <flux:heading size="lg">Edit Team</flux:heading>

                <form wire:submit="updateTeam" class="space-y-4">
                    <flux:field>
                        <flux:label>Team Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g., Tier 1 Support" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Brief description (optional)"
                            rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Color</flux:label>
                        <input type="color" wire:model="color"
                            class="h-10 w-20 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                        <flux:error name="color" />
                    </flux:field>

                    <div class="flex gap-3 pt-4">
                        <flux:button type="button" wire:click="closeEditModal" variant="ghost" class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Update Team
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
                <flux:heading size="lg">Delete Team</flux:heading>

                <p class="text-zinc-500 dark:text-zinc-400">
                    Are you sure you want to delete this team? Members will be removed from the team but not deleted.
                    This action cannot be undone.
                </p>

                <div class="flex gap-3 pt-4">
                    <flux:button type="button" wire:click="cancelDelete" variant="ghost" class="flex-1">
                        Cancel
                    </flux:button>
                    <flux:button type="button" wire:click="deleteTeam" variant="primary"
                        class="flex-1 !bg-emerald-500 hover:!bg-emerald-600">
                        Delete Team
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Members Management Modal -->
    @if ($showMembersModal && $this->managingTeam)
        <flux:modal wire:model="showMembersModal" class="md:w-[500px]">
            <div class="space-y-6">
                <flux:heading size="lg">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full"
                            style="background-color: {{ $this->managingTeam->color ?? '#14b8a6' }}"></span>
                        {{ $this->managingTeam->name }} — Members
                    </span>
                </flux:heading>

                <!-- Add Member -->
                <div class="flex items-end gap-2">
                    <flux:field class="flex-1">
                        <flux:label>Add Operator</flux:label>
                        <flux:select wire:model="addMemberId">
                            <flux:select.option value="">Select operator...</flux:select.option>
                            @foreach ($this->availableOperators as $op)
                                <flux:select.option value="{{ $op->id }}">{{ $op->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select wire:model="addMemberRole">
                            <flux:select.option value="member">Member</flux:select.option>
                            <flux:select.option value="lead">Lead</flux:select.option>
                        </flux:select>
                    </flux:field>
                    <flux:button wire:click="addMember" variant="primary" size="sm">Add</flux:button>
                </div>

                <!-- Current Members -->
                <div class="space-y-2">
                    @forelse ($this->managingTeam->members as $member)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-medium">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $member->name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $member->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="toggleMemberRole({{ $member->id }})"
                                    class="px-2 py-1 text-xs font-medium rounded-full border transition-colors {{ $member->pivot->role === 'lead' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20 hover:bg-amber-500/20' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                                    {{ ucfirst($member->pivot->role) }}
                                </button>
                                <button wire:click="removeMember({{ $member->id }})"
                                    wire:confirm="Remove {{ $member->name }} from this team?"
                                    class="p-1 text-zinc-400 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-zinc-500 py-4">No members yet. Add operators above.</p>
                    @endforelse
                </div>

                <div class="flex justify-end pt-2">
                    <flux:button wire:click="closeMembersModal" variant="ghost">Done</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
