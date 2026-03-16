<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chatbot Widget</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-zinc-100 text-zinc-900 antialiased">
    <div class="h-screen w-full flex flex-col rounded-2xl overflow-hidden border border-zinc-200 bg-white">
        <div class="px-4 py-3 border-b border-zinc-200 bg-zinc-50">
            <h1 class="text-sm font-semibold">{{ $company->name }} Assistant</h1>
        </div>

        <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-white"></div>

        <div id="chat-input-area" class="border-t border-zinc-200 p-3 bg-zinc-50">
            <form id="chat-form" class="flex items-center gap-2">
                <input id="chat-message" type="text" maxlength="500" required
                    class="flex-1 rounded-lg border border-zinc-300 px-3 py-2 text-sm bg-white"
                    placeholder="Type your message...">
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium">
                    Send
                </button>
            </form>
        </div>

        <div id="ticket-form-area" class="hidden border-t border-zinc-200 p-4 bg-zinc-50">
            <h2 class="text-sm font-semibold mb-3">Create a support ticket</h2>
            <form id="ticket-form" class="space-y-2">
                <input name="customer_name" required placeholder="Your name"
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm bg-white">
                <input name="customer_email" type="email" required placeholder="Your email"
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm bg-white">
                <input name="subject" required placeholder="Subject"
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm bg-white">
                <textarea name="description" required rows="4" placeholder="Describe your issue"
                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm bg-white"></textarea>
                <button type="submit"
                    class="w-full px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                    Submit Ticket
                </button>
            </form>
            <p id="ticket-feedback" class="mt-2 text-xs text-zinc-500"></p>
        </div>
    </div>

    <script>
        const chatbotUrl = @js(route('chatbot.widget.message', ['company' => $company->slug, 'key' => $widgetSetting->widget_key]));
        const ticketSubmitUrl = @js(route('widget.submit', ['company' => $company->slug, 'key' => $widgetSetting->widget_key]));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const greeting = @js($aiSettings->chatbot_greeting ?: 'Hi! How can I help you today?');

        const messages = document.getElementById('messages');
        const chatForm = document.getElementById('chat-form');
        const chatMessage = document.getElementById('chat-message');
        const chatInputArea = document.getElementById('chat-input-area');
        const ticketFormArea = document.getElementById('ticket-form-area');
        const ticketForm = document.getElementById('ticket-form');
        const ticketFeedback = document.getElementById('ticket-feedback');

        function appendMessage(role, text) {
            const row = document.createElement('div');
            row.className = role === 'bot' ? 'flex' : 'flex justify-end';

            const bubble = document.createElement('div');
            bubble.className = role === 'bot' ?
                'max-w-[85%] rounded-2xl px-3 py-2 text-sm bg-zinc-100 text-zinc-800' :
                'max-w-[85%] rounded-2xl px-3 py-2 text-sm bg-teal-600 text-white';
            bubble.textContent = text;

            row.appendChild(bubble);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;
        }

        async function sendMessage(message) {
            const response = await fetch(chatbotUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message
                }),
            });

            if (!response.ok) {
                throw new Error('Chat failed');
            }

            return response.json();
        }

        async function submitTicket(formData) {
            const response = await fetch(ticketSubmitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to submit ticket.');
            }

            return data;
        }

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const content = chatMessage.value.trim();
            if (!content) {
                return;
            }

            appendMessage('user', content);
            chatMessage.value = '';

            try {
                const payload = await sendMessage(content);
                appendMessage('bot', payload.reply || 'I could not answer that right now.');

                if (payload.show_ticket_form) {
                    chatInputArea.classList.add('hidden');
                    ticketFormArea.classList.remove('hidden');
                    appendMessage('bot', 'I can open a support ticket for you now.');
                }
            } catch (error) {
                appendMessage('bot', 'Something went wrong. Please try again.');
            }
        });

        ticketForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            ticketFeedback.textContent = 'Submitting ticket...';

            const formData = new FormData(ticketForm);

            try {
                const result = await submitTicket(formData);
                ticketFeedback.textContent = result.message || 'Ticket submitted successfully.';
                ticketForm.reset();
            } catch (error) {
                ticketFeedback.textContent = error.message;
            }
        });

        appendMessage('bot', greeting);
    </script>
</body>

</html>
