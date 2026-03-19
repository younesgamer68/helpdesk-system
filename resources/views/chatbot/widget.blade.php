<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company->name }} Chat</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: transparent;
        }

        .chat-bubble {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #0d9488;
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            z-index: 9999;
        }

        .chat-bubble:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
        }

        .chat-bubble svg {
            width: 26px;
            height: 26px;
            transition: opacity 0.15s ease, transform 0.15s ease;
        }

        .chat-bubble .icon-close {
            position: absolute;
        }

        .chat-bubble[data-open="false"] .icon-close {
            opacity: 0;
            transform: rotate(-90deg) scale(0.5);
        }

        .chat-bubble[data-open="false"] .icon-chat {
            opacity: 1;
            transform: rotate(0) scale(1);
        }

        .chat-bubble[data-open="true"] .icon-close {
            opacity: 1;
            transform: rotate(0) scale(1);
        }

        .chat-bubble[data-open="true"] .icon-chat {
            opacity: 0;
            transform: rotate(90deg) scale(0.5);
        }

        .chat-panel {
            position: fixed;
            bottom: 96px;
            right: 24px;
            width: 380px;
            max-height: 520px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 9998;
            transform: translateY(16px) scale(0.95);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chat-panel.open {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #0d9488;
            color: #fff;
        }

        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .chat-header-logo {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            object-fit: contain;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.15);
            padding: 2px;
        }

        .chat-header-title {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .chat-header-close {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.85);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: color 0.15s, background 0.15s;
        }

        .chat-header-close:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 280px;
            max-height: 340px;
            background: #f9fafb;
        }

        .chat-messages::-webkit-scrollbar {
            width: 5px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .msg-row {
            display: flex;
            animation: msgIn 0.25s ease-out;
        }

        .msg-row.bot {
            justify-content: flex-start;
        }

        .msg-row.user {
            justify-content: flex-end;
        }

        .msg-bubble {
            max-width: 82%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .msg-row.bot .msg-bubble {
            background: #fff;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 4px;
        }

        .msg-row.user .msg-bubble {
            background: #0d9488;
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .escalation-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 8px 18px;
            background: #0d9488;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.15s;
        }

        .escalation-btn:hover {
            background: #0f766e;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            width: fit-content;
        }

        .typing-dots span {
            width: 7px;
            height: 7px;
            background: #9ca3af;
            border-radius: 50%;
            animation: bounce 1.2s infinite;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.15s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.3s;
        }

        .chat-input-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        .chat-input-bar input {
            flex: 1;
            padding: 9px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 13.5px;
            outline: none;
            transition: border-color 0.15s;
            background: #fff;
            color: #111827;
        }

        .chat-input-bar input:focus {
            border-color: #0d9488;
        }

        .chat-input-bar button {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #0d9488;
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
            flex-shrink: 0;
        }

        .chat-input-bar button:hover {
            background: #0f766e;
        }

        .chat-input-bar button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        @keyframes msgIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-5px);
            }
        }

        @media (max-width: 440px) {
            .chat-panel {
                right: 8px;
                left: 8px;
                bottom: 88px;
                width: auto;
            }

            .chat-bubble {
                bottom: 16px;
                right: 16px;
            }
        }
    </style>
</head>

<body>
    {{-- Floating bubble button --}}
    <button class="chat-bubble" id="chatBubble" data-open="false" aria-label="Open chat">
        <svg class="icon-chat" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <svg class="icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Chat panel --}}
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header">
            <div class="chat-header-left">
                @if ($company->logo)
                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" class="chat-header-logo">
                @endif
                <span class="chat-header-title">{{ $company->name }}</span>
            </div>
            <button class="chat-header-close" id="chatClose" aria-label="Close chat">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="chat-messages" id="messages"></div>

        <div class="chat-input-bar" id="chatInputBar">
            <form id="chatForm" style="display:flex;gap:8px;width:100%;">
                <input id="chatInput" type="text" maxlength="500" required placeholder="Type a message..."
                    autocomplete="off">
                <button type="submit" id="sendBtn" aria-label="Send">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const chatbotUrl = @js(route('chatbot.widget.message', ['company' => $company->slug, 'key' => $widgetSetting->widget_key]));
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const greeting = @js($aiSettings->chatbot_greeting ?: 'Hi! How can I help you today?');

            const bubble = document.getElementById('chatBubble');
            const panel = document.getElementById('chatPanel');
            const closeBtn = document.getElementById('chatClose');
            const messages = document.getElementById('messages');
            const chatForm = document.getElementById('chatForm');
            const chatInput = document.getElementById('chatInput');
            const sendBtn = document.getElementById('sendBtn');

            let isOpen = false;
            let isEscalated = false;
            let sessionId = null;

            function toggle() {
                isOpen = !isOpen;
                bubble.dataset.open = isOpen;
                panel.classList.toggle('open', isOpen);
                if (isOpen) chatInput.focus();
            }

            bubble.addEventListener('click', toggle);
            closeBtn.addEventListener('click', toggle);

            function appendMessage(role, html) {
                const row = document.createElement('div');
                row.className = 'msg-row ' + role;
                const bub = document.createElement('div');
                bub.className = 'msg-bubble';
                bub.innerHTML = html;
                row.appendChild(bub);
                messages.appendChild(row);
                messages.scrollTop = messages.scrollHeight;
            }

            function showTyping() {
                const row = document.createElement('div');
                row.className = 'msg-row bot';
                row.id = 'typing';
                row.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
                messages.appendChild(row);
                messages.scrollTop = messages.scrollHeight;
            }

            function hideTyping() {
                const el = document.getElementById('typing');
                if (el) el.remove();
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            async function sendMessage(message) {
                const resp = await fetch(chatbotUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        ...(sessionId ? {
                            'X-Chatbot-Session': sessionId
                        } : {}),
                    },
                    body: JSON.stringify({
                        message
                    }),
                });

                if (!resp.ok) throw new Error('Request failed');
                return resp.json();
            }

            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (isEscalated) return;

                const text = chatInput.value.trim();
                if (!text) return;

                appendMessage('user', escapeHtml(text));
                chatInput.value = '';
                sendBtn.disabled = true;
                showTyping();

                try {
                    const data = await sendMessage(text);
                    if (data.session_id) {
                        sessionId = data.session_id;
                    }
                    hideTyping();
                    appendMessage('bot', escapeHtml(data.reply || 'I could not answer that right now.'));

                    if (data.show_ticket_form && data.escalation_url) {
                        isEscalated = true;
                        const escalationHtml =
                            escapeHtml(
                                "I wasn't able to fully help with that. Let me connect you with our support team!"
                            ) +
                            '<br><a class="escalation-btn" href="' + escapeHtml(data.escalation_url) +
                            '" target="_blank" rel="noopener">Submit a Ticket</a>';
                        appendMessage('bot', escalationHtml);
                        chatInput.disabled = true;
                        chatInput.placeholder = 'Chat ended — use the button above';
                    }
                } catch (err) {
                    hideTyping();
                    appendMessage('bot', escapeHtml('Something went wrong. Please try again.'));
                }

                sendBtn.disabled = false;
            });

            // Show greeting on load
            appendMessage('bot', escapeHtml(greeting));
        })();
    </script>
</body>

</html>
