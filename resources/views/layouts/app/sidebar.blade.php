<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <style>
        /* Sidebar tooltip */
        .sb-tip {
            position: relative;
        }
        .sb-tip::after {
            content: attr(data-tip);
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: #134e4a;
            color: #f0fdfa;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 150ms ease;
            z-index: 999;
        }
        .sb-tip::before {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #134e4a;
            pointer-events: none;
            opacity: 0;
            transition: opacity 150ms ease;
            z-index: 999;
        }
        .sb-tip:hover::after,
        .sb-tip:hover::before {
            opacity: 1;
        }
    </style>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-900">

    {{-- Mobile overlay --}}
    <div id="mobile-overlay"
         onclick="closeMobileSidebar()"
         class="fixed inset-0 z-40 bg-black/40 hidden lg:hidden"></div>

    {{-- Mobile header --}}
    <div class="mobile-header lg:hidden fixed top-0 inset-x-0 h-12 bg-teal-900 border-b border-teal-800 flex items-center px-4 z-40 gap-3">
        <button onclick="openMobileSidebar()"
                class="p-1.5 rounded-lg bg-transparent border-none text-teal-300 hover:bg-teal-800 transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
           class="flex items-center gap-2 no-underline text-white text-sm font-semibold">
            <img src="{{ asset('images/logodm.png') }}" alt="" class="w-6 h-6">
            Helpdesk
        </a>
        <div class="flex-1"></div>
        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit', Auth::user()->company->slug)" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- ── Sidebar: fixed 64px icon strip ─────────────────────────────── --}}
    <div id="app-sidebar"
         class="fixed inset-y-0 left-0 z-50 flex flex-col
                w-16 bg-teal-900 border-r border-teal-800
                -translate-x-full lg:translate-x-0 transition-transform duration-300">

        {{-- Logo --}}
        <div class="h-16 flex items-center justify-center border-b border-teal-800 shrink-0">
            <img src="{{ asset('images/logodm.png') }}" alt="Helpdesk" class="w-7 h-7">
        </div>

        {{-- Nav icons --}}
        <nav class="flex-1 flex flex-col items-center gap-1 py-3 ">

            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                @php $active = request()->routeIs('dashboard', 'admin.dashboard', 'agent.dashboard'); @endphp
                <a href="{{ route('dashboard', Auth::user()->company->slug) }}" wire:navigate
                   data-tip="{{ __('Dashboard') }}"
                   class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                          {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </a>
            @endif

            @php $active = request()->routeIs('tickets'); @endphp
            <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
               data-tip="{{ __('Tickets') }}"
               class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                      {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M15 5v2M15 11v2M15 17v2M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2z"/>
                </svg>
            </a>

            @can('view-operators')
                @php $active = request()->routeIs('operators'); @endphp
                <a href="{{ route('operators', Auth::user()->company->slug) }}" wire:navigate
                   data-tip="{{ __('Operators') }}"
                   class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                          {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </a>

                @php $active = request()->routeIs('categories'); @endphp
                <a href="{{ route('categories', Auth::user()->company->slug) }}" wire:navigate
                   data-tip="{{ __('Categories') }}"
                   class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                          {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                </a>

                @php $active = request()->routeIs('automation'); @endphp
                <a href="{{ route('automation', Auth::user()->company->slug) }}" wire:navigate
                   data-tip="{{ __('Automation') }}"
                   class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                          {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                    </svg>
                </a>

                @php $active = request()->routeIs('form-widget.edit'); @endphp
                <a href="{{ route('form-widget.edit', Auth::user()->company->slug) }}" wire:navigate
                   data-tip="{{ __('Form Widget') }}"
                   class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                          {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                    </svg>
                </a>

                @php $active = request()->routeIs('reports'); @endphp
                <a href="{{ route('reports', Auth::user()->company->slug) }}" wire:navigate
                   data-tip="{{ __('Reports') }}"
                   class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg transition-colors no-underline
                          {{ $active ? 'bg-teal-700 text-white' : 'text-teal-400 hover:bg-teal-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                </a>
            @endcan

        </nav>

        {{-- Bottom: notification bell + profile --}}
                <div class="flex flex-col items-center gap-1 py-3 border-t border-teal-800 shrink-0 w-full">

                    {{-- Notification bell --}}
                    <div data-tip="{{ __('Notifications') }}"
                        class="sb-tip w-10 h-10 flex items-center justify-center rounded-lg text-teal-400 hover:bg-teal-800 hover:text-white transition-colors cursor-default">
                        <livewire:notification-bell />
                    </div>

                    {{-- Profile: full row, sidebar clips it to just the avatar --}}
                    <div class="w-full overflow-hidden px-3">
                        <x-app.desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
                    </div>

                </div>
    </div>

    {{-- Main content —  offset by sidebar width --}}
    <div id="main-content" class="lg:ml-16">
        {{ $slot }}
    </div>

    @fluxScripts

    <script>
        function openMobileSidebar() {
            document.getElementById('app-sidebar').classList.remove('-translate-x-full');
            document.getElementById('mobile-overlay').classList.remove('hidden');
        }
        function closeMobileSidebar() {
            document.getElementById('app-sidebar').classList.add('-translate-x-full');
            document.getElementById('mobile-overlay').classList.add('hidden');
        }
    </script>

</body>

</html>