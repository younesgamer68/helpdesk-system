<div>
    <x-ui.flash-message />

    <!-- Filters Bar -->
    <div class="mb-6 space-y-4">
        <!-- Top Row: Search and Actions -->
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <!-- Search -->
            <div class="relative w-full xl:max-w-sm">
                <svg class="pointer-events-none absolute left-0 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search by name or email..."
                    class="w-full border-0 border-b border-zinc-200 bg-transparent py-2 pl-6 pr-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500">
            </div>

            <!-- Right Side: Filters -->
            <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                <!-- Role Filter -->
                <flux:dropdown>
                    <button type="button"
                        class="flex items-center justify-between min-w-[150px] rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-600 focus:border-emerald-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                        <span>
                            @php
                                $roleLabels = [
                                    '' => 'All Roles',
                                    'admin' => 'Admin',
                                    'operator' => 'Operator',
                                ];
                            @endphp
                            {{ $roleLabels[$roleFilter] ?? 'All Roles' }}
                        </span>
                        <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <flux:menu class="w-[150px]">
                        <flux:menu.radio.group wire:model.live="roleFilter">
                            <flux:menu.radio value=""
                                class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                All Roles</flux:menu.radio>
                            <flux:menu.radio value="admin"
                                class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                Admin</flux:menu.radio>
                            <flux:menu.radio value="operator"
                                class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                Operator</flux:menu.radio>
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                <!-- Status Filter -->
                <flux:dropdown>
                    <button type="button"
                        class="flex items-center justify-between min-w-[150px] rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-600 focus:border-emerald-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                        <span>
                            @php
                                $statusLabels = [
                                    '' => 'All Statuses',
                                    'active' => 'Active Members',
                                    'pending' => 'Pending Invites',
                                ];
                            @endphp
                            {{ $statusLabels[$statusFilter] ?? 'All Statuses' }}
                        </span>
                        <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <flux:menu class="w-[150px]">
                        <flux:menu.radio.group wire:model.live="statusFilter">
                            <flux:menu.radio value=""
                                class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                All Statuses</flux:menu.radio>
                            <flux:menu.radio value="active"
                                class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                Active Members</flux:menu.radio>
                            <flux:menu.radio value="pending"
                                class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                                Pending Invites</flux:menu.radio>
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                @if ($this->hasActiveFilters)
                    <button wire:click="$set('showSaveViewModal', true)"
                        class="text-xs font-medium text-teal-600 hover:text-teal-700 transition-colors">
                        Save View
                    </button>
                    <button wire:click="clearFilters"
                        class="text-xs text-zinc-500 hover:text-zinc-700 transition-colors">
                        Clear
                    </button>
                @endif
            </div>
        </div>

        <!-- Saved Views (if any) -->
        @if (count($this->savedViews) > 0)
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider mr-1">Views:</span>
                @foreach ($this->savedViews as $view)
                    <div class="flex items-center gap-1 group">
                        <button wire:click="applyPreset('{{ $view->id }}')"
                            class="px-2 py-1 text-xs font-medium rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-400 transition-colors">
                            {{ $view->name }}
                        </button>
                        <button wire:click="deleteSavedView({{ $view->id }})" wire:confirm="Delete view?"
                            class="opacity-0 group-hover:opacity-100 text-zinc-300 hover:text-red-500 transition-opacity">
                            &times;
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Bulk Actions Bar -->
    @if (count($selected) > 0)
        <div
            class="mb-6 p-2 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300 ml-2">{{ count($selected) }}
                    selected</span>
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
                        class="text-xs font-medium text-teal-600 hover:text-teal-700 transition-colors">
                        Resend Invites
                    </button>
                    <button wire:click="bulkRevokeInvites"
                        wire:confirm="Are you sure you want to revoke these invitations?"
                        class="text-xs font-medium text-red-600 hover:text-red-700 transition-colors">
                        Revoke
                    </button>
                @endif

                @if ($selectedActive && !$hasSelf)
                    <button wire:click="bulkRemoveMembers"
                        wire:confirm="Are you sure you want to remove these members? Their tickets will be unassigned."
                        class="text-xs font-medium text-red-600 hover:text-red-700 transition-colors">
                        Remove Members
                    </button>
                @endif

                @if (!$selectedPending && !$selectedActive && !$hasSelf)
                    <button wire:click="bulkResendInvites"
                        class="text-xs font-medium text-teal-600 hover:text-teal-700 transition-colors">
                        Resend (Pending)
                    </button>
                    <button wire:click="bulkRemoveMembers"
                        class="text-xs font-medium text-red-600 hover:text-red-700 transition-colors">
                        Remove (Active)
                    </button>
                @endif
            </div>
            <button wire:click="deselectAll" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors mr-2">
                Cancel
            </button>
        </div>
    @endif

    <!-- Active Filters Display -->
    @if ($this->hasActiveFilters)
        <div class="mb-4 flex flex-wrap gap-2">
            @if ($search)
                <span class="text-xs text-zinc-500">
                    Searching for "{{ $search }}"
                </span>
            @endif
            @if ($roleFilter)
                <span class="text-xs text-zinc-500">
                    Role: {{ ucfirst($roleFilter) }}
                </span>
            @endif
            @if ($statusFilter)
                <span class="text-xs text-zinc-500">
                    Status: {{ $statusFilter === 'pending' ? 'Pending' : 'Active' }}
                </span>
            @endif
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-clip">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left w-10">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="rounded border-zinc-300 text-teal-600 focus:ring-teal-600 dark:border-zinc-700 dark:bg-zinc-900">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer group hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Member
                            @if ($sortBy === 'name')
                                <span
                                    class="text-teal-500 dark:text-teal-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span
                                    class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Specialities</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">Teams
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">Activity
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">Tickets
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-400">Joined
                    </th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->operators as $user)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors group/row {{ $user->isPendingInvite() ? 'cursor-default' : 'cursor-pointer' }}"
                        wire:key="{{ $user->id }}"
                        @click="if (!{{ $user->isPendingInvite() ? 'true' : 'false' }} && !$event.target.closest('input') && !$event.target.closest('button') && !$event.target.closest('a')) { Livewire.navigate('{{ route('operator.profile', ['company' => Auth::user()->company->slug, 'operator' => $user->id]) }}') }">
                        <td class="px-4 py-4 text-left" wire:click.stop>
                            @if ($user->id !== Auth::id())
                                <input type="checkbox" wire:model.live="selected" value="{{ $user->id }}"
                                    wire:key="checkbox-{{ $user->id }}" wire:loading.attr="disabled"
                                    class="w-4 h-4 rounded border-zinc-200 dark:border-zinc-700 text-teal-500 focus:ring-teal-500 bg-zinc-50 dark:bg-zinc-800">
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 text-xs font-medium border border-zinc-200 dark:border-zinc-700">
                                    {{ $user->initials() }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                        @if ($user->id === Auth::id())
                                            You <span class="text-zinc-400 font-normal">({{ $user->name }})</span>
                                        @else
                                            {{ $user->name }}
                                        @endif
                                    </span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if ($user->role === 'admin')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/10 text-amber-600 dark:text-amber-500 border border-amber-500/20">
                                    Admin
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                    Operator
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex flex-col gap-1 items-start">
                                @if ($user->isPendingInvite())
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/10 text-amber-600 dark:text-amber-500 border border-amber-500/20">
                                        Pending
                                    </span>
                                    @php
                                        $hoursRemaining = $user->inviteHoursRemaining();
                                    @endphp
                                    @if (!is_null($hoursRemaining))
                                        <span
                                            class="text-[10px] {{ !is_null($hoursRemaining) && $hoursRemaining <= 0 ? 'text-red-500' : 'text-zinc-400' }}">
                                            {{ !is_null($hoursRemaining) && $hoursRemaining <= 0 ? 'Expired' : $hoursRemaining . 'h left' }}
                                        </span>
                                    @endif
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-500 border border-emerald-500/20">
                                        Active
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
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
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                        {{ $spec->name }}
                                    </span>
                                @empty
                                    <span class="text-zinc-400 text-xs">-</span>
                                @endforelse

                                @if ($allSpecialties->count() > 3)
                                    <span
                                        class="text-[10px] text-zinc-400 font-medium">+{{ $allSpecialties->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @forelse ($user->teams->take(2) as $team)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                            style="background-color: {{ $team->color }}"></span>
                                        {{ $team->name }}
                                    </span>
                                @empty
                                    <span class="text-zinc-400 text-xs">-</span>
                                @endforelse
                                @if ($user->teams->count() > 2)
                                    <span
                                        class="text-[10px] text-zinc-400 font-medium">+{{ $user->teams->count() - 2 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center gap-1.5">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ $user->status == 'offline' ? 'bg-zinc-300 dark:bg-zinc-600' : 'bg-emerald-500' }}"></span>
                                <span
                                    class="text-xs {{ $user->status == 'offline' ? 'text-zinc-500' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            @if ($user->open_tickets_count > 0)
                                <span
                                    class="font-medium {{ $user->open_tickets_count > 8 ? 'text-red-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                                    {{ $user->open_tickets_count }}
                                </span>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-4 py-4 text-right" wire:click.stop>
                            @if ($user->id !== Auth::user()->id)
                                <div x-data="{ open: false }" @click.away="open = false"
                                    class="relative inline-block text-left">
                                    <button @click="open = !open"
                                        class="p-1 text-zinc-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
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

                                        <button
                                            @click="open = false; confirmAction($wire, {{ $user->id }}, 'removeUser', 'Are you sure?', '{{ $user->isPendingInvite() ? 'Revoke this invitation?' : 'Remove this team member? Their tickets will be unassigned.' }}', 'Yes, {{ $user->isPendingInvite() ? 'revoke' : 'remove' }} it!')"
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
        <flux:modal wire:model="showCreateModal" class="md:w-[600px]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Invite Agent</flux:heading>
                    <flux:subheading>Send a secure invite link to onboard a new operator or admin.</flux:subheading>
                </div>

                <form wire:submit="createAgent" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>Agent Name</flux:label>
                                <flux:input wire:model.blur="inviteName" placeholder="John Doe" />
                                <flux:error name="inviteName" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Email Address</flux:label>
                                <flux:input wire:model.blur="inviteEmail" type="email"
                                    placeholder="john@example.com" />
                                <flux:error name="inviteEmail" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Account Role</flux:label>
                                <flux:select wire:model.live="inviteRole" placeholder="Select role...">
                                    <flux:select.option value="operator">Operator</flux:select.option>
                                    <flux:select.option value="admin">Admin</flux:select.option>
                                </flux:select>
                                <flux:error name="inviteRole" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
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

                    <div class="flex gap-3">
                        <flux:button type="button" wire:click="attemptCloseCreateModal" variant="ghost"
                            class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Dispatch Invite
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>

        <!-- Discard Confirmation Dialog -->
        <flux:modal wire:model="showDiscardConfirmation" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Discard invitation?</flux:heading>
                    <flux:subheading>You have unsaved changes. If you close now, your progress will be lost.
                    </flux:subheading>
                </div>

                <div class="flex gap-3">
                    <flux:button wire:click="cancelDiscard" variant="ghost" class="flex-1">
                        Keep editing
                    </flux:button>
                    <flux:button wire:click="confirmDiscard" variant="primary"
                        class="flex-1 !bg-emerald-500 hover:!bg-emerald-600">
                        Discard
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Bulk Invite Modal -->
    @if ($showBulkInviteModal)
        <flux:modal wire:model="showBulkInviteModal" class="md:w-[600px]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Bulk Invite Agents</flux:heading>
                    <flux:subheading>Invite multiple team members by entering their email addresses.</flux:subheading>
                </div>

                <form wire:submit="processBulkInvite" class="space-y-6">
                    <flux:field>
                        <flux:label>Email Addresses</flux:label>
                        <flux:description>Enter emails separated by commas or new lines</flux:description>
                        <flux:textarea wire:model="bulkInviteEmails" rows="5"
                            placeholder="john@example.com, jane@example.com" />
                        <flux:error name="bulkInviteEmails" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Account Role for all</flux:label>
                        <flux:select wire:model.live="bulkInviteRole" placeholder="Select role...">
                            <flux:select.option value="operator">Operator</flux:select.option>
                            <flux:select.option value="admin">Admin</flux:select.option>
                        </flux:select>
                        <flux:error name="bulkInviteRole" />
                    </flux:field>

                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
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

                    <div class="flex gap-3">
                        <flux:button type="button" wire:click="closeBulkInviteModal" variant="ghost"
                            class="flex-1">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" class="flex-1">
                            Dispatch Bulk Invites
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
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
                            class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
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
                            class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-lg transition-colors">
                            Save View
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
