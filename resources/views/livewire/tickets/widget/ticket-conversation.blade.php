<div>
    <div class="space-y-6">
        {{-- Original Ticket (First Message) --}}
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <div
                    class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                    {{ substr($ticket->customer_name, 0, 1) }}
                </div>
            </div>
            <div class="flex-1">
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $ticket->customer_name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $ticket->created_at->format('M d, Y g:i A') }}</div>
                        </div>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            You
                        </span>
                    </div>
                    <div class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
                </div>
            </div>
        </div>

        {{-- Replies --}}
        @foreach ($replies as $reply)
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    @if ($reply->user_id || $reply->is_technician)
                        {{-- Staff Reply (Either an actual user or a disguised technician) --}}
                        <div
                            class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-semibold">
                            {{ $reply->is_technician ? 'T' : substr($reply->user->name, 0, 1) }}
                        </div>
                    @else
                        {{-- Customer Reply --}}
                        <div
                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                            {{ substr($reply->customer_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="font-semibold text-gray-900">
                                    @if ($reply->is_technician)
                                        Technician
                                    @elseif ($reply->user_id)
                                        {{ $reply->user->name }}
                                    @else
                                        {{ $reply->customer_name }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $reply->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                            @if ($reply->user_id || $reply->is_technician)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">
                                    Support Team
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    You
                                </span>
                            @endif
                        </div>
                        <div class="prose prose-sm max-w-none text-gray-700">{!! $reply->message !!}</div>

                        {{-- Attachments Display --}}
                        @if (!empty($reply->attachments))
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs font-medium text-gray-500 mb-2">Attachments:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($reply->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span class="truncate max-w-[150px]">{{ $attachment['name'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Reply Form --}}
    @php
        $status = $ticket->status;
        $reopenHours = $this->slaPolicy?->reopen_hours ?? 48;
        $linkedTicketDays = $this->slaPolicy?->linked_ticket_days ?? 7;
        $resolvedAt = $ticket->resolved_at;
        $closedAt = $ticket->closed_at;
        $canReopen = $status === 'resolved' && $resolvedAt && now()->diffInHours($resolvedAt, false) >= -$reopenHours;
        $canCreateLinked =
            $status === 'closed' && $closedAt && now()->diffInDays($closedAt, false) >= -$linkedTicketDays;
        $showForm = !in_array($status, ['resolved', 'closed']);
    @endphp

    @if ($showForm)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Add a Reply</h2>

            <form wire:submit="submitReply">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>

                    <div class="relative">
                        <textarea wire:model="message" id="message" rows="5"
                            class="block w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400 text-gray-700 sm:text-sm"
                            placeholder="Write your message here..."></textarea>
                    </div>

                    <div class="flex justify-between items-center mt-1">
                        <div>
                            @error('message')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- File Uploads (Max 2) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional, Max 2)</label>

                    <div class="flex items-center justify-center w-full relative">
                        <label for="dropzone-file"
                            class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors {{ count($attachments) >= 2 ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <div class="flex flex-col items-center justify-center pt-3 pb-4">
                                <svg class="w-6 h-6 mb-2 text-gray-500" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                </svg>
                                <p class="text-xs text-gray-500"><span class="font-semibold">Click to upload</span> (Max
                                    2MB per file)</p>
                            </div>
                            <input wire:model="attachments" id="dropzone-file" type="file" class="hidden" multiple
                                accept="image/*,.pdf,.doc,.docx" {{ count($attachments) >= 2 ? 'disabled' : '' }} />
                        </label>

                        {{-- Loading State for files --}}
                        <div wire:loading wire:target="attachments"
                            class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-sm text-gray-600 font-medium">Uploading...</span>
                        </div>
                    </div>

                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('attachments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Previews --}}
                    @if ($attachments)
                        <div class="mt-3 space-y-2">
                            @foreach ($attachments as $index => $attachment)
                                <div
                                    class="flex items-center justify-between p-2 bg-gray-50 border border-gray-200 rounded-md">
                                    <span
                                        class="text-sm text-gray-700 truncate max-w-[80%]">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeAttachment({{ $index }})"
                                        class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span wire:loading.remove wire:target="submitReply">Send Reply</span>
                        <span wire:loading wire:target="submitReply">Sending...</span>
                    </button>
                </div>
            </form>
        </div>
    @elseif ($canReopen)
        {{-- Resolved within reopen window — allow reply to reopen --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-green-800">This ticket has been resolved.</p>
                    <p class="text-sm text-green-700 mt-0.5">If your issue isn't fully resolved, you can reply below to
                        reopen it. The reopen window closes
                        {{ $resolvedAt->copy()->addHours($reopenHours)->diffForHumans() }}.</p>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-gray-900 mb-4">Reopen Ticket</h2>

            <form wire:submit="submitReply">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                    <textarea wire:model="message" id="message" rows="5"
                        class="block w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400 text-gray-700 sm:text-sm"
                        placeholder="Describe the remaining issue..."></textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <span wire:loading.remove wire:target="submitReply">Reply &amp; Reopen</span>
                        <span wire:loading wire:target="submitReply">Sending...</span>
                    </button>
                </div>
            </form>
        </div>
    @elseif ($status === 'resolved' && !$canReopen)
        {{-- Resolved but past reopen window --}}
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-600 font-medium">This ticket has been resolved</p>
            <p class="text-sm text-gray-500 mt-1">The reopen window has passed. Please submit a new support request if
                you need further help.</p>
        </div>
    @elseif ($canCreateLinked)
        {{-- Closed within linked-ticket window --}}
        @if ($confirmLinkedTicket)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-yellow-800">This ticket is closed. Your message will create a
                        new follow-up ticket linked to this one.</p>
                    <p class="text-sm text-yellow-700 mt-1">The new ticket subject will be: <strong>Follow-up:
                            {{ $ticket->subject }}</strong></p>
                </div>

                <form wire:submit="submitReply">
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your
                            Message</label>
                        <textarea wire:model="message" id="message" rows="5"
                            class="block w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400 text-gray-700 sm:text-sm"
                            placeholder="Describe your issue..."></textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="cancelLinkedTicket"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <span wire:loading.remove wire:target="submitReply">Create Follow-up Ticket</span>
                            <span wire:loading wire:target="submitReply">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-amber-800 font-medium">This ticket is closed</p>
                <p class="text-sm text-amber-700 mt-1">Still need help? You can create a follow-up ticket linked to
                    this one.</p>
                <button type="button" wire:click="submitReply"
                    class="mt-4 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                    Create Follow-up Ticket
                </button>
            </div>
        @endif
    @else
        {{-- Closed and past linked-ticket window --}}
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <p class="text-gray-600 font-medium">This ticket is permanently closed</p>
            <p class="text-sm text-gray-500 mt-1">Please submit a new support request if you need further assistance.
            </p>
        </div>
    @endif
</div>
