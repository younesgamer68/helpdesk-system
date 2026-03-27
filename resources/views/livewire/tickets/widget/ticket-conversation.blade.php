<div class="flex flex-col flex-1 min-h-0">
    {{-- Scrollable messages --}}
    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 bg-zinc-50/40 dark:bg-zinc-950/40">

        {{-- Original Ticket (First Message) --}}
        @if ($ticket->description)
            <div class="flex gap-3">
                <div
                    class="shrink-0 w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                    {{ strtoupper(substr($ticket->customer_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 mb-1.5">
                        <span
                            class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $ticket->customer_name }}</span>
                        <span class="text-xs text-blue-600 dark:text-blue-400">You</span>
                        <span
                            class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">{{ $ticket->created_at->format('M d, g:i A') }}</span>
                    </div>
                    <div
                        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl px-5 py-4">
                        <div
                            class="prose prose-sm prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                            {!! \Mews\Purifier\Facades\Purifier::clean($ticket->description) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Replies --}}
        @foreach ($replies as $reply)
            @php
                $isStaff = $reply->user_id || $reply->is_technician;
                if ($reply->is_technician) {
                    $avatarBg = 'bg-violet-500';
                    $senderName = 'Technician';
                    $badgeText = 'Technician';
                    $badgeColor = 'text-violet-600 dark:text-violet-400';
                } elseif ($reply->user_id) {
                    $avatarBg = 'bg-emerald-600';
                    $senderName = $reply->user->name;
                    $badgeText = 'Support Team';
                    $badgeColor = 'text-emerald-600 dark:text-emerald-400';
                } else {
                    $avatarBg = 'bg-blue-500';
                    $senderName = $reply->customer_name;
                    $badgeText = 'You';
                    $badgeColor = 'text-blue-600 dark:text-blue-400';
                }
                $initials = strtoupper(substr($senderName ?? '?', 0, 1));
            @endphp

            <div class="flex gap-3">
                <div
                    class="shrink-0 w-9 h-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-white text-sm font-bold shadow-sm">
                    {{ $initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 mb-1.5">
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $senderName }}</span>
                        <span class="text-xs {{ $badgeColor }}">{{ $badgeText }}</span>
                        <span
                            class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">{{ $reply->created_at->format('M d, g:i A') }}</span>
                    </div>
                    <div
                        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl px-5 py-4">
                        <div
                            class="prose prose-sm prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                            {!! \Mews\Purifier\Facades\Purifier::clean($reply->message) !!}
                        </div>

                        @if (!empty($reply->attachments))
                            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap gap-2">
                                @foreach ($reply->attachments as $attachment)
                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                                        class="flex items-center gap-1.5 px-2.5 py-1.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-xs text-zinc-600 dark:text-zinc-300 hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <span class="truncate max-w-[150px]">{{ $attachment['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Support typing indicator --}}
        @if ($supportTypingName)
            <div class="flex gap-3">
                <div
                    class="shrink-0 w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                    {{ strtoupper(substr($supportTypingName, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 mb-1.5">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $supportTypingName }} is
                            typing</span>
                    </div>
                    <div
                        class="inline-flex items-center gap-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400 animate-bounce"
                            style="animation-delay:0ms"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400 animate-bounce"
                            style="animation-delay:120ms"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400 animate-bounce"
                            style="animation-delay:240ms"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Reply / Status Forms --}}
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
        <div class="shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 sm:p-5">
            <form wire:submit="submitReply">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Your
                        Message</label>
                    <textarea wire:model="message" x-on:input.debounce.1s="$wire.markTyping()" id="message" rows="4"
                        class="block w-full px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"
                        placeholder="Write your message here..."></textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- File Uploads (Max 2) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Attachments <span
                            class="text-zinc-400 font-normal">(Optional, Max 2)</span></label>
                    <div class="relative">
                        <label for="dropzone-file"
                            class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl cursor-pointer bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition {{ count($attachments) >= 2 ? 'opacity-50 pointer-events-none' : '' }}">
                            <div class="flex flex-col items-center justify-center py-3">
                                <svg class="w-5 h-5 mb-1.5 text-zinc-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400"><span class="font-medium">Click to
                                        upload</span> (Max 2MB per file)</p>
                            </div>
                            <input wire:model="attachments" id="dropzone-file" type="file" class="hidden" multiple
                                accept="image/*,.pdf,.doc,.docx" {{ count($attachments) >= 2 ? 'disabled' : '' }} />
                        </label>

                        <div wire:loading wire:target="attachments"
                            class="absolute inset-0 bg-white/80 dark:bg-zinc-900/80 flex items-center justify-center rounded-xl">
                            <svg class="animate-spin h-5 w-5 text-emerald-500 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            <span class="text-sm text-zinc-600 dark:text-zinc-300 font-medium">Uploading...</span>
                        </div>
                    </div>

                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('attachments')
                        <p class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @if ($attachments)
                        <div class="mt-2.5 flex flex-wrap gap-2">
                            @foreach ($attachments as $index => $attachment)
                                <div
                                    class="flex items-center gap-2 px-3 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-xs">
                                    <span
                                        class="text-zinc-600 dark:text-zinc-300 truncate max-w-[150px]">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeAttachment({{ $index }})"
                                        class="text-zinc-400 hover:text-red-500 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
                    <button type="submit" wire:loading.attr="disabled" wire:target="submitReply"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
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
        <div class="shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 sm:p-5">
            <div
                class="mb-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">This ticket has been
                        resolved.</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-300/80 mt-0.5">If your issue isn't fully
                        resolved, you can reply
                        below to reopen it. The reopen window closes
                        {{ $resolvedAt->copy()->addHours($reopenHours)->diffForHumans() }}.</p>
                </div>
            </div>

            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mb-3">Reopen Ticket</h3>

            <form wire:submit="submitReply">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Your
                        Message</label>
                    <textarea wire:model="message" x-on:input.debounce.1s="$wire.markTyping()" id="message" rows="4"
                        class="block w-full px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"
                        placeholder="Describe the remaining issue..."></textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="submitReply"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <span wire:loading.remove wire:target="submitReply">Reply &amp; Reopen</span>
                        <span wire:loading wire:target="submitReply">Sending...</span>
                    </button>
                </div>
            </form>
        </div>
    @elseif ($status === 'resolved' && !$canReopen)
        {{-- Resolved but past reopen window --}}
        <div class="shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 text-center">
            <div
                class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-500/10 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">This ticket has been resolved</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">The reopen window has passed. Please submit a new
                support request if
                you need further help.</p>
        </div>
    @elseif ($canCreateLinked)
        {{-- Closed within linked-ticket window --}}
        @if ($confirmLinkedTicket)
            <div class="shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 sm:p-5">
                <div
                    class="mb-4 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">This ticket is closed. Your
                        message will create a
                        new follow-up ticket linked to this one.</p>
                    <p class="text-sm text-amber-600 dark:text-amber-300/80 mt-1">The new ticket subject will be:
                        <strong>Follow-up:
                            {{ $ticket->subject }}</strong>
                    </p>
                </div>

                <form wire:submit="submitReply">
                    <div class="mb-4">
                        <label for="message"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Your
                            Message</label>
                        <textarea wire:model="message" x-on:input.debounce.1s="$wire.markTyping()" id="message" rows="4"
                            class="block w-full px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"
                            placeholder="Describe your issue..."></textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="cancelLinkedTicket"
                            class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="submitReply"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <span wire:loading.remove wire:target="submitReply">Create Follow-up Ticket</span>
                            <span wire:loading wire:target="submitReply">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div
                class="shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 text-center">
                <div
                    class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-500/10 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">This ticket is closed</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Still need help? You can create a follow-up
                    ticket linked to
                    this one.</p>
                <button type="button" wire:click="submitReply"
                    class="mt-4 inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                    Create Follow-up Ticket
                </button>
            </div>
        @endif
    @else
        {{-- Closed and past linked-ticket window --}}
        <div class="shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 text-center">
            <div
                class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">This ticket is permanently closed</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Please submit a new support request if you need
                further assistance.</p>
        </div>
    @endif
</div>
