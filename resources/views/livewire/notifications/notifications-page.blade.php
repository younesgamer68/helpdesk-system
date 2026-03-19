<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl text-zinc-900 dark:text-zinc-100">Notifications</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('notifications.preferences', ['company' => Auth::user()->company->slug]) }}" wire:navigate
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 hover:text-teal-500 hover:border-teal-500/40 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors"
                title="Notification settings" aria-label="Notification settings">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
            <button wire:click="markAllRead" @class([
                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                'bg-teal-500 hover:bg-teal-600 text-white' => $this->unreadCount > 0,
                'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 cursor-not-allowed' =>
                    $this->unreadCount === 0,
            ])
                @if ($this->unreadCount === 0) disabled @endif>
                Mark all read
            </button>
            <button wire:click="clearAll" @class([
                'px-4 py-2 text-sm font-medium rounded-lg transition-colors border',
                'bg-transparent border-red-500/50 hover:bg-red-500/10 text-red-500' => $this->notifications->isNotEmpty(),
                'bg-transparent border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-600 cursor-not-allowed' => $this->notifications->isEmpty(),
            ])
                @if ($this->notifications->isEmpty()) disabled @endif>
                Clear all
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-1 mb-6 border-b border-zinc-200 dark:border-zinc-800">
        @php
            $tabs = [
                'all' => 'All',
                'unread' => 'Unread',
                'assigned' => 'Assigned',
                'replies' => 'Replies',
            ];

            if (Auth::user()->role === 'admin') {
                $tabs['system'] = 'System';
                $tabs['sla'] = 'SLA';
            }
        @endphp

        @foreach ($tabs as $key => $label)
            <button wire:click="setTab('{{ $key }}')" @class([
                'px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px',
                'border-teal-500 text-teal-400' => $activeTab === $key,
                'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' =>
                    $activeTab !== $key,
            ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- Notifications List -->
    @if ($this->notifications->isEmpty())
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center py-20">
            <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800/80 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-zinc-900 dark:text-zinc-100 text-lg font-medium">You're all caught up!</p>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">No notifications here</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($this->groupedNotifications as $label => $group)
                <!-- Date Group Label -->
                <div>
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2 px-1">
                        {{ $label }}</p>

                    <div
                        class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden divide-y divide-zinc-200 dark:divide-zinc-800/60">
                        @foreach ($group as $notification)
                            <button wire:click="markRead('{{ $notification->id }}')"
                                wire:key="notification-{{ $notification->id }}" @class([
                                    'w-full text-left flex items-center gap-4 px-4 py-3.5 transition-colors relative group',
                                    ' bg-zinc-50 dark:bg-zinc-800/20 hover:bg-zinc-100 dark:hover:bg-zinc-800/50' => is_null(
                                        $notification->read_at),
                                    'border-l-2 border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/30' => !is_null(
                                        $notification->read_at),
                                ])>
                                <!-- Unread Dot -->
                                <div class="flex-shrink-0 w-2 flex justify-center">
                                    @if (is_null($notification->read_at))
                                        <span class="w-2 h-2 bg-teal-500 rounded-full"></span>
                                    @endif
                                </div>

                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    @if (($notification->data['type'] ?? '') === 'assigned')
                                        <div
                                            class="w-9 h-9 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'client_replied')
                                        <div
                                            class="w-9 h-9 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'status_changed')
                                        <div
                                            class="w-9 h-9 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'reassigned')
                                        <div
                                            class="w-9 h-9 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'ticket_submitted')
                                        <div
                                            class="w-9 h-9 rounded-full bg-teal-500/10 flex items-center justify-center text-teal-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'ticket_unassigned')
                                        <div
                                            class="w-9 h-9 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                </path>
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'sla_breached')
                                        <div
                                            class="w-9 h-9 rounded-full bg-red-500/10 flex items-center justify-center text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                </path>
                                            </svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'internal_note')
                                        <div
                                            class="w-9 h-9 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </div>
                                    @else
                                        <div
                                            class="w-9 h-9 rounded-full bg-zinc-500/10 flex items-center justify-center text-zinc-500 dark:text-zinc-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p @class([
                                        'text-sm leading-snug truncate',
                                        'text-zinc-900 dark:text-zinc-100 font-semibold' => is_null(
                                            $notification->read_at),
                                        'text-zinc-600 dark:text-zinc-200' => !is_null($notification->read_at),
                                    ])>
                                        {{ $notification->data['message'] ?? 'New notification' }}
                                    </p>
                                    @if (isset($notification->data['subject']))
                                        <p class="text-xs text-zinc-400 mt-0.5 truncate">
                                            {{ $notification->data['subject'] }}</p>
                                    @endif
                                </div>

                                <!-- Time -->
                                <div class="flex-shrink-0 text-right">
                                    <span
                                        class="text-xs text-zinc-500">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Load More -->
        @if ($this->hasMoreNotifications)
            <div class="mt-6 text-center">
                <button wire:click="loadMore"
                    class="px-6 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg transition-colors border border-zinc-200 dark:border-zinc-700">
                    <span wire:loading.remove wire:target="loadMore">Load more</span>
                    <span wire:loading wire:target="loadMore">Loading...</span>
                </button>
            </div>
        @endif
    @endif
</div>
