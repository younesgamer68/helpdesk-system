<div>
    {{-- Inline animations for the chatbot widget --}}
    <style>
        @keyframes cw-gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .cw-animated-bg {
            background: linear-gradient(135deg,
                rgba(0,114,96,0.05) 0%, rgba(0,169,131,0.07) 25%,
                rgba(45,175,45,0.05) 50%, rgba(23,73,77,0.07) 75%,
                rgba(0,114,96,0.05) 100%);
            background-size: 400% 400%;
            animation: cw-gradient-shift 8s ease infinite;
        }
        @keyframes cw-msg-in {
            from { opacity: 0; transform: translateY(10px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .cw-msg-enter { animation: cw-msg-in 0.3s ease-out forwards; }
        @keyframes cw-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .cw-float { animation: cw-float 3s ease-in-out infinite; }
        @keyframes cw-pulse-ring {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.7); opacity: 0; }
        }
        .cw-pulse-ring::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, #17494D, #2DAF2D);
            animation: cw-pulse-ring 2s cubic-bezier(0.4,0,0.6,1) infinite;
            z-index: -1;
        }
        @keyframes cw-shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .cw-shimmer::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.06) 40%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0.06) 60%, transparent 100%);
            background-size: 200% 100%;
            animation: cw-shimmer 3s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes cw-dot-bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-4px); }
        }
        @keyframes cw-slide-up-anim {
            from { transform: translateY(100%); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        @keyframes cw-slide-down-anim {
            from { transform: translateY(0);    opacity: 1; }
            to   { transform: translateY(100%); opacity: 0; }
        }
        .cw-slide-up {
            animation: cw-slide-up-anim 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .cw-slide-down {
            animation: cw-slide-down-anim 0.25s cubic-bezier(0.4, 0, 1, 1) forwards;
        }
    </style>

    <div x-data="{
            isOpen: false,
            visible: false,
            openFromShortcut() {
                const panel = this.$refs.chatPanel;

                this.visible = true;
                panel.classList.remove('cw-slide-down');
                void panel.offsetWidth;
                panel.classList.add('cw-slide-up');
                this.isOpen = true;
                this.scrollToBottom();

                if (!$wire.chatting && !Array.isArray($wire.messages)) {
                    $wire.newConversation();
                    return;
                }

                if (!$wire.chatting && Array.isArray($wire.messages) && $wire.messages.length === 0) {
                    $wire.newConversation();
                }
            },
            toggle() {
                const panel = this.$refs.chatPanel;
                if (this.isOpen) {
                    this.isOpen = false;
                    panel.classList.remove('cw-slide-up');
                    panel.classList.add('cw-slide-down');
                    setTimeout(() => { this.visible = false; panel.classList.remove('cw-slide-down'); }, 250);
                } else {
                    this.visible = true;
                    panel.classList.remove('cw-slide-down');
                    void panel.offsetWidth;
                    panel.classList.add('cw-slide-up');
                    this.isOpen = true;
                    this.scrollToBottom();
                }
            },
            scrollToBottom() {
                setTimeout(() => {
                    if (this.$refs.chatContainer) this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                }, 50);
            }
        }"
        @scroll-to-bottom.window="scrollToBottom()"
        @open-chatbot-widget.window="openFromShortcut()"
        x-init="$watch('isOpen', value => { if(value) scrollToBottom() })"
        id="chatbot-widget"
        class="fixed bottom-6 right-6 z-[9999] flex flex-col items-end">

        {{-- Chat Window --}}
        <div x-ref="chatPanel" x-show="visible" x-cloak wire:ignore.self
            style="transform: translateY(100%); opacity: 0;"
            class="mb-4 flex flex-col w-[380px] h-[600px] bg-white rounded-3xl shadow-[0_30px_60px_-20px_rgba(0,114,96,0.3),0_0_0_1px_rgba(0,114,96,0.1)] overflow-hidden">

            {{-- ========== HOME VIEW (conversation list) ========== --}}
            @if(!$chatting)
                {{-- Header --}}
                <div class="cw-shimmer relative shrink-0 bg-linear-to-br from-[#0a0a0a] via-[#17494D] to-[#2DAF2D] px-4 pb-4 pt-4 overflow-hidden">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl  p-1">
                            <img src="{{ asset('images/Logos/logos without text DM.png') }}" alt="Helpdesk" class="h-7 w-7 object-contain" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold tracking-tight text-white">Helpdesk</p>
                            <div class="flex items-center gap-1.5">
                                <span class="h-[5px] w-[5px] rounded-full bg-green-400 shadow-[0_0_6px_#4ade80] animate-pulse"></span>
                                <p class="text-[10px] text-white/50">Online now</p>
                            </div>
                        </div>
                        <button @click="toggle()" type="button" class="rounded-lg p-1 text-white/40 transition hover:bg-white/10 hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-2 text-[12px] leading-snug text-white/60">Welcome back! How can we help?</p>
                </div>

                {{-- Conversation list --}}
                <div class="cw-animated-bg flex-1 overflow-y-auto [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-[#2DAF2D]/30">
                    @if(empty($conversations))
                        <div class="flex h-full flex-col items-center justify-center px-8 py-12">
                            <div class="cw-float mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-[rgba(45,175,45,0.1)] to-[rgba(45,175,45,0.03)] text-[#2DAF2D]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10">
                                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                    <path d="M14 22h5"></path>
                                    <path d="M9 8l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                                </svg>
                            </div>
                            <p class="mb-1 text-sm font-bold text-zinc-800">No conversations yet</p>
                            <p class="text-center text-xs text-zinc-400">Tap below to start a conversation.<br>We'll help you right away!</p>
                        </div>
                    @else
                        @foreach($conversations as $conv)
                            <button wire:click="selectConversation('{{ $conv['id'] }}')" type="button"
                                class="group relative flex w-full items-start gap-3 border-b border-gray-100 px-4 py-4 text-left transition hover:bg-zinc-50">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-green-100 bg-white shadow-sm text-[#2DAF2D]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                        <path d="M14 22h5"></path>
                                        <path d="M9 8l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1 pr-6">
                                    <div class="flex items-baseline gap-2 mb-1">
                                        <p class="text-[13px] font-bold text-zinc-800">Started {{ $conv['date'] }}</p>
                                        <p class="text-[11px] text-zinc-400">{{ $conv['short_date'] }}</p>
                                    </div>
                                    <p class="truncate text-[12px] text-zinc-800 leading-snug mb-2">Helpdesk: {{ $conv['preview'] }}</p>
                                    <span class="inline-flex items-center rounded-full bg-gray-200/60 px-2.5 py-0.5 text-[11px] font-medium text-zinc-800 border border-black/5">
                                        Ended
                                    </span>
                                </div>

                                {{-- Delete button (span to avoid nested button) --}}
                                <span @click.stop="$wire.deleteConversation('{{ $conv['id'] }}')"
                                    role="button"
                                    class="absolute right-3 top-4 cursor-pointer rounded p-1 text-zinc-600 transition-all duration-150 hover:bg-red-50 hover:text-red-500"
                                    title="Remove conversation">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            </button>
                        @endforeach
                    @endif
                </div>

                {{-- New conversation button --}}
                <div class="shrink-0 px-4 pb-3 pt-1">
                    <button wire:click="newConversation" type="button"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-linear-to-br from-[#00A983] to-[#2DAF2D] py-2.5 text-xs font-bold text-white shadow-[0_6px_16px_-4px_rgba(45,175,45,0.4),inset_0_1px_0_rgba(255,255,255,0.15)] transition hover:scale-[1.02] hover:shadow-[0_10px_22px_-5px_rgba(45,175,45,0.5)] active:scale-[0.97]">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New conversation
                    </button>
                </div>

                {{-- Footer --}}
<div class="shrink-0 border-t border-gray-200 bg-gray-50 py-2">
    <div class="flex items-center justify-center gap-2">
        <div class="flex h-3 w-3 items-center justify-center">
            <img src="{{ asset('images/Logos/logo without text LM.png') }}" alt="Helpdesk" class="h-4 w-4 object-contain" />
        </div>
        <p class="text-[9px] font-semibold tracking-wide text-zinc-400">
            BUILT WITH HELPDESK AI
        </p>
    </div>
</div>
            @else
                {{-- ========== CHAT VIEW ========== --}}

                {{-- Chat header --}}
                <div class="cw-shimmer relative flex shrink-0 items-center gap-2.5 bg-linear-to-br from-[#0a0a0a] via-[#17494D] to-[#2DAF2D] px-4 py-2.5 overflow-hidden">
                    <button wire:click="backToHome" type="button" class="rounded-lg p-1 text-white/50 transition hover:bg-white/10 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10 p-0.5 text-white">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                            <path d="M14 22h5"></path>
                            <path d="M9 8l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-white">Helpdesk Assistant</p>
                        <p class="text-[9px] text-white/40">Typically replies instantly</p>
                    </div>
                    <button @click="isOpen = false" type="button" class="rounded-lg p-1 text-white/50 transition hover:bg-white/10 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Messages Area with animated gradient --}}
                <div x-ref="chatContainer" class="cw-animated-bg flex-1 overflow-y-auto scroll-smooth p-4 flex flex-col gap-2 [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-[#2DAF2D]/30">

                    @foreach($messages as $index => $msg)
                        @if($msg['role'] === 'user')
                            <div class="flex justify-end cw-msg-enter" style="animation-delay: {{ $index * 40 }}ms">
                                <div class="flex max-w-[80%] flex-col items-end gap-0.5">
                                    <div class="rounded-xl rounded-br-md bg-linear-to-br from-[#00A983] to-[#2DAF2D] px-3 py-2 shadow-[0_3px_10px_rgba(45,175,45,0.2)]">
                                        <p class="text-xs leading-relaxed text-white whitespace-pre-wrap">{{ trim($msg['content']) }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-end gap-1.5 cw-msg-enter" style="animation-delay: {{ $index * 40 }}ms">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg p-0.5 text-[#2DAF2D]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                        <path d="M14 22h5"></path>
                                        <path d="M9 8l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                                    </svg>
                                </div>
                                <div class="flex max-w-[80%] flex-col gap-0.5">
                                    <div class="rounded-xl rounded-bl-md bg-white px-3 py-2 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_0_0_1px_rgba(0,0,0,0.02)]">
                                        <p class="text-xs leading-relaxed text-zinc-800 whitespace-pre-wrap">{{ preg_replace('/\*{1,2}([^*]+)\*{1,2}/', '$1', trim($msg['content'])) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Typing Indicator --}}
                    @if($isTyping)
                        <div class="flex items-end gap-1.5 cw-msg-enter">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg p-0.5 text-[#2DAF2D]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                    <path d="M14 22h5"></path>
                                    <path d="M9 8l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                                </svg>
                            </div>
                            <div class="rounded-xl rounded-bl-md bg-white px-3 py-2.5 shadow-[0_1px_3px_rgba(0,0,0,0.03),0_0_0_1px_rgba(0,0,0,0.02)]">
                                <div class="flex gap-1">
                                    <span class="h-[5px] w-[5px] rounded-full bg-[#2DAF2D] opacity-80" style="animation: cw-dot-bounce 1.2s infinite;"></span>
                                    <span class="h-[5px] w-[5px] rounded-full bg-[#2DAF2D] opacity-80" style="animation: cw-dot-bounce 1.2s infinite 150ms;"></span>
                                    <span class="h-[5px] w-[5px] rounded-full bg-[#2DAF2D] opacity-80" style="animation: cw-dot-bounce 1.2s infinite 300ms;"></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Replies --}}
                    @if(count($messages) <= 1)
                        <div class="flex flex-col gap-1.5 pt-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-400">Quick actions</p>
                            <button wire:click="setQuickReply('How does the ticketing system work?')"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-xs font-medium transition hover:border-[#2DAF2D] hover:bg-[rgba(45,175,45,0.04)] hover:text-[#1a7a1a] hover:shadow-[0_2px_6px_rgba(45,175,45,0.1)] active:scale-[0.98]">
                                How does the ticketing system work?
                            </button>
                            <button wire:click="setQuickReply('Can I try it for free?')"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-xs font-medium transition hover:border-[#2DAF2D] hover:bg-[rgba(45,175,45,0.04)] hover:text-[#1a7a1a] hover:shadow-[0_2px_6px_rgba(45,175,45,0.1)] active:scale-[0.98]">
                                Can I try it for free?
                            </button>
                            <button wire:click="setQuickReply('What integrations do you support?')"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-xs font-medium transition hover:border-[#2DAF2D] hover:bg-[rgba(45,175,45,0.04)] hover:text-[#1a7a1a] hover:shadow-[0_2px_6px_rgba(45,175,45,0.1)] active:scale-[0.98]">
                                What integrations do you support?
                            </button>
                            <button wire:click="setQuickReply('Tell me about pricing')"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-xs font-medium transition hover:border-[#2DAF2D] hover:bg-[rgba(45,175,45,0.04)] hover:text-[#1a7a1a] hover:shadow-[0_2px_6px_rgba(45,175,45,0.1)] active:scale-[0.98]">
                                Tell me about pricing
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Input Area --}}
                <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-2.5">
                    <div class="flex items-center gap-2">
                        <input type="text"
                            wire:model="message"
                            wire:keydown.enter="sendMessage"
                            placeholder="Type your message..."
                            autocomplete="off"
                            class="flex-1 rounded-[10px] border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#2DAF2D] focus:bg-white focus:ring-2 focus:ring-[rgba(45,175,45,0.2)] disabled:opacity-50" />
                        <button wire:click="sendMessage"
                            wire:loading.attr="disabled"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#00A983] to-[#2DAF2D] text-white shadow-[0_2px_8px_rgba(45,175,45,0.3)] transition active:scale-90 disabled:opacity-30">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Floating Toggle Button with pulse ring --}}
        <button @click="toggle()"
            type="button"
            class="cw-pulse-ring relative flex h-[52px] w-[52px] items-center justify-center rounded-2xl bg-linear-to-br from-[#17494D] to-[#2DAF2D] shadow-[0_8px_24px_-6px_rgba(0,114,96,0.5),0_0_0_2px_rgba(0,114,96,0.2)] transition-all duration-200 hover:scale-[1.06] hover:shadow-[0_12px_28px_-6px_#0f2b0f,0_0_0_2px_#2DAF2D] active:scale-95">
    <img x-show="!isOpen" 
     src="{{ asset('images/Logos/logos without text DM.png') }}" 
     alt="Chat" 
     style="height: 27px; width: 27px;" 
     class="object-contain" />
           
            <svg x-show="isOpen" x-cloak class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    </div>
</div>
