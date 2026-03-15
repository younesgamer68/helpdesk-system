<div>
    <x-ui.flash-message />

    <!-- Filters Section -->
    <div class="mb-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Filters & Search</h3>
            <div class="flex items-center gap-2">
                @if ($this->hasActiveFilters)
                    <button wire:click="$set('showSaveViewModal', true)"
                        class="px-3 py-1.5 text-sm text-teal-600 dark:text-teal-400 hover:text-teal-900 dark:hover:text-teal-100 transition-colors font-medium">
                        Save current view
                    </button>
                    <button wire:click="clearFilters" wire:confirm="Are you sure you want to clear all active filters?"
                        class="px-3 py-1.5 text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                        Clear all filters
                    </button>
                @endif
            </div>
        </div>

        <!-- Saved Filters (Presets) -->
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mr-2">Saved
                Views:</span>

            @foreach ($this->savedViews as $view)
                <div class="flex items-center gap-1 group">
                    <button wire:click="applyPreset('{{ $view->id }}')"
                        wire:confirm="Apply the '{{ $view->name }}' filter view?"
                        class="px-3 py-1.5 text-xs font-medium rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:border-teal-500/50 hover:bg-teal-500/5 transition-all flex items-center gap-1.5 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        {{ $view->name }}
                    </button>
                    <button wire:click="deleteSavedView({{ $view->id }})"
                        wire:confirm="Are you sure you want to delete this view?"
                        class="opacity-0 group-hover:opacity-100 p-1 text-zinc-400 hover:text-red-500 transition-all">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Input -->
            <div class="relative lg:col-span-2">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search by name or email..."
                    class="w-full pl-10 pr-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
            </div>

            <!-- Role Filter -->
            <div>
                <select wire:model.live="roleFilter"
                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="operator">Operator</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                    <option value="">All Statuses</option>
                    <option value="active">Active Members</option>
                    <option value="pending">Pending Invites</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    @if (count($selected) > 0)
        <div
            class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ count($selected) }}
                    members selected</span>
                <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></div>

                @php
                    $selectedPending = $this->operators
                        ->whereIn('id', $selected)
                        ->every(fn($u) => $u->isPendingInvite());
                    $selectedActive = $this->operators
                        ->whereIn('id', $selected)
                        ->every(fn($u) => $u->isActive() && $u->id !== Auth::id());
                    $hasSelf = in_array(Auth::id(), $selected);
                @endphp

                @if ($selectedPending)
                    <button wire:click="bulkResendInvites"
                        wire:confirm="Are you sure you want to resend invitations to these {{ count($selected) }} members?"
                        wire:loading.attr="disabled" wire:target="bulkResendInvites"
                        class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-700 transition-colors">
                        <span wire:loading.remove wire:target="bulkResendInvites">Resend Invites</span>
                        <span wire:loading wire:target="bulkResendInvites"
                            class="inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                    <button wire:click="bulkRevokeInvites"
                        wire:confirm="Are you sure you want to revoke these invitations?"
                        class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 transition-colors">
                        Revoke Invites
                    </button>
                @endif

                @if ($selectedActive && !$hasSelf)
                    <button wire:click="bulkRemoveMembers"
                        wire:confirm="Are you sure you want to remove these members? Their tickets will be unassigned."
                        class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 transition-colors">
                        Remove Members
                    </button>
                @endif

                @if (!$selectedPending && !$selectedActive && !$hasSelf)
                    <button wire:click="bulkResendInvites"
                        wire:confirm="Are you sure you want to resend invitations to all selected pending members?"
                        wire:loading.attr="disabled" wire:target="bulkResendInvites"
                        class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-700 transition-colors">
                        <span wire:loading.remove wire:target="bulkResendInvites">Resend (Pending)</span>
                        <span wire:loading wire:target="bulkResendInvites"
                            class="inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                    <button wire:click="bulkRemoveMembers"
                        wire:confirm="Are you sure you want to remove active members? Their tickets will be unassigned."
                        class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 transition-colors">
                        Remove (Active)
                    </button>
                @endif
            </div>
            <button wire:click="deselectAll"
                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                Cancel selection
            </button>
        </div>
    @endif

    <!-- Active Filters Display -->
    @if ($this->hasActiveFilters)
        <div class="mb-4 flex flex-wrap gap-2">
            @if ($search)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-teal-500/10 text-teal-400 text-xs font-medium rounded-full border border-teal-500/20">
                    Search: {{ $search }}
                </span>
            @endif
            @if ($roleFilter)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-500/10 text-blue-400 text-xs font-medium rounded-full border border-blue-500/20">
                    Role: {{ ucfirst($roleFilter) }}
                </span>
            @endif
            @if ($statusFilter)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-500/10 text-purple-400 text-xs font-medium rounded-full border border-purple-500/20">
                    Status: {{ $statusFilter === 'pending' ? 'Pending Invites' : 'Active Members' }}
                </span>
            @endif
        </div>
    @endif

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" wire:model.live="selectAll" wire:loading.attr="disabled"
                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-teal-500 focus:ring-teal-500 bg-white dark:bg-zinc-800">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Member
                            @if ($sortBy === 'name')
                                <span class="text-teal-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Role
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Specialities
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Open Tickets
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                        Joined
                    </th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($this->operators as $user)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition-colors cursor-pointer group/row"
                        wire:key="{{ $user->id }}"
                        @click="if (!$event.target.closest('input') && !$event.target.closest('button') && !$event.target.closest('a')) { Livewire.navigate('{{ route('operator.profile', ['company' => Auth::user()->company->slug, 'operator' => $user->id]) }}') }">
                        <td class="px-4 py-3 text-sm">
                            @if ($user->id !== Auth::id())
                                <input type="checkbox" wire:model.live="selected" value="{{ $user->id }}"
                                    wire:key="checkbox-{{ $user->id }}" wire:loading.attr="disabled"
                                    class="rounded border-zinc-300 dark:border-zinc-700 text-teal-500 focus:ring-teal-500 bg-white dark:bg-zinc-800">
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                        @if ($user->id === Auth::id())
                                            You <span
                                                class="text-xs text-zinc-500 font-normal">({{ $user->name }})</span>
                                        @else
                                            {{ $user->name }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($user->role === 'admin')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-500/10 text-orange-400 text-xs font-medium rounded-full border border-orange-500/20">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                        </path>
                                    </svg>
                                    Admin
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-500/10 text-gray-400 text-xs font-medium rounded-full border border-gray-500/20">
                                    Operator
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex flex-col gap-1 items-start">
                                @if ($user->isPendingInvite())
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/10 text-amber-400 text-xs font-medium rounded-full border border-amber-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pending
                                    </span>
                                    @php
                                        $hrsAgo = round($user->created_at->diffInHours(now()));
                                    @endphp
                                    <span class="text-[10px] text-amber-500 font-medium">
                                        Invited {{ $hrsAgo }} {{ Str::plural('hr', $hrsAgo) }} ago
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-500/10 text-green-400 text-xs font-medium rounded-full border border-green-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Active
                                    </span>
                                    <!-- Online/Offline column placeholder - user teammate is adding this -->
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @php
                                    $allSpecialties = collect();
                                    if ($user->specialty) {
                                        $allSpecialties->push($user->specialty);
                                    }
                                    if ($user->categories) {
                                        $allSpecialties = $allSpecialties->concat($user->categories);
                                    }
                                    $allSpecialties = $allSpecialties->unique('id');
                                @endphp

                                @forelse ($allSpecialties->take(3) as $spec)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium border"
                                        style="background-color: {{ $spec->color }}15; color: {{ $spec->color }}; border-color: {{ $spec->color }}30">
                                        {{ $spec->name }}
                                    </span>
                                @empty
                                    <span class="text-zinc-500 italic">Not set</span>
                                @endforelse

                                @if ($allSpecialties->count() > 3)
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700">
                                        +{{ $allSpecialties->count() - 3 }} more
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($user->open_tickets_count > 0)
                                <span
                                    class="font-semibold {{ $user->open_tickets_count > 8 ? 'text-red-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                                    {{ $user->open_tickets_count }}
                                </span>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($user->id !== Auth::user()->id)
                                <div x-data="{ open: false }" @click.away="open = false"
                                    class="relative inline-block text-left">
                                    <button @click="open = !open"
                                        class="p-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 rounded-lg shadow-xl border border-zinc-200 dark:border-zinc-700 z-10 overflow-hidden text-left"
                                        style="display: none;">

                                        @if ($user->isPendingInvite())
                                            <button wire:click="resendInvite({{ $user->id }})"
                                                wire:confirm="Are you sure you want to resend the invitation to {{ $user->name }}?"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                Resend Invite
                                            </button>
                                        @else
                                            <a href="{{ route('operator.profile', ['company' => Auth::user()->company->slug, 'operator' => $user->id]) }}"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                View Profile
                                            </a>

                                            <button
                                                @click="if(confirm('Are you sure you want to change this member\'s role to {{ $user->role === 'admin' ? 'Operator' : 'Admin' }}?')) { $wire.updateRole({{ $user->id }}, '{{ $user->role === 'admin' ? 'operator' : 'admin' }}') }"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                Make {{ $user->role === 'admin' ? 'Operator' : 'Admin' }}
                                            </button>
                                        @endif

                                        <button wire:click="removeUser({{ $user->id }})"
                                            wire:confirm="Are you sure you want to {{ $user->isPendingInvite() ? 'revoke this invitation' : 'remove this team member? Their tickets will be unassigned.' }}?"
                                            @click="open = false"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            {{ $user->isPendingInvite() ? 'Revoke Invite' : 'Remove Member' }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1 mt-4">No team members
                                found</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">We couldn't find anyone matching your
                                current filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->operators->links() }}
    </div>

    <!-- Create Operator Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data="{ showingDiscard: @entangle('showDiscardConfirmation') }"
            @click.self="$wire.attemptCloseCreateModal()" @keydown.escape.window="$wire.attemptCloseCreateModal()">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                @click.stop>
                <!-- Header -->
                <div
                    class="sticky top-0 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Invite Agent</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Send a secure invite link to onboard a
                            new operator or
                            admin.</p>
                    </div>
                    <button wire:click="attemptCloseCreateModal"
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit="createAgent" class="p-6 space-y-6">
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300 mb-2">
                                    Agent Name <span class="text-red-400">*</span>
                                </label>
                                <input wire:model.blur="inviteName" type="text"
                                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    placeholder="John Doe">
                                @error('inviteName')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300 mb-2">
                                    Email Address <span class="text-red-400">*</span>
                                </label>
                                <input wire:model.blur="inviteEmail" type="email"
                                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    placeholder="john@example.com">
                                @error('inviteEmail')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300 mb-2">
                                    Account Role <span class="text-red-400">*</span>
                                </label>
                                <select wire:model.live="inviteRole"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                    <option value="operator">Operator</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('inviteRole')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mt-6">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-blue-400 font-medium">Secure Email Invitation</p>
                                <p class="text-xs text-blue-300/80 mt-1">This user will appear as <b>Pending</b> until
                                    they execute the signed email to assign their permanent secure password.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="button" wire:click="attemptCloseCreateModal"
                            class="flex-1 px-4 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="createAgent"
                            class="flex-1 px-4 py-2 bg-teal-500 hover:bg-teal-600 disabled:opacity-70 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex flex-nowrap items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="createAgent"
                                class="inline-flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Dispatch Invite
                            </span>
                            <span wire:loading wire:target="createAgent"
                                class="flex flex-row items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none"
                                    aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Sending Invite...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Discard Confirmation Dialog -->
                <div x-show="showingDiscard" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-black/60 flex items-center justify-center p-4" style="display: none;">
                    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-2xl max-w-md w-full p-6"
                        @click.stop>
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-amber-500/10 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Discard invitation?
                                </h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                                    You have unsaved changes. If you close now, your progress will be lost.
                                </p>
                                <div class="flex gap-3 mt-6">
                                    <button wire:click="cancelDiscard"
                                        class="flex-1 px-4 py-2 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-900 dark:text-zinc-200 font-medium rounded-lg transition-colors">
                                        Keep editing
                                    </button>
                                    <button wire:click="confirmDiscard"
                                        class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors">
                                        Discard
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Invite Modal -->
    @if ($showBulkInviteModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="$wire.closeBulkInviteModal()" @keydown.escape.window="$wire.closeBulkInviteModal()">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                @click.stop>
                <!-- Header -->
                <div
                    class="sticky top-0 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Bulk Invite Agents</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Invite multiple team members by
                            entering their email addresses.</p>
                    </div>
                    <button wire:click="closeBulkInviteModal"
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit="processBulkInvite" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300 mb-2">
                            Email Addresses <span class="text-red-400">*</span>
                        </label>
                        <p class="text-xs text-zinc-500 mb-2">Enter emails separated by commas or new lines.</p>
                        <textarea wire:model="bulkInviteEmails" rows="5"
                            class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                            placeholder="john@example.com, jane@example.com"></textarea>
                        @error('bulkInviteEmails')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300 mb-2">
                            Account Role for all <span class="text-red-400">*</span>
                        </label>
                        <select wire:model.live="bulkInviteRole"
                            class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                            <option value="operator">Operator</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('bulkInviteRole')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mt-6">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-blue-400 font-medium">Bulk Secure Email Invitations</p>
                                <p class="text-xs text-blue-300/80 mt-1">Each email will receive a unique signed link.
                                    Duplicate or existing emails will be automatically skipped.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="button" wire:click="closeBulkInviteModal"
                            class="flex-1 px-4 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="processBulkInvite"
                            class="flex-1 px-4 py-2 bg-teal-500 hover:bg-teal-600 disabled:opacity-70 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex flex-nowrap items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="processBulkInvite"
                                class="inline-flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Dispatch Bulk Invites
                            </span>

                            <span wire:loading wire:target="processBulkInvite"
                                class="inline-flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                    aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Sending Invites...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Save View Modal -->
    @if ($showSaveViewModal)
        <div class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4"
            @click.self="$wire.set('showSaveViewModal', false)"
            @keydown.escape.window="$wire.set('showSaveViewModal', false)">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-xl max-w-md w-full p-6 animate-in zoom-in-95 duration-200"
                @click.stop>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Save current view</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">Give this filtered view a name to access it
                    later.</p>

                <form wire:submit="saveCustomView" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">View
                            Name</label>
                        <input wire:model="customViewName" type="text" placeholder="e.g., Pending Admins"
                            class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                        @error('customViewName')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="$wire.set('showSaveViewModal', false)"
                            class="flex-1 px-4 py-2 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-200 font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors">
                            Save View
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
