<div class="relative w-full">

    {{-- ── Trigger button — navigates directly to notifications page ── --}}
    <a href="{{ route('notifications', ['company' => Auth::user()->company->slug]) }}" wire:navigate
        class="mx-3 h-10 w-[calc(100%-1.5rem)] flex items-center rounded-lg
               transition-all duration-200 hover:translate-x-1 no-underline
               {{ request()->routeIs('notifications')
                   ? 'bg-zinc-800 text-white'
                   : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">

        <div class="w-10 flex items-center justify-center shrink-0 relative">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                stroke-linejoin="round" viewBox="0 0 24 24">
                <path
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>

            @if ($this->unreadCount > 0)
                <span
                    class="absolute top-0.5 right-0.5 flex h-4 min-w-4 items-center
                             justify-center rounded-full bg-red-500 px-1 text-[10px]
                             font-bold text-white ring-2 ring-black">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </div>

        <span class="sidebar-label">{{ __('Notifications') }}</span>
    </a>

    {{-- ── Toast container — singleton, in <body> ── --}}
    @if (!isset($__toastBellRendered))
        @php($__toastBellRendered = true)
        <template x-teleport="body">
            <div x-data="{
                toasts: [],
                ticketUrlTemplate: @js(route('details', ['company' => Auth::user()->company->slug, 'ticket' => '__TICKET__'])),
                titleFor(type) {
                    return {
                        assigned: 'Ticket Assigned',
                        reassigned: 'Ticket Reassigned',
                        client_replied: 'New Client Reply',
                        status_changed: 'Status Updated',
                        internal_note: 'New Internal Note',
                        ticket_submitted: 'New Ticket',
                        ticket_unassigned: 'Unassigned Ticket',
                        sla_breached: 'SLA Breached',
                        priority_changed: 'Priority Changed',
                        team_assigned: 'Team Assigned',
                        mentioned: 'You Were Mentioned',
                    } [type] || 'New Notification';
                },
                ticketUrl(payload) {
                    if (!payload.ticket_number || payload.type === 'reassigned') return null;
                    return this.ticketUrlTemplate.replace('__TICKET__', payload.ticket_number);
                },
                push(notification) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({
                        id,
                        title: this.titleFor(notification.type),
                        message: notification.message || notification.subject || 'You have a new notification',
                        url: this.ticketUrl(notification),
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
                    <div x-show="true" x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="translate-y-10 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="translate-y-0 opacity-100"
                        x-transition:leave-end="translate-y-2 opacity-0" @click="visit(toast)"
                        :class="toast.url ? 'cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800' : ''"
                        class="w-full bg-white dark:bg-zinc-900 border border-zinc-200
                               dark:border-zinc-700/80 shadow-2xl rounded-xl p-4 flex items-start
                               space-x-3 pointer-events-auto overflow-hidden relative transition-colors">

                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-zinc-600"></div>

                        <div
                            class="shrink-0 mt-0.5 ml-1 text-zinc-500 dark:text-zinc-300
                                    bg-zinc-500/10 dark:bg-zinc-400/10 p-1.5 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>

                        <div class="flex-1 w-0">
                            <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="toast.title">
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-text="toast.message"></p>
                        </div>

                        <button @click.stop="dismiss(toast.id)"
                            class="bg-transparent rounded-md inline-flex text-zinc-500 dark:text-zinc-400
                                   hover:text-zinc-700 dark:hover:text-zinc-300 focus:outline-none
                                   transition-colors">
                            <span class="sr-only">Close</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </template>
    @endif
</div>
