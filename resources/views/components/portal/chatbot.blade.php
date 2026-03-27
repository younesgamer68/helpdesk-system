{{-- ========== CHATBOT WIDGET ========== --}}
<div id="chatbot-widget" class="fixed bottom-0 right-0 z-[9999] text-zinc-900">
    <div x-data="{
        open: false,
        chatting: false,
        sending: false,
        userInput: '',
        conversations: [],
        activeConvIndex: -1,
        quickActions: [
            'Connect with sales',
            'Start a trial',
            'Pricing',
            'Request a demo',
            'Learn about Helpdesk',
            'Help with my account',
        ],
    
        get activeMessages() {
            if (this.activeConvIndex >= 0 && this.conversations[this.activeConvIndex]) {
                return this.conversations[this.activeConvIndex].messages;
            }
            return [];
        },
    
        get showQuickActions() {
            const msgs = this.activeMessages;
            const userMsgs = msgs.filter(m => m.type === 'user');
            return userMsgs.length === 0 && !this.sending;
        },
    
        toggle() {
            this.open = !this.open;
            if (!this.open) this.chatting = false;
        },
    
        forceOpen() {
            this.open = true;
        },
    
        openConversation(index) {
            this.activeConvIndex = index;
            this.chatting = true;
            this.$nextTick(() => this.scrollToBottom());
        },
    
        newConversation() {
            const now = new Date();
            const conv = {
                id: Date.now(),
                date: now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
                time: now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }),
                messages: [],
            };
            this.conversations.unshift(conv);
            this.activeConvIndex = 0;
            this.chatting = true;
    
            this.addBotMessage('Hi there! I\'m your <strong>AI assistant</strong>. I\'m here to help you transform your support experience.');
            setTimeout(() => {
                this.addBotMessage('Ask me anything, pick an option below, or type your own question.');
                this.scrollToBottom();
            }, 400);
        },
    
        backToHome() {
            this.chatting = false;
        },
    
        close() {
            this.open = false;
            this.chatting = false;
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.open = false;
                    this.chatting = false;
                });
            });
        },
    
        addBotMessage(text) {
            if (this.activeConvIndex < 0) return;
            this.conversations[this.activeConvIndex].messages.push({
                type: 'bot',
                text,
                time: new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }),
            });
        },
    
        addUserMessage(text) {
            if (this.activeConvIndex < 0) return;
            this.conversations[this.activeConvIndex].messages.push({
                type: 'user',
                text,
                time: new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }),
            });
        },
    
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.chatBody;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    
        async sendMessage(text) {
            text = (text || '').trim();
            if (!text || this.sending) return;
    
            this.addUserMessage(text);
            this.userInput = '';
            this.sending = true;
            this.scrollToBottom();
    
            try {
                const res = await fetch('{{ route('chatbot.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                this.addBotMessage(data.reply || 'Sorry, something went wrong.');
            } catch (e) {
                this.addBotMessage('Sorry, I couldn\'t process that. Please try again.');
            } finally {
                this.sending = false;
                this.scrollToBottom();
            }
        },
    
        init() {
            window.addEventListener('open-chatbot-widget', () => {
                this.forceOpen();
            });
        },
    }">

        {{-- MAIN CARD (PORTRAIT) --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="fixed bottom-[100px] right-6 h-[600px] w-[380px] overflow-hidden rounded-3xl bg-white shadow-[0_30px_60px_-20px_rgba(33,150,83,0.3),0_0_0_1px_rgba(33,150,83,0.1)]">

            {{-- ========== HOME VIEW ========== --}}
            <div x-show="!chatting" class="flex h-full flex-col">
                {{-- Header --}}
                <div class="shrink-0 bg-linear-to-br from-[#0a0a0a] via-[#17494D] to-brand px-4 pb-4 pt-4">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-linear-to-br from-[#1b7a44] to-brand shadow-[0_4px_10px_rgba(33,150,83,0.3)]">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold tracking-tight text-white">Helpdesk</p>
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="h-[5px] w-[5px] rounded-full bg-[#219653] shadow-[0_0_6px_#219653] animate-[chatbot-pulse_2s_infinite]"></span>
                                <p class="text-[10px] text-white/50">Online now</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conversations list --}}
                <div
                    class="flex-1 overflow-y-auto [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-brand/30">
                    <template x-for="(conv, idx) in conversations" :key="conv.id">
                        <button @click="openConversation(idx)" type="button"
                            class="group flex w-full items-start gap-3 border-b border-gray-100 px-4 py-3 text-left transition hover:bg-[rgba(33,150,83,0.04)]">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                :class="idx === 0 ? 'bg-[rgba(33,150,83,0.15)]' : 'bg-[rgba(33,150,83,0.06)]'">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" :class="idx === 0 ? 'text-brand' : 'text-brand/60'">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="mb-0.5 flex items-center justify-between gap-1">
                                    <p class="truncate text-[11px] font-semibold text-zinc-800"
                                        x-text="'Started ' + conv.date + ' at ' + conv.time"></p>
                                    <span class="shrink-0 text-[9px] text-zinc-400"
                                        x-text="conv.messages.length ? conv.messages[conv.messages.length-1].time : ''"></span>
                                </div>
                                <p class="truncate text-[11px] leading-snug text-zinc-500">
                                    <span class="text-brand">Bot:</span>
                                    <span
                                        x-text="(conv.messages.filter(m => m.type==='bot').slice(-1)[0]?.text || '').replace(/<[^>]*>/g,'').substring(0,45)"></span>
                                </p>
                            </div>
                            <svg class="mt-1.5 h-3.5 w-3.5 shrink-0 text-zinc-300 transition group-hover:text-brand"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="conversations.length === 0">
                        <div class="flex h-full flex-col items-center justify-center px-8 py-12">
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-[rgba(33,150,83,0.1)] to-[rgba(33,150,83,0.03)]">
                                <svg class="h-7 w-7 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                                </svg>
                            </div>
                            <p class="mb-1 text-sm font-bold text-zinc-800">No conversations yet</p>
                            <p class="text-center text-xs text-zinc-400">
                                Tap below to start a conversation.<br>We'll help you right away!
                            </p>
                        </div>
                    </template>
                </div>

                {{-- New conversation button --}}
                <div class="shrink-0 px-4 pb-3 pt-1">
                    <button @click="newConversation()" type="button"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-linear-to-br from-[#1b7a44] to-brand py-2.5 text-xs font-bold text-white shadow-[0_6px_16px_-4px_rgba(33,150,83,0.4),inset_0_1px_0_rgba(255,255,255,0.15)] transition hover:scale-[1.02] hover:shadow-[0_10px_22px_-5px_rgba(33,150,83,0.5)] active:scale-[0.97]">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New conversation
                    </button>
                </div>

                {{-- Footer --}}
                <div class="shrink-0 border-t border-gray-200 bg-gray-50 py-2">
                    <p
                        class="flex items-center justify-center gap-1.5 text-[9px] font-semibold tracking-wide text-zinc-400">
                        <span class="h-[5px] w-[5px] rounded-full bg-[#1b7a44] opacity-50"></span>
                        BUILT WITH HELPDESK
                    </p>
                </div>
            </div>

            {{-- ========== CHAT VIEW ========== --}}
            <div x-show="chatting" class="flex h-full flex-col">
                {{-- Chat header --}}
                <div
                    class="flex shrink-0 items-center gap-2.5 bg-linear-to-br from-[#0a0a0a] via-[#17494D] to-brand px-4 py-2.5">
                    <button @click.stop.prevent="backToHome()" type="button"
                        class="rounded-lg p-1 text-white/50 transition hover:bg-white/10 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-linear-to-br from-[#1b7a44] to-brand shadow-[0_3px_8px_rgba(33,150,83,0.25)]">
                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-white">Helpdesk Assistant</p>
                        <p class="text-[9px] text-white/40">Typically replies instantly</p>
                    </div>
                    <button @click.stop.prevent="close()" type="button"
                        class="rounded-lg p-1 text-white/50 transition hover:bg-white/10 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Chat messages body --}}
                <div class="flex flex-1 flex-col overflow-y-auto scroll-smooth bg-gray-50/50 [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-brand/30"
                    x-ref="chatBody">
                    <div class="flex justify-center px-4 pt-2 pb-1">
                        <span
                            class="rounded-full bg-black/[0.04] px-2 py-0.5 text-[9px] font-medium text-zinc-400 backdrop-blur-sm"
                            x-text="conversations[activeConvIndex]?.date + ' · ' + conversations[activeConvIndex]?.time"></span>
                    </div>

                    <div class="flex flex-col gap-2 px-4 pb-4">
                        <template x-for="(msg, index) in activeMessages" :key="index">
                            <div>
                                {{-- Bot message --}}
                                <template x-if="msg.type === 'bot'">
                                    <div class="flex items-end gap-1.5">
                                        <div
                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#1b7a44] to-brand">
                                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                            </svg>
                                        </div>
                                        <div class="flex max-w-[78%] flex-col gap-0.5">
                                            <div
                                                class="rounded-xl rounded-bl-md bg-white px-3 py-2 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_0_0_1px_rgba(0,0,0,0.02)]">
                                                <p class="text-xs leading-relaxed text-zinc-800" x-html="msg.text">
                                                </p>
                                            </div>
                                            <span class="pl-1 text-[9px] text-zinc-400" x-text="msg.time"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- User message --}}
                                <template x-if="msg.type === 'user'">
                                    <div class="flex justify-end">
                                        <div class="flex max-w-[78%] flex-col items-end gap-0.5">
                                            <div
                                                class="rounded-xl rounded-br-md bg-linear-to-br from-[#1b7a44] to-brand px-3 py-2 shadow-[0_3px_10px_rgba(33,150,83,0.2)]">
                                                <p class="text-xs leading-relaxed text-white" x-text="msg.text"></p>
                                            </div>
                                            <span class="pr-1 text-[9px] text-zinc-400" x-text="msg.time"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Quick actions --}}
                        <div class="flex flex-col gap-1.5 pt-0.5" x-show="showQuickActions">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-400">quick actions
                            </p>
                            <template x-for="action in quickActions" :key="action">
                                <button @click="sendMessage(action)" type="button"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-xs font-medium transition hover:border-[#1b7a44] hover:bg-[rgba(33,150,83,0.04)] hover:text-[#1a7a1a] hover:shadow-[0_2px_6px_rgba(33,150,83,0.1)] active:scale-[0.98]"
                                    x-text="action">
                                </button>
                            </template>
                        </div>

                        {{-- Typing indicator --}}
                        <div class="flex items-end gap-1.5" x-show="sending">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#1b7a44] to-brand">
                                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                </svg>
                            </div>
                            <div
                                class="rounded-xl rounded-bl-md bg-white px-3 py-2.5 shadow-[0_1px_3px_rgba(0,0,0,0.03),0_0_0_1px_rgba(0,0,0,0.02)]">
                                <div class="flex gap-1">
                                    <span
                                        class="h-[5px] w-[5px] rounded-full bg-[#1b7a44] opacity-80 animate-[chatbot-bounce_1.2s_infinite]"></span>
                                    <span
                                        class="h-[5px] w-[5px] rounded-full bg-[#1b7a44] opacity-80 animate-[chatbot-bounce_1.2s_infinite_150ms]"></span>
                                    <span
                                        class="h-[5px] w-[5px] rounded-full bg-[#1b7a44] opacity-80 animate-[chatbot-bounce_1.2s_infinite_300ms]"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chat input --}}
                <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-2.5">
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="userInput" x-on:keydown.enter.prevent="sendMessage(userInput)"
                            :disabled="sending" placeholder="Type your message..." autocomplete="off"
                            class="flex-1 rounded-[10px] border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#1b7a44] focus:bg-white focus:ring-2 focus:ring-[rgba(33,150,83,0.3)] disabled:opacity-50" />
                        <button type="button" x-on:click="sendMessage(userInput)"
                            :disabled="!userInput.trim() || sending"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#1b7a44] to-brand text-white shadow-[0_2px_8px_rgba(33,150,83,0.3)] transition active:scale-90 disabled:opacity-30">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toggle button --}}
        <button @click="toggle()" type="button"
            class="fixed bottom-6 right-6 z-[9999] flex h-[52px] w-[52px] items-center justify-center rounded-2xl bg-linear-to-br from-[#17494D] to-brand shadow-[0_8px_24px_-6px_rgba(33,150,83,0.5),0_0_0_2px_rgba(33,150,83,0.2)] transition-all duration-200 hover:scale-[1.06] hover:shadow-[0_12px_28px_-6px_#0f2b0f,0_0_0_2px_#1b7a44] active:scale-95">
            <svg x-show="!open" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
            <svg x-show="open" x-cloak class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    </div>
</div>
