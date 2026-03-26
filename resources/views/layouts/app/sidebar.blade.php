<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <style>
        .sidebar-label {
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 1;
            transition: opacity 150ms ease 75ms;
        }

        @media (min-width: 1024px) {
            .sidebar-label {
                opacity: 0;
            }

            .sb-wide .sidebar-label {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950">

    {{-- Mobile overlay --}}
    <div id="mobile-overlay" onclick="closeMobileSidebar()" class="fixed inset-0 z-40 bg-black/40 hidden lg:hidden"></div>

    {{-- Mobile header --}}
    <div
        class="mobile-header lg:hidden fixed top-0 inset-x-0 h-12 bg-black flex items-center px-4 z-40 gap-3 border-b border-zinc-800">
        <button onclick="openMobileSidebar()"
            class="p-1.5 rounded-lg bg-transparent border-none text-zinc-400 hover:bg-zinc-900 hover:text-zinc-100 transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
        <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
            class="flex items-center gap-2 no-underline text-zinc-300 text-lg font-bold transition-colors hover:text-white">
            <img src="{{ asset('images/logodm.png') }}" alt="" class="w-6 h-6">
            Helpdesk
        </a>
        <div class="flex-1"></div>
        <flux:dropdown position="top" align="end">
            <flux:button variant="ghost" icon-trailing="chevron-down" class="gap-2">
                <flux:avatar :initials="auth()->user()->initials()" class="size-7" />
                <span class="truncate max-w-28">{{ auth()->user()->name }}</span>
            </flux:button>
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
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- ── Sidebar: expands on hover ──────────────────────────────────── --}}
    <div id="app-sidebar" x-data="{
        wide: false,
        init() {
            this.wide = window.__sidebarHovered === true;
        },
        enter() {
            this.wide = true;
            window.__sidebarHovered = true;
        },
        leave() {
            this.wide = false;
            window.__sidebarHovered = false;
        }
    }" @mouseenter="enter()" @mouseleave="leave()"
        :class="wide ? 'lg:w-56 lg:shadow-xl sb-wide' : 'lg:w-16'"
        class="fixed inset-y-0 left-0 z-50 flex flex-col
            w-56 bg-black border-r border-zinc-900 dark:border-r-2 dark:border-dashed dark:border-zinc-700
           -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out
           overflow-hidden">

        {{-- Logo --}}
        <div class="h-16 flex items-center shrink-0 px-3">
            <div class="w-10 flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logodm.png') }}" alt="Helpdesk" class="w-7 h-7">
            </div>
            <span
                class="sidebar-label ml-0.5 text-white font-bold !text-lg transition-colors hover:text-white">Helpdesk</span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 flex flex-col gap-1 py-3 overflow-y-auto overflow-x-hidden">

            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                @php $active = request()->routeIs('dashboard', 'admin.dashboard', 'agent.dashboard'); @endphp
                <a href="{{ route('agent.dashboard', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Dashboard') }}</span>
                </a>
            @endif

            @php $active = request()->routeIs('tickets'); @endphp
            <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
                class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                        {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                <div class="w-10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path
                            d="M15 5v2M15 11v2M15 17v2M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2z" />
                    </svg>
                </div>
                <span class="sidebar-label">{{ __('Tickets') }}</span>
            </a>

            @can('view-operators')
                @php $active = request()->routeIs('customers*'); @endphp
                <a href="{{ route('customers', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="16" rx="2" />
                            <circle cx="9" cy="10" r="2.5" />
                            <path d="M7 16.5c.7-1.4 2-2.5 4-2.5s3.3 1.1 4 2.5" />
                            <path d="M15.5 9h3" />
                            <path d="M15.5 12h3" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Customers') }}</span>
                </a>

                @php $active = request()->routeIs('operators'); @endphp
                <a href="{{ route('operators', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Operators') }}</span>
                </a>

                @php $active = request()->routeIs('teams'); @endphp
                <a href="{{ route('teams', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Teams') }}</span>
                </a>

                @php $active = request()->routeIs('categories'); @endphp
                <a href="{{ route('categories', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Categories') }}</span>
                </a>

                @php $active = request()->routeIs('automation', 'automation.*'); @endphp
                <a href="{{ route('automation', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Automation') }}</span>
                </a>

                @php $active = request()->routeIs('channels'); @endphp
                <a href="{{ route('channels', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path
                                d="M12 2H8.5C7.57 2 6.5 2.5 6.5 4s1.07 2 2 2H12v4H8c-1.1 0-2 .9-2 2v2h4v4h2v-4h4v-2c0-1.1-.9-2-2-2h-2V6h3.5c.93 0 2-.5 2-2s-1.07-2-2-2H12z" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Integrations') }}</span>
                </a>

                @php $active = request()->routeIs('reports'); @endphp
                <a href="{{ route('reports', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="18" y1="20" x2="18" y2="10" />
                            <line x1="12" y1="20" x2="12" y2="4" />
                            <line x1="6" y1="20" x2="6" y2="14" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('Reports') }}</span>
                </a>
            @endcan

            @php $active = request()->routeIs('kb.*'); @endphp
            <a href="{{ route('kb.articles', Auth::user()->company->slug) }}" wire:navigate
                class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                        {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                <div class="w-10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                    </svg>
                </div>
                <span class="sidebar-label">{{ __('Knowledge Base') }}</span>
            </a>

            @if (Auth::user()->isOperator())
                @php $active = request()->routeIs('settings.my-team'); @endphp
                <a href="{{ route('settings.my-team', Auth::user()->company->slug) }}" wire:navigate
                    class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                          {{ $active ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <div class="w-10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <span class="sidebar-label">{{ __('My Teams') }}</span>
                </a>
            @endif

        </nav>

        {{-- Bottom: notification bell + settings + profile --}}
        <div class="flex flex-col gap-1 py-3 shrink-0">

            {{-- Notifications --}}
            {{-- Notifications (Livewire: real-time count + dropdown + toasts) --}}
            @livewire('notification-bell')

            {{-- Settings icon --}}
            @php $settingsActive = request()->routeIs('company.profile', 'settings.ai-copilot', 'appearance.edit', 'settings.security', 'settings.email', 'notifications.preferences', 'settings.danger', 'profile.edit', 'form-widget.edit'); @endphp
            <a href="{{ Auth::user()->isAdmin() ? route('company.profile', Auth::user()->company->slug) : route('settings.security', Auth::user()->company->slug) }}"
                wire:navigate
                class="mx-3 h-10 flex items-center rounded-lg transition-all duration-200 hover:translate-x-1 no-underline
                        {{ $settingsActive ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                <div class="w-10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </div>
                <span class="sidebar-label">{{ __('Settings') }}</span>
            </a>

            {{-- Profile dropdown --}}
            <x-app.desktop-user-menu />

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
