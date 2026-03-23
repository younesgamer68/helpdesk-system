<div class="relative w-full"
    x-data="{
        open: false,
        top: 0,
        left: 0,
        toggle(btn) {
            this.open = !this.open;
            if (this.open) {
                const r = btn.getBoundingClientRect();
                this.left = r.right + 10;
                this.top  = Math.min(r.top, window.innerHeight - 530);
            }
        },
        close() { this.open = false; },
        init() {
            window.addEventListener('livewire:navigate', () => this.close());
        }
    }">

    {{-- ── Trigger button — styled like every other sidebar nav item ── --}}
    <button type="button"
        @click="toggle($el)"
        class="mx-3 h-10 w-[calc(100%-1.5rem)] flex items-center rounded-lg
               transition-all duration-200 hover:translate-x-1
               {{ request()->routeIs('notifications')
                   ? 'bg-[#007260] text-white'
                   : 'text-[#00A983] hover:bg-[#0f3538] hover:text-white' }}">

        <div class="w-10 flex items-center justify-center shrink-0 relative">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 viewBox="0 0 24 24">
                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>

            @if ($this->unreadCount > 0)
                <span class="absolute top-0.5 right-0.5 flex h-4 min-w-4 items-center
                             justify-center rounded-full bg-red-500 px-1 text-[10px]
                             font-bold text-white ring-2 ring-[#17494D]">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </div>

        <span class="sidebar-label">{{ __('Notifications') }}</span>
    </button>

    {{-- ── Panel — teleported to <body>, completely outside sidebar DOM ── --}}
    <template x-teleport="body">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="close()"
             :style="`position:fixed; top:${top}px; left:${left}px; z-index:9999;`"
             class="w-80 md:w-96 min-w-[320px] bg-white dark:bg-zinc-800 border
                    border-zinc-200 dark:border-zinc-700/80 rounded-xl shadow-2xl
                    overflow-hidden origin-top-left"
             style="display:none">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b
                        border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    Notifications
                </h3>
                @if ($this->unreadCount > 0)
                    <button wire:click.stop="markAllRead" type="button"
                        class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors">
                        Mark all read
                    </button>
                @endif
            </div>

            {{-- List --}}
            <div class="max-h-96 overflow-y-auto divide-y divide-zinc-200
                        dark:divide-zinc-800/60 custom-scrollbar">
                @forelse($this->notifications as $notification)
                    <button wire:click="markRead('{{ $notification->id }}')"
                        class="w-full text-left flex gap-3 p-4 transition-colors relative
                               {{ is_null($notification->read_at)
                                   ? 'bg-zinc-50 dark:bg-zinc-800/30 hover:bg-zinc-100 dark:hover:bg-zinc-800/80'
                                   : 'bg-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">

                        @if (is_null($notification->read_at))
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5
                                         bg-emerald-500 rounded-full"></span>
                        @endif

                        {{-- Icon --}}
                        <div class="shrink-0 mt-0.5 ml-2">
                            @php $type = $notification->data['type'] ?? ''; @endphp
                            @if ($type === 'assigned')
                                <div class="w-8 h-8 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @elseif ($type === 'client_replied')
                                <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </div>
                            @elseif ($type === 'status_changed')
                                <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                            @elseif ($type === 'reassigned')
                                <div class="w-8 h-8 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                            @elseif ($type === 'sla_breached')
                                <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                </div>
                            @elseif ($type === 'mentioned')
                                <div class="w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-zinc-500/10 flex items-center justify-center text-zinc-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            @endif
                        </div>

                        {{-- Text --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-zinc-600 dark:text-zinc-200 leading-snug">
                                {{ $notification->data['message'] ?? 'New notification' }}
                            </p>
                            <p class="text-xs text-zinc-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </button>
                @empty
                    <div class="p-8 text-center flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800/80
                                    flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-zinc-400 dark:text-zinc-500" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">You are all caught up 👌</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-center px-4 py-3 border-t border-zinc-200
                        dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <a href="{{ route('notifications', ['company' => Auth::user()->company->slug]) }}"
                   @click="close()"
                   class="text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-900
                          dark:hover:text-zinc-300 font-medium transition-colors">
                    View all notifications →
                </a>
            </div>
        </div>
    </template>

    {{-- ── Toast container — singleton, in <body> ── --}}
    @if (!isset($__toastBellRendered))
        @php($__toastBellRendered = true)
        <template x-teleport="body">
            <div x-data="{
                    toasts: [],
                    ticketUrlTemplate: @js(route('details', ['company' => Auth::user()->company->slug, 'ticket' => '__TICKET__'])),
                    titleFor(type) {
                        return {
                            assigned:         'Ticket Assigned',
                            reassigned:       'Ticket Reassigned',
                            client_replied:   'New Client Reply',
                            status_changed:   'Status Updated',
                            internal_note:    'New Internal Note',
                            ticket_submitted: 'New Ticket',
                            ticket_unassigned:'Unassigned Ticket',
                            sla_breached:     'SLA Breached',
                            priority_changed: 'Priority Changed',
                            team_assigned:    'Team Assigned',
                            mentioned:        'You Were Mentioned',
                        }[type] || 'New Notification';
                    },
                    ticketUrl(payload) {
                        if (!payload.ticket_number || payload.type === 'reassigned') return null;
                        return this.ticketUrlTemplate.replace('__TICKET__', payload.ticket_number);
                    },
                    push(notification) {
                        const id = Date.now() + Math.random();
                        this.toasts.push({
                            id,
                            title:   this.titleFor(notification.type),
                            message: notification.message || notification.subject || 'You have a new notification',
                            url:     this.ticketUrl(notification),
                        });
                        setTimeout(() => this.dismiss(id), 5000);
                    },
                    dismiss(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
                    visit(toast) {
                        if (!toast.url) return;
                        this.dismiss(toast.id);
                        window.location.href = toast.url;
                    },
                    init() {
                        window.addEventListener('helpdesk:notification', e => this.push(e.detail || {}));
                    },
                }"
                class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 items-end
                       pointer-events-none w-80 sm:w-96">

                <template x-for="toast in toasts" :key="toast.id">
                    <div x-show="true"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="translate-y-10 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="translate-y-0 opacity-100"
                        x-transition:leave-end="translate-y-2 opacity-0"
                        @click="visit(toast)"
                        :class="toast.url ? 'cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800' : ''"
                        class="w-full bg-white dark:bg-zinc-900 border border-zinc-200
                               dark:border-zinc-700/80 shadow-2xl rounded-xl p-4 flex items-start
                               space-x-3 pointer-events-auto overflow-hidden relative transition-colors">

                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>

                        <div class="shrink-0 mt-0.5 ml-1 text-emerald-500 dark:text-emerald-400
                                    bg-emerald-500/10 dark:bg-emerald-400/10 p-1.5 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>

                        <div class="flex-1 w-0">
                            <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate"
                               x-text="toast.title"></p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                               x-text="toast.message"></p>
                        </div>

                        <button @click.stop="dismiss(toast.id)"
                            class="bg-transparent rounded-md inline-flex text-zinc-500 dark:text-zinc-400
                                   hover:text-zinc-700 dark:hover:text-zinc-300 focus:outline-none
                                   transition-colors">
                            <span class="sr-only">Close</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </template>
    @endif
</div>