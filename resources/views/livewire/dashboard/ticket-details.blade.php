<div class="min-h-screen">
        <x-flash-message />
    

  
    <div class="max-w-[1600px] mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('tickets', ['company' => $ticket->company->slug]) }}"
                        class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-sm text-zinc-500">Ticket #{{ $ticket->ticket_number }}</span>
                            <span class="text-xs text-zinc-600">Last updated
                                {{ $ticket->updated_at->diffForHumans() }}</span>
                        </div>
                        <h1 class="text-2xl font-semibold text-white">{{ $ticket->subject }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if ($state !== 'resolved')
                        <button wire:click="resolve"
                            wire:confirm="Are you sure you want to mark this ticket as resolved?"
                            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Mark as Resolved
                        </button>
                    @else
                        <button wire:click="unresolve" wire:confirm="Are you sure you want to unresolve this ticket?"
                            class="px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l3-3m0 0l3 3m-3-3v9" />
                            </svg>
                            Unresolve
                        </button>
                    @endif

                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $statusBg = match ($ticket->status) {
                        'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'on-hold', 'on_hold' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        'resolved' => 'bg-green-500/10 text-green-400 border-green-500/20',
                        'closed' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                        'in_progress', 'in progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                    };

                    $priorityBg = match ($ticket->priority) {
                        'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                        'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                        'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                    };
                @endphp

                <span class="px-2.5 py-1 {{ $statusBg }} text-xs font-medium rounded-full border">
                    {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                </span>
                <span class="px-2.5 py-1 {{ $priorityBg }} text-xs font-medium rounded-full border">
                    {{ ucfirst($ticket->priority) }}
                </span>

                @if ($ticket->category)
                    <span
                        class="px-2.5 py-1 bg-zinc-800 text-zinc-400 text-xs font-medium rounded-full border border-zinc-700">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ $ticket->category->name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Left Column - Conversation --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Conversation Section --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                    <div class="p-6 border-b border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white">Conversation</h2>
                            <span class="text-sm text-zinc-500">Visible to customer</span>
                        </div>
                        <p class="text-sm text-zinc-500 mt-1">0 messages · First response in 0 min</p>
                    </div>

                    <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
                        {{-- Messages will go here --}}
                    </div>

                    {{-- Reply Form --}}
                    <div class="p-6 border-t border-zinc-800">
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost"
                                        class="px-3 py-1.5 text-sm bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition border border-zinc-700"
                                        size="sm">
                                        Reply as {{ Auth::user()->name }} {{ ucwords(Auth::user()->role) }}
                                    </flux:button>
                                    <flux:menu>
                                        @foreach ($agents as $agent)
                                            <flux:menu.item>{{  $agent->name === Auth::user()->name ? $agent->name . ' (You)' : $agent->name }}</flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                            <div class="relative" x-data="ticketReplyEditor()" x-init="initEditor($refs.editorBox)">
                                <!-- TipTap Editor toolbar omitted for brevity (compact) -->
                                <div class="flex items-center gap-1 bg-zinc-800/50 p-1.5 border border-b-0 border-zinc-700 rounded-t-lg">
                                    <button type="button" @click="editor.chain().focus().toggleBold().run()" class="p-1 rounded text-zinc-400 hover:bg-zinc-700 hover:text-white transition" title="Bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path></svg>
                                    </button>
                                    <button type="button" @click="editor.chain().focus().toggleItalic().run()" class="p-1 rounded text-zinc-400 hover:bg-zinc-700 hover:text-white transition" title="Italic">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                    </button>
                                </div>
                                <div x-ref="editorBox" class="w-full min-h-[100px] bg-zinc-800 border border-zinc-700 rounded-b-lg p-3 text-zinc-200 focus:outline-none overflow-y-auto prose prose-invert max-w-none"></div>

                                <!-- Slash Command Popup -->
                                <div x-show="showSlashMenu" 
                                     x-transition 
                                     class="absolute z-10 w-64 bg-zinc-800 border border-zinc-700 rounded-lg shadow-lg overflow-hidden flex flex-col mt-2"
                                     style="display: none;"
                                     :style="`left: ${menuCoords.left}px; top: ${menuCoords.top}px;`">
                                    
                                    <div class="p-2 border-b border-zinc-700">
                                        <p class="text-xs text-zinc-400 font-medium">Search Knowledge Base...</p>
                                    </div>
                                    
                                    <ul class="max-h-48 overflow-y-auto w-full py-1 text-sm bg-zinc-800" role="listbox">
                                        <template x-for="(article, index) in kbResults" :key="article.id">
                                            <li @click="insertKbLink(article)" 
                                                class="px-3 py-2 text-zinc-300 hover:bg-zinc-700 hover:text-white cursor-pointer transition select-none flex items-center gap-2">
                                                <svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                                                <span x-text="article.title" class="truncate"></span>
                                            </li>
                                        </template>
                                        <li x-show="kbResults.length === 0" class="px-3 py-2 text-zinc-500 text-xs italic">
                                            No articles found.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <script type="module">
                                import { Editor } from 'https://esm.sh/@tiptap/core';
                                import StarterKit from 'https://esm.sh/@tiptap/starter-kit';
                                import Link from 'https://esm.sh/@tiptap/extension-link';

                                document.addEventListener('alpine:init', () => {
                                    Alpine.data('ticketReplyEditor', () => ({
                                        editor: null,
                                        content: @entangle('replyMessage'),
                                        showSlashMenu: false,
                                        menuCoords: { left: 0, top: 0 },
                                        searchQuery: '',
                                        fullSearchTerm: '', // includes "/kb "
                                        kbResults: [],

                                        initEditor(element) {
                                            const vm = this;
                                            this.editor = new Editor({
                                                element: element,
                                                extensions: [
                                                    StarterKit,
                                                    Link.configure({ openOnClick: false }),
                                                ],
                                                content: this.content,
                                                onUpdate: ({ editor }) => {
                                                    this.content = editor.getHTML();
                                                    this.checkSlashCommand();
                                                },
                                                editorProps: {
                                                    attributes: {
                                                        class: 'prose prose-invert max-w-none focus:outline-none min-h-[100px]',
                                                    },
                                                    handleKeyDown(view, event) {
                                                        if (vm.showSlashMenu && event.key === 'Escape') {
                                                            vm.showSlashMenu = false;
                                                            return true;
                                                        }
                                                        return false;
                                                    }
                                                },
                                            });
                                        },

                                        async checkSlashCommand() {
                                            const { state, view } = this.editor;
                                            const { selection } = state;
                                            const { $cursor } = selection;
                                            
                                            if (!$cursor) {
                                                this.showSlashMenu = false;
                                                return;
                                            }
                                            
                                            const textBefore = $cursor.parent.textContent.slice(0, $cursor.parentOffset);
                                            const match = textBefore.match(/\/kb\s*(.*)$/);

                                            if (match) {
                                                this.searchQuery = match[1];
                                                this.fullSearchTerm = match[0];
                                                
                                                // Calculate popup position relative to text wrapper
                                                const coords = view.coordsAtPos($cursor.pos);
                                                const editorBox = this.$refs.editorBox.getBoundingClientRect();
                                                
                                                this.menuCoords.left = coords.left - editorBox.left;
                                                this.menuCoords.top = (coords.bottom - editorBox.top) + 20; 

                                                // Fetch results
                                                this.kbResults = await this.$wire.searchKbArticles(this.searchQuery);
                                                this.showSlashMenu = true;
                                            } else {
                                                this.showSlashMenu = false;
                                            }
                                        },

                                        insertKbLink(article) {
                                            const baseUrl = '{{ rtrim(config("app.url"), "/") }}';
                                            const companySlug = '{{ $ticket->company->slug }}';
                                            const scheme = baseUrl.startsWith('https') ? 'https://' : 'http://';
                                            // The URL format based on routes/web.php domain group
                                            const domain = '{{ config("app.domain") }}';
                                            const linkUrl = `${scheme}${companySlug}.${domain}/kb/articles/${article.slug}`;

                                            // Delete the /kb query from editor
                                            const { state, view } = this.editor;
                                            const { $cursor } = state.selection;
                                            const pos = $cursor.pos;
                                            
                                            const from = pos - this.fullSearchTerm.length;
                                            const to = pos;

                                            this.editor.chain()
                                                .focus()
                                                .deleteRange({ from, to })
                                                .insertContent(`<a href="${linkUrl}" target="_blank" class="text-teal-400 underline">${article.title}</a> `)
                                                .run();

                                            this.showSlashMenu = false;
                                        }
                                    }));
                                });
                            </script>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                        class="rounded border-zinc-700 bg-zinc-800 text-teal-600 focus:ring-teal-500">
                                    <span class="text-sm text-zinc-400">Reply & keep as Open</span>
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition">
                                    Save draft
                                </button>
                                <button
                                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Send reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Ticket Details & Internal Notes --}}
            <div class="space-y-6">
                {{-- Ticket Details --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-white">Ticket details</h3>
                    </div>
                    <p class="text-xs text-zinc-500 mb-4">Context for this request</p>

                    <div class="space-y-4">
                        {{-- Customer --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Customer</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    {{ substr($ticket->customer_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm text-white">{{ $ticket->customer_name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $ticket->customer_email }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Assigned Agent --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Assigned agent</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                                    {{ substr($ticket->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm text-white">{{ $ticket->user->name ?? 'Unassigned' }}</p>
                                    @if ($ticket->user)
                                        <p class="text-xs text-zinc-500">{{ $ticket->user->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Category</p>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-zinc-400 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <div>
                                    <p class="text-sm text-white">{{ $ticket->category->name ?? 'Uncategorized' }}</p>
                                    @if ($ticket->category)
                                        <p class="text-xs text-zinc-500">{{ $ticket->category->description ?? '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Created --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Created</p>
                            <p class="text-sm text-white">{{ $ticket->created_at->format('l - H:i') }} CET</p>
                            <p class="text-xs text-zinc-500">via Web form</p>
                        </div>

                        {{-- Last Updated --}}
                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Last updated</p>
                            <p class="text-sm text-white">{{ $ticket->updated_at->diffForHumans() }}</p>
                            <p class="text-xs text-zinc-500">via {{ Auth::user()->name ?? 'System' }}</p>
                        </div>
                    </div>

                    {{-- SLA Information Section --}}
                    @if ($ticket->due_time)
                    <div class="mt-6 pt-6 border-t border-zinc-800 space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                SLA Information
                            </h3>
                        </div>

                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Response Time Limit</p>
                            <p class="text-sm text-white">{{ ucfirst($ticket->priority) }} Priority</p>
                            <p class="text-xs text-zinc-500">Due: {{ $ticket->due_time->format('l - H:i') }} CET</p>
                        </div>

                        <div>
                            <p class="text-xs text-zinc-500 mb-2">Remaining Time</p>
                            <div x-data="{
                                    dueTime: new Date('{{ is_string($ticket->due_time) ? \Carbon\Carbon::parse($ticket->due_time)->toISOString() : $ticket->due_time->toISOString() }}').getTime(),
                                    now: new Date().getTime(),
                                    status: '{{ $ticket->status }}',
                                    slaStatus: '{{ $ticket->sla_status }}',
                                    get isStopped() { return ['resolved', 'closed'].includes(this.status); },
                                    get remaining() { return Math.max(0, this.dueTime - this.now); },
                                    get isBreached() { return this.slaStatus === 'breached' || (!this.isStopped && this.remaining === 0); },
                                    get isWarning() { return !this.isBreached && !this.isStopped && this.remaining > 0 && this.remaining < 3600000; },
                                    get formatted() {
                                        if (this.isStopped) return this.slaStatus === 'breached' ? 'Breached' : '-';
                                        if (this.isBreached) return 'Breached';
                                        let totalSeconds = Math.floor(this.remaining / 1000);
                                        let h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
                                        let m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
                                        let s = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
                                        return `${h}h ${m}m ${s}s`;
                                    }
                                }"
                                x-init="if (!isStopped) setInterval(() => now = new Date().getTime(), 1000)"
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border"
                                :class="{
                                    'bg-red-500/10 text-red-400 border-red-500/20': isBreached,
                                    'bg-orange-500/10 text-orange-400 border-orange-500/20': !isBreached && isWarning,
                                    'bg-green-500/10 text-green-400 border-green-500/20': !isBreached && !isWarning && !isStopped,
                                    'bg-slate-500/10 text-slate-400 border-slate-500/20': !isBreached && !isWarning && isStopped
                                }">
                                <svg x-show="isBreached" class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <svg x-show="!isBreached" class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span x-text="formatted"></span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-zinc-500 mb-2">SLA Status</p>
                            @php
                                $slaBadgeClass = match($ticket->sla_status) {
                                    'on_time' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                    'breached' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                    default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $slaBadgeClass }}">
                                {{ str_replace('_', ' ', ucfirst($ticket->sla_status)) }}
                            </span>
                        </div>
                    </div>
                    @endif

                    <div class="mt-6 pt-6 border-t border-zinc-800 space-y-2">
                        {{-- Assign / Reassign --}}

                        <x-dropdown-btn>
                            <x-slot:title>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Assign / Reassign
                                </span>
                            </x-slot:title>
                            @foreach ($agents as $agent)
                                <button wire:click="assign({{ $agent->id }})"
                                    wire:confirm="Assign to {{ $agent->name }}?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ $agent->name === Auth::user()->name ? $agent->name . ' (You)' : $agent->name }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Change Priority --}}

                        <x-dropdown-btn>
                            <x-slot:title>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Change Priority
                                </span>
                            </x-slot:title>
                            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                                <button wire:click="changePriority('{{ $priority }}')"
                                    wire:confirm="Change priority to {{ $priority }}?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ ucfirst($priority) }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Change Status --}}

                        <x-dropdown-btn>
                            <x-slot:title>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                                Change Status
                                </span>
                            </x-slot:title>
                            @foreach (['pending', 'open', 'in progress', 'resolved', 'closed'] as $status)
                                <button wire:click="changeStatus({{ json_encode($status) }})"
                                    wire:confirm="Change status to {{ $status }}?" @click="open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-white hover:bg-zinc-700 transition first:rounded-t-lg last:rounded-b-lg">
                                    {{ $status }}
                                </button>
                            @endforeach
                        </x-dropdown-btn>

                        {{-- Close Ticket --}}
                        <button wire:click="closeTicket" wire:confirm="Are you sure you want to close this ticket?"
                            class="w-full px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-red-400 rounded-lg transition text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Close ticket
                        </button>
                    </div>
                </div>

                {{-- Internal Notes --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-white">Internal notes</h3>
                        <span class="text-xs text-zinc-500">0 notes</span>
                    </div>
                    <p class="text-xs text-zinc-500 mb-4">Visible only to your team</p>

                    <div class="space-y-4 mb-4">
                        {{-- Notes will appear here --}}
                    </div>

                    {{-- Add Note Form --}}
                    <div>
                        <textarea rows="3" placeholder="Add an internal note for your team..."
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-zinc-600 resize-none mb-2"></textarea>
                        <div class="flex gap-2">
                            <button
                                class="flex-1 px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg transition text-sm">
                                Save note
                            </button>
                            <button
                                class="flex-1 px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition text-sm">
                                Add note
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
