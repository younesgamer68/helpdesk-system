<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Ticket #{{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            {{-- Header --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span class="font-mono font-semibold">{{ $ticket->ticket_number }}</span>
                            <span>•</span>
                            <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'open' => 'bg-blue-100 text-blue-800',
                                'in_progress' => 'bg-purple-100 text-purple-800',
                                'resolved' => 'bg-green-100 text-green-800',
                                'closed' => 'bg-gray-100 text-gray-800',
                            ];
                            $priorityColors = [
                                'low' => 'bg-green-100 text-green-800',
                                'medium' => 'bg-blue-100 text-blue-800',
                                'high' => 'bg-orange-100 text-orange-800',
                                'urgent' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($ticket->priority) }} Priority
                            </span>
                        </div>
                    </div>
                </div>

                @if($ticket->category)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <span class="text-sm text-gray-600">Category: </span>
                        <span class="text-sm font-medium text-gray-900">{{ $ticket->category->name }}</span>
                    </div>
                @endif
            </div>

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Conversation Thread --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Conversation</h2>

                <div class="space-y-6">
                    {{-- Original Ticket (First Message) --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                {{ substr($ticket->customer_name, 0, 1) }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $ticket->customer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $ticket->created_at->format('M d, Y g:i A') }}</div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        You
                                    </span>
                                </div>
                                <div class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Replies --}}
                    @foreach($replies as $reply)
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                @if($reply->user_id)
                                    {{-- Staff Reply --}}
                                    <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-semibold">
                                        {{ substr($reply->user->name, 0, 1) }}
                                    </div>
                                @else
                                    {{-- Customer Reply --}}
                                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                        {{ substr($reply->customer_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <div class="font-semibold text-gray-900">
                                                {{ $reply->user_id ? $reply->user->name : $reply->customer_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $reply->created_at->format('M d, Y g:i A') }}</div>
                                        </div>
                                        @if($reply->user_id)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">
                                                Support Team
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                You
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-gray-700 whitespace-pre-wrap">{{ $reply->message }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reply Form --}}
            @if(!in_array($ticket->status, ['closed']))
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Add a Reply</h2>

                    <form action="{{ route('widget.reply', ['ticketNumber' => $ticket->ticket_number, 'token' => request()->route('token')]) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                            <textarea 
                                name="message" 
                                id="message" 
                                rows="6" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                placeholder="Type your message here...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <p class="text-gray-600 font-medium">This ticket is closed</p>
                    <p class="text-sm text-gray-500 mt-1">You cannot add replies to closed tickets</p>
                </div>
            @endif

            {{-- Footer --}}
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Keep this link safe - it's your unique access to this ticket.</p>
            </div>
        </div>
    </div>
</body>
</html>