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
    <header class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('kb.public.home', $company->slug) }}" class="flex items-center gap-3">
                @if($company->settings['logo'] ?? null)
                    <img src="{{ Storage::url($company->settings['logo']) }}" alt="{{ $company->name }}" class="h-8">
                @endif
                <span class="font-bold text-lg tracking-tight">{{ $company->name }} Support</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="https{{ $company->website ? '://' . str_replace(['http://', 'https://'], '', $company->website) : '#' }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
                    Go to Website
                </a>
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
    <footer class="bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 mt-auto py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:flex sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                &copy; {{ date('Y') }} {{ $company->name }}. All rights reserved.
            </p>
            <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-2 sm:mt-0 flex items-center justify-center gap-1">
                Powered by <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg> HelpDesk System
            </p>
        </div>
    </footer>

</body>
</html>
