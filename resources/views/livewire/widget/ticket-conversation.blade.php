<div>
    <div class="space-y-6">
        {{-- Original Ticket (First Message) --}}
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
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
                        <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-semibold">
                            {{ $reply->is_technician ? 'T' : substr($reply->user->name, 0, 1) }}
                        </div>
                    @else
                        {{-- Customer Reply --}}
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">
                                    Support Team
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
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
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Add a Reply</h2>

            <form wire:submit="submitReply">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                    
                    <div class="relative">
                        <div x-data="tiptapEditor" class="w-full bg-white border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                            
                            {{-- Toolbar --}}
                            <div class="flex items-center gap-1 p-2 border-b border-gray-200 flex-wrap relative" x-data="{ showLinkInput: false, linkUrl: '' }">
                                <button type="button" @mousedown.prevent @click="bold()"
                                    :class="isActive('bold') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8"/></svg>
                                </button>
                                <button type="button" @mousedown.prevent @click="italic()"
                                    :class="isActive('italic') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Italic">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
                                </button>
                                <button type="button" @mousedown.prevent @click="underline()"
                                    :class="isActive('underline') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"/><line x1="4" y1="21" x2="20" y2="21"/></svg>
                                </button>
                                <div class="w-px h-4 bg-gray-300 mx-1"></div>
                                <button type="button" @mousedown.prevent @click="bulletList()"
                                    :class="isActive('bulletList') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Bullet List">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/></svg>
                                </button>
                                <button type="button" @mousedown.prevent @click="orderedList()"
                                    :class="isActive('orderedList') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Numbered List">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M10 6h11"/><path d="M10 12h11"/><path d="M10 18h11"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                                </button>
                                <div class="w-px h-4 bg-gray-300 mx-1"></div>
                                <button type="button" @mousedown.prevent @click="codeBlock()"
                                    :class="isActive('codeBlock') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Code Block">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                </button>
                                <button type="button" @mousedown.prevent @click="showLinkInput = !showLinkInput; if(showLinkInput) { $nextTick(() => $refs.linkInput.focus()); linkUrl = getLinkUrl(); }"
                                    :class="isActive('link') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-200'"
                                    class="p-1.5 rounded transition" title="Link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </button>

                                <!-- Link Input Popover -->
                                <div x-show="showLinkInput" @click.away="showLinkInput = false" style="display: none;"
                                     class="absolute top-full left-0 mt-1 z-10 w-72 p-2 bg-white border border-gray-200 rounded-lg shadow-lg flex gap-2 items-center">
                                    <input x-ref="linkInput" type="url" x-model="linkUrl" placeholder="https://example.com"
                                           @keydown.enter.prevent="setLink(linkUrl); showLinkInput = false; linkUrl = ''"
                                           class="flex-1 bg-white border border-gray-300 text-gray-900 text-sm rounded px-2 py-1.5 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <button type="button" @click="setLink(linkUrl); showLinkInput = false; linkUrl = ''"
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded transition">
                                        Set
                                    </button>
                                    <button type="button" @click="setLink(null); showLinkInput = false; linkUrl = ''"
                                            class="text-gray-400 hover:text-red-500 p-1.5" title="Remove Link">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Editor area --}}
                            <div wire:ignore>
                                <div x-ref="editorEl"></div>
                            </div>
                        </div>
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
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors {{ count($attachments) >= 2 ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <div class="flex flex-col items-center justify-center pt-3 pb-4">
                                <svg class="w-6 h-6 mb-2 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="text-xs text-gray-500"><span class="font-semibold">Click to upload</span> (Max 2MB per file)</p>
                            </div>
                            <input wire:model="attachments" id="dropzone-file" type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx" {{ count($attachments) >= 2 ? 'disabled' : '' }} />
                        </label>
                        
                        {{-- Loading State for files --}}
                        <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                                <div class="flex items-center justify-between p-2 bg-gray-50 border border-gray-200 rounded-md">
                                    <span class="text-sm text-gray-700 truncate max-w-[80%]">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeAttachment({{ $index }})" class="text-red-500 hover:text-red-700 p-1">
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