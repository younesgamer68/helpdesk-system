<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': dark }" x-init="$watch('dark', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Ticket #{{ $ticket->ticket_number }} - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-zinc-50 dark:bg-zinc-950 min-h-screen font-sans antialiased text-zinc-900 dark:text-zinc-100 flex flex-col"
    style="font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';">

    {{-- Top Bar --}}
    <header
        class="sticky top-0 z-30 shrink-0 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border-b border-zinc-200 dark:border-zinc-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14">
            <div class="flex items-center gap-3">
                @if (!empty($ticket->company?->logo))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($ticket->company->logo) }}"
                        alt="{{ $ticket->company->name }}"
                        class="w-8 h-8 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700">
                @else
                    <div
                        class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($ticket->company->name ?? config('app.name'), 0, 1)) }}
                    </div>
                @endif
                <span
                    class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $ticket->company->name ?? config('app.name') }}</span>
            </div>

            {{-- Dark / Light Mode Toggle --}}
            <button @click="dark = !dark"
                class="p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                title="Toggle dark mode">
                <svg x-show="dark" x-cloak class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg x-show="!dark" class="w-5 h-5 text-zinc-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>
    </header>

    <main class="flex-1 flex flex-col min-h-0">
        <div class="max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col flex-1 min-h-0">

            {{-- Ticket Header --}}
            <div class="mb-4 shrink-0">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 leading-tight">
                            {{ $ticket->subject }}
                        </h1>
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="font-mono text-xs">#{{ $ticket->ticket_number }}</span>
                            <span class="text-zinc-300 dark:text-zinc-600">&middot;</span>
                            <span>{{ $ticket->created_at->format('M d, Y • h:i A') }}</span>
                            @if ($ticket->category)
                                <span class="text-zinc-300 dark:text-zinc-600">&middot;</span>
                                <span>{{ $ticket->category->name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 shrink-0">
                        @php
                            $statusStyles = [
                                'open' =>
                                    'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/20',
                                'pending' =>
                                    'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                                'resolved' =>
                                    'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                'closed' =>
                                    'bg-zinc-100 dark:bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20',
                            ];
                            $statusStyle = $statusStyles[$ticket->status] ?? $statusStyles['open'];

                            $priorityStyles = [
                                'low' =>
                                    'bg-slate-50 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-500/20',
                                'medium' =>
                                    'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20',
                                'high' =>
                                    'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-500/20',
                                'urgent' =>
                                    'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border-red-200 dark:border-red-500/20',
                            ];
                            $priorityStyle = $priorityStyles[$ticket->priority] ?? $priorityStyles['medium'];
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusStyle }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-60"></span>
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $priorityStyle }}">
                            {{ ucfirst($ticket->priority) }} Priority
                        </span>
                    </div>
                </div>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div
                    class="mb-4 shrink-0 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Conversation --}}
            <div class="flex-1 min-h-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden flex flex-col"
                style="min-height: 500px;">
                @livewire('widget.ticket-conversation', ['ticket' => $ticket])
            </div>

            {{-- Footer --}}
            <div class="mt-6 shrink-0 text-center text-xs text-zinc-400 dark:text-zinc-500 pb-4">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </main>
</body>

</html>
