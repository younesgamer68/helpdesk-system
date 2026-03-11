<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header class="flex justify-center items-center">
            <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
                class="text-center flex flex-col items-center justify-center">
                <img src="{{ asset('images/logodm.png') }}" alt="" style="width:30px;heigh:30px;">
                <span>Helpdesk</span>
            </a>

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>
        <flux:sidebar.nav>
            <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                {{ __('Welcome back!') }}
            </div>
            <nav class="flex flex-col gap-1 px-2">

                @php
                    function sidebarClass(bool $active): string
                    {
                        $base =
                            'flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors duration-150';
                        $active
                            ? ($state = 'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-white')
                            : ($state =
                                'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white');
                        return "$base $state";
                    }
                @endphp

                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <a href="/dashboard" wire:navigate
                        class="{{ sidebarClass(request()->is('dashboard') || request()->is('admin/dashboard') || request()->is('home')) }}">
                        <flux:icon.home class="size-5 shrink-0" />
                        {{ __('Dashboard') }}
                    </a>
                @endif

                <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
                    class="{{ sidebarClass(request()->routeIs('tickets')) }}">
                    <flux:icon.ticket class="size-5 shrink-0" />
                    {{ __('Tickets') }}
                </a>

                @can('view-operators')
                    <a href="{{ route('operators', Auth::user()->company->slug) }}" wire:navigate
                        class="{{ sidebarClass(request()->routeIs('operators')) }}">
                        <flux:icon.users class="size-5 shrink-0" />
                        {{ __('Operators') }}
                    </a>

                    <a href="{{ route('categories', Auth::user()->company->slug) }}" wire:navigate
                        class="{{ sidebarClass(request()->routeIs('categories')) }}">
                        <flux:icon.folder class="size-5 shrink-0" />
                        {{ __('Categories') }}
                    </a>

                    <a href="{{ route('automation', Auth::user()->company->slug) }}" wire:navigate
                        class="{{ sidebarClass(request()->routeIs('automation')) }}">
                        <flux:icon.cog class="size-5 shrink-0" />
                        {{ __('Automation') }}
                    </a>

                    <a href="{{ route('form-widget.edit', Auth::user()->company->slug) }}" wire:navigate
                        class="{{ sidebarClass(request()->routeIs('form-widget.edit')) }}">
                        <flux:icon.layout-grid class="size-5 shrink-0" />
                        {{ __('Form Widget') }}
                    </a>
                @endcan

            </nav>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <div class="mt-2 text-zinc-500">

                <livewire:notification-bell />
                Notification
            </div>
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <div class="ml-2 flex items-center gap-1">
            <a href="{{ route('tickets', Auth::user()->company->slug) }}" wire:navigate
                class="flex items-center gap-2">
                <img src="{{ asset('images/logodm.png') }}" alt="" style="width:24px;height:24px;">
                <span class="font-bold text-sm">Helpdesk</span>
            </a>
        </div>

        <flux:spacer />

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
                    <flux:menu.item :href="route('profile.edit', Auth::user()->company->slug)" icon="cog"
                        wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
