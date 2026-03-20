<div>
    <div class="space-y-6">
        {{-- Original Ticket (First Message) --}}
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center text-white font-semibold shadow-sm">
                    {{ substr($ticket->customer_name, 0, 1) }}
                </div>
            </div>
            <div class="flex-1">
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="font-bold text-gray-900">{{ $ticket->customer_name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $ticket->created_at->format('M d, Y g:i A') }}</div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                            You
                        </span>
                    </div>
                    <div class="text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</div>
                </div>
            </div>
        </div>

        {{-- Replies --}}
        @foreach ($replies as $reply)
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    @if ($reply->user_id || $reply->is_technician)
                        {{-- Staff Reply (Either an actual user or a disguised technician) --}}
                        <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold shadow-sm">
                            {{ $reply->is_technician ? 'T' : substr($reply->user->name, 0, 1) }}
                        </div>
                    @else
                        {{-- Customer Reply --}}
                        <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center text-white font-semibold shadow-sm">
                            {{ substr($reply->customer_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-bold text-gray-900">
                                    @if ($reply->is_technician)
                                        Support Agent
                                    @elseif ($reply->user_id)
                                        {{ $reply->user->name }}
                                    @else
                                        {{ $reply->customer_name }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $reply->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                            @if ($reply->user_id || $reply->is_technician)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Support Team
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    You
                                </span>
                            @endif
                        </div>
                        <div class="prose prose-sm prose-emerald max-w-none text-gray-700 leading-relaxed">{!! $reply->message !!}</div>

                        {{-- Attachments Display --}}
                        @if (!empty($reply->attachments))
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs font-medium text-gray-500 mb-2">Attachments:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($reply->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    @if (!in_array($ticket->status, ['closed']))
        <div class="mt-8 pt-8 border-t border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                Add a Reply
            </h2>

            <form wire:submit="submitReply">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                    
                    <div class="relative">
                        <textarea wire:model="message" id="message" rows="5"
                            class="block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder-gray-400 text-gray-700 sm:text-sm shadow-sm"
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
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 transition-colors {{ count($attachments) >= 2 ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <div class="flex flex-col items-center justify-center pt-3 pb-4">
                                <svg class="w-6 h-6 mb-2 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="text-xs text-gray-500"><span class="font-semibold text-emerald-600">Click to upload</span> (Max 2MB per file)</p>
                            </div>
                            <input wire:model="attachments" id="dropzone-file" type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx" {{ count($attachments) >= 2 ? 'disabled' : '' }} />
                        </label>
                        
                        {{-- Loading State for files --}}
                        <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-600 font-medium">Uploading...</span>
                        </div>
                    </div>

                    @error('attachments.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('attachments') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    
                    {{-- Previews --}}
                    @if ($attachments)
                        <div class="mt-3 space-y-2">
                            @foreach ($attachments as $index => $attachment)
                                <div class="flex items-center justify-between p-2 bg-emerald-50/50 border border-emerald-100 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-700 truncate max-w-[200px] font-medium">{{ $attachment->getClientOriginalName() }}</span>
                                    </div>
                                    <button type="button" wire:click="removeAttachment({{ $index }})" class="text-gray-400 hover:text-red-500 p-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-all shadow-md flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
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
    @else
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <p class="text-gray-600 font-medium">This ticket is closed</p>
            <p class="text-sm text-gray-500 mt-1">You cannot add replies to closed tickets</p>
        </div>
    @endif
</div>