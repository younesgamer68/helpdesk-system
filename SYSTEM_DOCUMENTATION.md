# Helpdesk System — Technical Documentation

---

## Table of Contents

1. [Notification System](#1-notification-system)
2. [Live Chat (Real-Time Conversations)](#2-live-chat-real-time-conversations)
3. [AI Summary & AI Suggestion Settings](#3-ai-summary--ai-suggestion-settings)
4. [Embeddable Widgets (Iframe & Script Embeds)](#4-embeddable-widgets-iframe--script-embeds)

---

## 1. Notification System

### Overview

The notification system uses **Laravel Notifications** with three delivery channels: **database** (persisted), **broadcast** (real-time via WebSockets), and a **toast UI** (visual popup). Notifications flow from server-side events through Laravel Reverb WebSockets to the browser in real time.

### The 11 Notification Classes

All notification classes live in `app/Notifications/` and implement both `database` and `broadcast` channels:

| Notification            | Trigger                                          | Type Key            | Respects Preferences? |
| ----------------------- | ------------------------------------------------ | ------------------- | --------------------- |
| `TicketSubmitted`       | New ticket from widget/form                      | `ticket_submitted`  | Yes                   |
| `TicketAssigned`        | Ticket assigned to an operator                   | `assigned`          | Yes                   |
| `TicketReassigned`      | Ticket reassigned away from previous operator    | `reassigned`        | Yes                   |
| `TicketUnassigned`      | Auto-assignment failed, ticket left unassigned   | `ticket_unassigned` | No (always sent)      |
| `TicketStatusChanged`   | Ticket status transitions (e.g. open → resolved) | `status_changed`    | Yes                   |
| `TicketPriorityChanged` | Ticket priority modified                         | `priority_changed`  | No (always sent)      |
| `ClientReplied`         | Customer replies to a ticket                     | `client_replied`    | Yes                   |
| `InternalNoteAdded`     | Agent adds an internal note                      | `internal_note`     | Yes                   |
| `UserMentioned`         | Agent @mentions another user in an internal note | `mentioned`         | Yes                   |
| `SlaBreached`           | SLA deadline passed (runs via scheduled command) | `sla_breached`      | No (always sent)      |
| `TeamAssigned`          | User added to a team                             | `team_assigned`     | Yes                   |

### Notification Preferences

Users can toggle which notifications they receive. Preferences are stored as a JSON column on the `users` table (`notification_preferences`):

```
{
    "ticket_assigned": true,
    "ticket_reassigned": true,
    "client_replied": true,
    "status_changed": true,
    "internal_note": true,
    "ticket_submitted": true,
    "team_assigned": true
}
```

**How it works:** Each notification's `via()` method calls `$notifiable->wantsNotification('type_key')`. If the user has set a preference to `false`, the notification returns an empty channel array and is not delivered. If no preference is set, it defaults to `true`.

Three notifications **always** send regardless of preferences: `SlaBreached`, `TicketUnassigned`, and `TicketPriorityChanged`.

The settings UI is managed by `app/Livewire/Settings/NotificationPreferences.php`.

### Real-Time Delivery Flow

```
1. Server Action (e.g. new reply)
   │
   ▼
2. $user->notify(new ClientReplied($ticket))
   │
   ├──► DATABASE: Stored in `notifications` table (UUID primary key, JSON data, read_at timestamp)
   │
   └──► BROADCAST: Sent via Laravel Reverb to private channel `App.Models.User.{userId}`
         │
         ▼
3. resources/js/app.js (boots on `livewire:init`)
   - Echo subscribes to: window.Echo.private(`App.Models.User.${userId}`)
   - On `.notification()` event received:
     a) Dispatches browser CustomEvent `helpdesk:notification` (for Alpine toast)
     b) Dispatches Livewire event `notifications-updated` (for NotificationBell refresh)
         │
         ▼
4. TWO UI reactions happen simultaneously:
   │
   ├──► NotificationBell component (Livewire)
   │    - Listens for `#[On('notifications-updated')]`
   │    - Re-renders: fetches latest 10 notifications + unread count
   │    - Dropdown shows notification list with mark-as-read actions
   │
   └──► Toast popup (Alpine.js in notification-bell.blade.php)
        - Listens for `helpdesk:notification` window event
        - Shows 5-second auto-dismissing toast with title based on notification type
        - Clickable link to the relevant ticket
```

### Channel Authorization

In `routes/channels.php`:

```php
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

Only the authenticated user matching the channel ID can subscribe — no other user can receive their notifications.

### Marking Notifications as Read

- **Single:** `$notification->markAsRead()` sets `read_at = now()` — also redirects to ticket if applicable.
- **All:** `Auth::user()->unreadNotifications()->update(['read_at' => now()])`.

### NotificationsPage

`app/Livewire/Notifications/NotificationsPage.php` provides a full-page notification center with:

- **Tab-based filtering:** All, Unread, Assigned, Replies, System, SLA, Teams, Mentions
- **Role-based tabs:** SLA tab for admins & operators only; Teams tab for operators only
- **Infinite scroll:** Load 20 more at a time
- **Bulk actions:** Mark all read, clear all
- **Direct Echo listener:** Also listens on the private user channel for instant updates without relying on the separate app.js dispatcher

---

## 2. Live Chat (Real-Time Conversations)

### Overview

The ticket conversation system provides real-time messaging between customers and agents using **Laravel Reverb WebSockets** and **Livewire Echo listeners**. There is **no polling** — all updates are instant via broadcast events.

### Architecture

```
                                   ┌──────────────────┐
                                   │  Laravel Reverb   │
                                   │  (WebSocket Server)│
                                   └────────┬─────────┘
                                            │
                    ┌───────────────────────┼───────────────────────┐
                    │                       │                       │
           ┌────────▼────────┐    ┌────────▼────────┐    ┌────────▼────────┐
           │  Customer View   │    │   Agent View     │    │  Other Agents   │
           │  (Widget/Track)  │    │  (TicketDetails)  │    │  (same ticket)  │
           └─────────────────┘    └──────────────────┘    └─────────────────┘
```

All participants listen on a **public** channel: `ticket.{ticketId}`.

### Broadcast Events

Two event classes in `app/Events/`:

**`NewTicketReply`** — Fired when a reply (or internal note) is created.

- Channel: `ticket.{ticketId}` (public)
- Broadcast name: `.NewTicketReply`
- Payload: only `ticketId` (no message content in the event — the component re-fetches fresh data on receive)
- Implements `ShouldBroadcastNow` (no queue delay)

**`TicketTypingUpdated`** — Fired when someone starts or stops typing.

- Channel: `ticket.{ticketId}` (public)
- Broadcast name: `.TicketTypingUpdated`
- Payload: only `ticketId`
- Implements `ShouldBroadcastNow`

### Customer-Side Component

**File:** `app/Livewire/Tickets/Widget/TicketConversation.php`

**Echo listeners** (registered via `getListeners()`):

```
echo:ticket.{ticketId},.NewTicketReply   → refreshConversation()
echo:ticket.{ticketId},.TicketTypingUpdated → refreshTyping()
```

**Reply flow:**

1. Customer types in textarea → debounced 1s → `markTyping()` → sets Cache + broadcasts `TicketTypingUpdated`
2. Customer submits → `submitReply()` → validates → `createReply()`:
    - Stores attachments (max 2 files × 2MB)
    - Creates `TicketReply` record
    - Notifies assigned agent (`ClientReplied` notification)
    - Clears typing state
    - `broadcast(new NewTicketReply($ticketId))->toOthers()`
3. On the agent's side, the Echo listener fires → Livewire re-renders → fresh replies appear instantly.

**Special cases:**

- **Resolved tickets:** Customer reply reopens the ticket (if within `reopen_hours` SLA window)
- **Closed tickets:** Customer reply creates a new linked follow-up ticket (if within `linked_ticket_days` SLA window)

### Agent-Side Component

**File:** `app/Livewire/Tickets/TicketDetails.php`

**Same Echo listeners:**

```
echo:ticket.{ticketId},.NewTicketReply   → refreshConversation()
echo:ticket.{ticketId},.TicketTypingUpdated → refreshTyping()
```

**Reply flow:**

1. Agent types → `updatedMessage()` lifecycle hook fires on every keystroke:
    - Sets Cache: `ticket:typing:agent:{ticketId}` = agent's name (6s TTL)
    - Broadcasts `TicketTypingUpdated`
2. Agent submits → `addReply()`:
    - Stores attachments, creates `TicketReply`, sets status to `pending`
    - Sends email notification to customer (if ticket is verified)
    - Notifies other assigned agent (if reply is from admin disguised as agent)
    - Clears typing state + broadcasts
    - `broadcast(new NewTicketReply($ticketId))->toOthers()`
3. Customer's Echo listener fires → component re-renders → new reply appears.

**Internal notes:** `addInternalNote()` also broadcasts `NewTicketReply` so other agents viewing the same ticket see new notes in real time.

### Typing Indicator System

Typing indicators use a **Cache + Broadcast** hybrid approach:

| Actor    | Cache Key                           | Cache Value           | TTL       |
| -------- | ----------------------------------- | --------------------- | --------- |
| Customer | `ticket:typing:customer:{ticketId}` | `true`                | 6 seconds |
| Agent    | `ticket:typing:agent:{ticketId}`    | Agent's name (string) | 6 seconds |

**How it works:**

1. Person types → Cache key is set → `TicketTypingUpdated` broadcast fired
2. Other side receives broadcast → Livewire re-renders → checks Cache
3. If Cache has a value → typing indicator shown ("Agent Smith is typing" / "Customer is typing")
4. Cache expires after 6 seconds → indicator disappears naturally
5. When a message is sent → Cache is explicitly cleared → broadcast fired → indicator disappears instantly

**Agent name display:** The agent's actual name is stored in the Cache value, so the customer sees personalized "Agent Smith is typing" instead of generic text. For legacy boolean values, it falls back to "Support team is typing".

### Echo Setup

**`resources/js/echo.js`** — Configures `window.Echo` with Laravel Reverb:

```javascript
window.Echo = new Echo({
    broadcaster: "reverb",
    key: VITE_REVERB_APP_KEY,
    wsHost,
    wsPort,
    wssPort,
    forceTLS,
    enabledTransports: ["ws", "wss"],
});
```

This file is loaded:

- In the main app layout (via `resources/js/app.js` which imports `echo.js`) for authenticated agents
- Via `@vite(['resources/js/echo.js'])` on the customer tracking page (`resources/views/portal/track.blade.php`)

### Customer Tracking Page

The customer accesses their ticket at: `https://{company}.{domain}/track/{ticketNumber}/{trackingToken}`

This page (`resources/views/portal/track.blade.php`):

- Loads Echo via Vite for WebSocket support
- Embeds the Livewire `widget.ticket-conversation` component
- Customer is NOT authenticated — the tracking token acts as the access credential
- All real-time features (new replies, typing indicators) work via public channels

---

## 3. AI Summary & AI Suggestion Settings

### Overview

Two AI-powered features can be independently toggled per company:

- **AI Suggestions** — Generates draft reply suggestions for agents
- **AI Summary** — Generates a ticket summary (Issue / Progress / Next Step)

### Settings Model

**File:** `app/Models/CompanyAiSettings.php`

Key fields:
| Field | Type | Default | Purpose |
|---|---|---|---|
| `ai_suggestions_enabled` | bool | false | Enables AI reply suggestions on ticket details |
| `ai_summary_enabled` | bool | false | Enables AI ticket summary on ticket details |
| `ai_chatbot_enabled` | bool | false | Enables the AI chatbot widget (separate feature) |
| `ai_model` | string | `gemini-2.5-flash` | Which AI model to use |

The `resolveProvider()` method maps the model string to the correct provider enum (OpenAI, Anthropic, or Gemini).

### Settings Page

**File:** `app/Livewire/Settings/AiCopilot.php`

Admins toggle these settings from the AI Copilot settings page:

- Checkbox for `ai_suggestions_enabled` → persisted to DB on save
- Checkbox for `ai_summary_enabled` → persisted to DB on save
- Dropdown for `ai_model` → shows available models with status indicators
    - Available models: Gemini 2.5 Flash, Gemini 2.5 Pro, GPT-4o Mini, GPT-4o, Claude Sonnet 4
    - Each model is only selectable if its provider API key is configured in `config/ai.php`

### How AI Suggestions Work

When an agent opens a ticket in `app/Livewire/Tickets/TicketDetails.php`:

1. **Button visibility:** The "AI Suggest" button in the reply form only appears if `$aiSuggestionsEnabled` is true (checked in the blade component `resources/views/components/app/tickets/reply-form.blade.php` via `@if ($aiSuggestionsEnabled)`).

2. **Agent clicks "AI Suggest"** → `startAiSuggestion()`:
    - Checks `$this->aiSettings->ai_suggestions_enabled` — if disabled, shows error toast and returns
    - Checks ticket is not closed
    - Sets loading state, then calls `generateAiSuggestion()` via JS

3. **`generateAiSuggestion()`** builds context prompt:
    - Company name, ticket category, priority, customer name
    - Original ticket description
    - Full conversation history (all replies)
    - Tone instruction (friendly / formal / professional)
    - Sends to `SupportReplyAgent` with the configured provider & model

4. **Result displayed** in an expandable panel with options to:
    - Use the suggestion (loads into reply editor)
    - Regenerate with a different tone
    - Dismiss

5. **Logging:** Every generate/regenerate/use/dismiss action is logged in `ai_suggestion_logs` table.

### How AI Summary Works

1. **Agent clicks "Summarize"** → `generateAiSummary()`:
    - Checks `$this->aiSettings->ai_summary_enabled` — if disabled, shows error toast, sets `showSummary = false`, returns
    - Loads all replies with user relationships
    - Builds prompt asking for exactly three points:
        - **Issue:** What is the customer asking about?
        - **Progress:** What has been tried or discussed?
        - **Next Step:** What needs to happen next?
    - Sends to `SupportReplyAgent` with configured provider & model

2. **Result displayed** in a collapsible card with:
    - Character-by-character typing animation (Alpine.js)
    - Parsed sections (Issue / Progress / Next Step) with labels
    - Regenerate button

3. **Error handling:** Both features catch rate limit errors specifically and show a targeted "rate limit reached" message vs. generic failure messages.

### Toggle Flow Summary

```
Admin toggles "AI Suggestions" ON in Settings
   │
   ▼
CompanyAiSettings.ai_suggestions_enabled = true (saved to DB)
   │
   ▼
Agent opens any ticket → TicketDetails.php loads aiSettings computed property
   │
   ▼
Blade view checks @if ($aiSuggestionsEnabled) → shows "AI Suggest" button
   │
   ▼
Agent clicks → startAiSuggestion() → double-checks setting → generates suggestion

(Same pattern for AI Summary with ai_summary_enabled)
```

If an admin disables the feature while an agent has the ticket open, the next call to `startAiSuggestion()` or `generateAiSummary()` re-reads from DB and shows an error toast.

---

## 4. Embeddable Widgets (Iframe & Script Embeds)

### Overview

The system provides **three embeddable widgets** that companies can add to their external websites:

| Widget                    | Embed Method                        | Purpose                 |
| ------------------------- | ----------------------------------- | ----------------------- |
| **Form Widget**           | `<iframe>`                          | Ticket submission form  |
| **AI Chatbot Widget**     | `<script>` (creates iframe)         | AI-powered chat support |
| **Knowledge Base Widget** | `<script>` (creates floating panel) | Searchable KB articles  |

### Widget 1: Form Widget (Iframe Embed)

**How the embed code is generated:**

The `WidgetSetting` model (`app/Models/WidgetSetting.php`) has a computed attribute:

```php
public function getIframeCodeAttribute(): string
{
    return '<iframe src="' . $this->widget_url . '" width="100%" height="700" frameborder="0" style="border: none; border-radius: 8px;"></iframe>';
}
```

The `widget_url` is: `https://{company-slug}.{domain}/widget/{widget_key}`

**How the widget key works:**

- When a `WidgetSetting` record is created, a unique 32-character random `widget_key` is auto-generated via `Str::random(32)`
- This key is used in the URL to identify which company's widget to load
- The key can be regenerated from settings if needed

**Route chain:**

```
External site loads <iframe src="https://acme.helpdesk.com/widget/abc123...">
    │
    ▼
routes/settings.php: GET /{company}/widget/{key}
    │
    ▼
WidgetController::show($company, $key)
    │
    ├── Looks up WidgetSetting by key + company
    ├── Checks is_active flag
    ├── Loads company, categories
    │
    ▼
returns view('portal.form') — full HTML page rendered inside the iframe
    │
    ▼
Customer fills form → POST /{company}/widget/{key}/submit
    │
    ├── Creates Customer record (or finds existing by email)
    ├── Creates Ticket with company defaults from WidgetSetting
    ├── Sends verification email (TicketVerified mail)
    ├── Notifies all admins via TicketSubmitted notification
    │
    ▼
Customer gets tracking link via email → can track/reply via portal/track page
```

**Configurable settings** (via `app/Livewire/Channels/Channels.php` or similar settings page):

- Theme mode (dark/light)
- Form title & welcome message
- Success message after submission
- Require phone toggle
- Show category toggle
- Default assigned agent
- Default status & priority

---

### Widget 2: AI Chatbot Widget (Script Embed)

**Embed method:** A `<script>` tag that dynamically creates an iframe.

**How the embed code is generated:**

`app/Livewire/Channels/AiChatbotWidget.php` provides computed properties:

- `chatbotScriptSrc` → `https://{company}.{domain}/chatbot-widget/{widget_key}/embed.js?v={filemtime}`
- `chatbotScriptTag` → `<script src="..." defer></script>`

The `?v={filemtime}` cache-buster uses the file modification time to ensure browsers load updated scripts.

**Route chain:**

```
External site loads <script src="https://acme.helpdesk.com/chatbot-widget/abc123/embed.js">
    │
    ▼
routes/settings.php: GET /{company}/chatbot-widget/{key}/embed.js
    │
    ▼
ChatbotWidgetController::snippet($company, $key)
    │
    ├── Checks ai_chatbot_enabled in CompanyAiSettings → returns 404 if disabled
    ├── Retrieves WidgetSetting by key
    ├── Builds widget URL: https://{company}.{domain}/chatbot-widget/{key}
    │
    ▼
returns view('chatbot.widget-js') with Content-Type: application/javascript
```

**What the JavaScript does** (`resources/views/chatbot/widget-js.blade.php`):

```
1. Creates a fixed-position <div> container:
   - Position: bottom-right corner (24px offset)
   - Size: 400px × 600px
   - z-index: 2147483647 (maximum)
   - Responsive: shrinks to full screen on mobile (≤480px)

2. Creates an <iframe> inside it:
   - src = the chatbot widget URL (a full Livewire page)
   - allow = "clipboard-write"
   - loading = "lazy"
   - border-radius: 12px

3. Prevents duplicate injection:
   - Checks for existing element by ID before creating
```

**Chatbot iframe content:**

```
GET /{company}/chatbot-widget/{key}
    │
    ▼
ChatbotWidgetController::show($company, $key)
    │
    ▼
returns view('chatbot.widget') — renders the Livewire AiChatWidget component
    │
    ▼
Full AI chat interface with:
    - Greeting message from settings
    - Message input
    - AI-powered responses (searches KB articles for answers)
    - Escalation button (creates a ticket)
```

**Message processing:**

```
Customer sends message → POST /{company}/chatbot-widget/{key}/message (throttled: 30/min)
    │
    ▼
ChatbotWidgetController::message()
    │
    ├── Searches company KB articles for relevant answers
    ├── Sends context to AI agent (HelpdeskAgent) with company-scoped KB content
    ├── Returns AI response
    │
    ▼
Displayed in chat UI — if AI can't answer, shows escalation option
```

**Enable/disable:** Controlled by `ai_chatbot_enabled` in `CompanyAiSettings`. The toggle in `AiChatbotWidget.php` uses `wire:model.live` so changes take effect immediately. When disabled, the embed.js route returns 404, so the script tag on external sites silently does nothing.

---

### Widget 3: Knowledge Base Widget (Script Embed)

**Embed method:** A `<script>` tag that creates a floating search button + panel (no iframe — it's pure DOM injection).

**How the embed code is generated:**

`app/Livewire/Channels/KbWidget.php` provides a `scriptTag` computed property:

```html
<script
    src="https://{company}.{domain}/kb/widget.js?v={filemtime}"
    defer
    data-default-link-mode="portal"
    data-article-base-url=""
    data-open-in-new-tab="true"
></script>
```

**Configurable `data-` attributes:**
| Attribute | Values | Purpose |
|---|---|---|
| `data-default-link-mode` | `portal` or `custom` | Where article links point to |
| `data-article-base-url` | URL string | Custom base URL for article links (when mode = custom) |
| `data-open-in-new-tab` | `true` / `false` | Whether article links open in a new tab |

**Route chain:**

```
External site loads <script src="https://acme.helpdesk.com/kb/widget.js">
    │
    ▼
routes/web.php: GET /{company}/kb/widget.js
    │
    ▼
KbWidgetController::snippet($company)
    │
    ├── Retrieves company logo URL, portal URL, API URL
    │
    ▼
returns view('kb.widget-js') with Content-Type: application/javascript
```

**What the JavaScript does** (`resources/views/kb/widget-js.blade.php`):

```
1. Reads configuration from script tag data- attributes

2. Injects CSS styles:
   - Floating button: 60px round teal (#0d9488) button, bottom-right corner
   - Search panel: 350px wide, white background, 12px border-radius
   - Responsive adjustments for mobile

3. Creates DOM elements:
   - Floating button with question-mark SVG icon
   - Panel with header (company logo + name)
   - Search input field
   - Results list
   - Footer with link to full KB portal

4. Search behavior:
   - Debounced input (400ms)
   - Minimum 3 characters to trigger search
   - Fetches from: GET /kb/search?q={query} (existing KB API)
   - Displays article titles + excerpts as clickable links
   - Links point to portal or custom URL based on data-default-link-mode

5. Toggle: Click floating button → panel slides open/closed
```

**Key difference from other widgets:** The KB widget does NOT use an iframe. It injects native HTML/CSS/JS directly into the host page's DOM. This means:

- It inherits no styles from the host page (all styles are inline/scoped)
- It makes fetch requests directly to the helpdesk API
- Article links navigate the host page (or open new tabs)

---

### Widget Comparison Summary

| Aspect                 | Form Widget                      | AI Chatbot Widget                         | KB Widget                         |
| ---------------------- | -------------------------------- | ----------------------------------------- | --------------------------------- |
| **Embed code**         | `<iframe>` tag                   | `<script>` → creates `<iframe>`           | `<script>` → creates DOM elements |
| **Rendering**          | Full HTML page in iframe         | Livewire component in iframe              | Vanilla JS + injected DOM         |
| **Route for embed**    | N/A (direct iframe URL)          | `/chatbot-widget/{key}/embed.js`          | `/kb/widget.js`                   |
| **Route for content**  | `/widget/{key}`                  | `/chatbot-widget/{key}`                   | Direct API calls (`/kb/search`)   |
| **Unique key**         | `widget_key` (32 chars)          | Same `widget_key`                         | None (global per company)         |
| **Position**           | Wherever iframe is placed        | Fixed bottom-right                        | Fixed bottom-right                |
| **Size**               | 100% × 700px (parent controlled) | 400px × 600px (auto-positioned)           | 350px panel (auto-positioned)     |
| **Authentication**     | None required                    | None required                             | None required                     |
| **Enable/disable**     | `is_active` on WidgetSetting     | `ai_chatbot_enabled` on CompanyAiSettings | Always enabled                    |
| **Settings component** | `Channels/Channels.php`          | `Channels/AiChatbotWidget.php`            | `Channels/KbWidget.php`           |
| **Cache headers**      | None                             | 300s on production                        | 300s on production                |
