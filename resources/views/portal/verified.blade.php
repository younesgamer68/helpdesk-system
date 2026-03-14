<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">


@include('partials.head')


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket Verified</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            {{-- Success Icon --}}
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            {{-- Message --}}
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Ticket Verified!</h1>
            <p class="text-gray-600 mb-6">
                Your support ticket <span
                    class="font-mono font-semibold text-gray-900">{{ $ticket->ticket_number }}</span> has been
                successfully verified.
            </p>

            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-700">
                        <p class="font-semibold mb-1">Check your email!</p>
                        <p>We've sent you a tracking link to <span
                                class="font-mono">{{ $ticket->customer_email }}</span>. You can use it to view updates
                            and reply to our team.</p>
                    </div>
                </div>
            </div>

            {{-- Ticket Details --}}
            <div class="bg-gray-50 rounded-lg p-4 text-left mb-6">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Ticket Details</div>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Subject:</span>
                        <span class="text-sm text-gray-900">{{ $ticket->subject }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Status:</span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Priority:</span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Note --}}
            <p class="text-xs text-gray-500">
                Our support team will review your ticket and respond as soon as possible.
            </p>
        </div>
    </div>
</body>

</html>
