<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Helpdesk System' }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|righteous:400" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Montserrat:wght@400;500;600;700&family=Raleway:wght@400;500;600&family=Poppins:wght@600;700&family=Sora:wght@600;700&family=DM+Sans:wght@500;700&family=Inter:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/welcome.css'])

    <!-- Alpine.js — ui-state store must load before Alpine starts -->
    <x-ui-state />
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data x-cloak
    class="welcome-body flex min-h-screen flex-col bg-[#ffffff] text-[#17494D] font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] antialiased transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-[#ffffff] text-[#17494D]'">

    <!-- Navigation -->
    <nav class="w-full relative z-50 transition-colors duration-300"
        :class="$store.ui.darkMode ? 'bg-gray-950/80 backdrop-blur-md border-b border-white/5' : 'bg-white/80 backdrop-blur-md border-b border-gray-100'">
        <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-6">
            {{-- LEFT Logo --}}
            <x-logo variant="landing" size="lg" href="/" />

            {{-- RIGHT Controls --}}
            <div class="flex items-center gap-4">
                {{-- Dark mode toggle --}}
                <button type="button"
                    @click="$store.ui.showLoading(400); setTimeout(() => { $store.ui.darkMode = !$store.ui.darkMode }, 150)"
                    class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors duration-200"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-[#1F1F1F] hover:bg-gray-100'"
                    title="Toggle dark mode">
                    <svg x-show="!$store.ui.darkMode"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M15.5 11.5A7 7 0 016.5 2.5a7 7 0 109 9z" fill="none" stroke="currentColor"
                            stroke-width="1.4" stroke-linecap="round" />
                    </svg>
                    <svg x-show="$store.ui.darkMode"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        style="display:none">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41" />
                    </svg>
                </button>

                {{-- Sign In / Sign Up --}}
                @if (Route::has('login'))
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="rounded-full bg-[#5EDB56] px-6 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#4cc944] hover:shadow-md">
                                <span x-text="$store.ui.t('heroDashboard') ?? 'Dashboard'"></span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="rounded-full bg-green-600 hover:bg-green-700 px-6 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:shadow-md">
                                <span x-text="$store.ui.t('utilitySignIn') ?? 'Sign In'"></span>
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>
    <x-loading-overlay />

    <main class="flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-12 relative z-0">
         {{ $slot }}
    </main>

</body>
</html>