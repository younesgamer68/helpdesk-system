<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', $company->name . ' Knowledge Base')">
    <title>@yield('title', 'Knowledge Base') - {{ $company->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>

<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased min-h-screen flex flex-col">

    <!-- Top Nav -->
    <header
        class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 sticky top-0 z-30 backdrop-blur-sm bg-white/95 dark:bg-zinc-900/95">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <a href="{{ route('kb.public.home', $company->slug) }}" class="flex items-center gap-2.5 group">
                @if ($company->logo)
                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" class="h-7">
                @endif
                <span
                    class="font-semibold text-base tracking-tight text-zinc-900 dark:text-zinc-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">{{ $company->name }}</span>
                <span class="hidden sm:inline text-xs text-zinc-400 dark:text-zinc-500 font-normal">Help Center</span>
            </a>
            <div class="flex items-center gap-4">
                @if ($company->website)
                    <a href="{{ $company->website }}"
                        class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Website
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Hero / Search Area -->
    @yield('hero')

    <!-- Main Content -->
    <main class="flex-grow max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 mt-auto py-6">
        <div
            class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                &copy; {{ date('Y') }} {{ $company->name }}
            </p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500 flex items-center gap-1">
                Powered by HelpDesk
            </p>
        </div>
    </footer>

    @php
        $widgetVersion = filemtime(resource_path('views/kb/widget-js.blade.php')) ?: time();
    @endphp
    <script src="{{ route('kb.public.widget', ['company' => $company->slug]) }}?v={{ $widgetVersion }}" defer></script>

</body>

</html>
