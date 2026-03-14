<div class="relative inline-flex items-center">
    <!-- Using Flux component so floating-ui handles putting it above the sidebar context -->
    <flux:dropdown position="bottom" align="start">
        <button type="button"
            class="relative p-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors rounded-full ">
            <flux:icon.bell class="size-5 shrink-0 text-teal-500 hover:text-white" />

            @if ($this->unreadCount > 0)
                <span
                    class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-zinc-900 border-none">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </button>

        <flux:menu
            class="w-80 sm:w-80 md:w-96 !p-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/80 rounded-xl shadow-2xl overflow-hidden min-w-[320px]">
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Notifications</h3>
                @if ($this->unreadCount > 0)
                    <button wire:click.stop="markAllRead" type="button"
                        class="text-xs text-teal-400 hover:text-teal-300 font-medium transition-colors">
                        Mark all read
                    </button>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto divide-y divide-zinc-200 dark:divide-zinc-800/60 custom-scrollbar">
                @forelse($this->notifications as $notification)
                    <button wire:click="markRead('{{ $notification->id }}')"
                        class="w-full text-left flex gap-3 p-4 transition-colors relative {{ is_null($notification->read_at) ? 'bg-zinc-50 dark:bg-zinc-800/30 hover:bg-zinc-100 dark:hover:bg-zinc-800/80' : 'bg-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">

                        @if (is_null($notification->read_at))
                            <span
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-teal-500 rounded-full"></span>
                        @endif

                        <div class="flex-shrink-0 mt-0.5 ml-2">
                            @if ($notification->data['type'] === 'assigned')
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @elseif($notification->data['type'] === 'client_replied')
                                <div
                                    class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                </div>
                            @elseif($notification->data['type'] === 'status_changed')
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            @elseif($notification->data['type'] === 'reassigned')
                                <div
                                    class="w-8 h-8 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="w-8 h-8 rounded-full bg-zinc-500/10 flex items-center justify-center text-zinc-500 dark:text-zinc-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-zinc-600 dark:text-zinc-200 leading-snug">
                                {{ $notification->data['message'] ?? 'New notification' }}</p>
                            <p class="text-xs text-zinc-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </button>
                @empty
                    <div class="p-8 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800/80 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">You are all caught up 👌</p>
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-center px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <a href="{{ route('notifications', ['company' => Auth::user()->company->slug]) }}"
                    class="text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-300 font-medium transition-colors">
                    View all notifications →
                </a>
            </div>

        </flux:menu>
    </flux:dropdown>

    <!-- Discord-style Toast Container (Bottom Right) -->
    <div x-data="{
        notifications: [],
        init() {
            $wire.on('show-notification-toast', (event) => {
                const id = Date.now() + Math.random();
    
                this.notifications.push({
                    id: id,
                    title: event.title || 'Notification',
                    message: event.message,
                    url: event.url || null,
                    type: event.type || null
                });
    
                setTimeout(() => {
                    this.remove(id);
                }, 5000);
            });
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        },
        visit(notification) {
            if (notification.url) {
                this.remove(notification.id);
                window.location.href = notification.url;
            }
        }
    }"
        class="fixed bottom-24 right-6 z-[100] flex flex-col gap-3 items-end pointer-events-none w-80 sm:w-96">

        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-10 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
                @click="visit(notification)" :class="notification.url ? 'cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800' : ''"
                class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700/80 shadow-2xl rounded-xl p-4 flex items-start space-x-3 pointer-events-auto overflow-hidden relative transition-colors">

                <!-- Subtle side accent line -->
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-500"></div>

                <!-- Bell Icon -->
                <div class="flex-shrink-0 mt-0.5 ml-1 text-teal-500 dark:text-teal-400 bg-teal-500/10 dark:bg-teal-400/10 p-1.5 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                </div>

                <div class="flex-1 w-0">
                    <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="notification.title"></p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-text="notification.message"></p>
                </div>

                <!-- Close Button -->
                <div class="flex-shrink-0 flex">
                    <button @click.stop="remove(notification.id)"
                        class="bg-transparent rounded-md inline-flex text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 focus:outline-none transition-colors">
                        <span class="sr-only">Close</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
