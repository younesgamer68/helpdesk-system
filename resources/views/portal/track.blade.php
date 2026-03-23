<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Ticket #{{ $ticket->ticket_number }} - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 min-h-screen font-sans antialiased text-gray-900">
    
    <div class="relative min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
        
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-emerald-100 mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-emerald-100 mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="mb-6 inline-block">
                </a>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
                    Ticket Tracking
                </h2>
                <p class="mt-2 text-lg text-gray-600">
                    Create #{{ $ticket->ticket_number }}
                </p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                <!-- Ticket Header -->
                <div class="px-6 py-8 sm:p-10 bg-white border-b border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                        <div class="space-y-4 flex-1">
                            <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                                {{ $ticket->subject }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $ticket->created_at->format('M d, Y • h:i A') }}
                                </span>
                                <span class="hidden sm:inline text-gray-300">|</span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ $ticket->category ? $ticket->category->name : 'General' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 items-start justify-end min-w-[200px]">
                            {{-- Status Badge --}}
                            @php
                                $statusClasses = [
                                    'open' => 'bg-blue-50 text-blue-700 border-blue-100 ring-blue-500/10',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-100 ring-amber-500/10',
                                    'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-100 ring-emerald-500/10',
                                    'closed' => 'bg-gray-50 text-gray-700 border-gray-100 ring-gray-500/10',
                                ];
                                $statusClass = $statusClasses[$ticket->status] ?? $statusClasses['open'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ring-1 ring-inset {{ $statusClass }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-50"></span>
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>

                            {{-- Priority Badge --}}
                            @php
                                $priorityClasses = [
                                    'low' => 'bg-slate-50 text-slate-600 border-slate-100',
                                    'medium' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'high' => 'bg-orange-50 text-orange-600 border-orange-100',
                                    'urgent' => 'bg-red-50 text-red-600 border-red-100',
                                ];
                                $priorityClass = $priorityClasses[$ticket->priority] ?? $priorityClasses['medium'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $priorityClass }}">
                                {{ ucfirst($ticket->priority) }} Priority
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Success Message --}}
                @if (session('success'))
                    <div class="mx-6 mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                {{-- Livewire Conversation --}}
                <div class="p-6 sm:p-10 bg-gray-50/50">
                    @livewire('widget.ticket-conversation', ['ticket' => $ticket])
                </div>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</body>
</html>