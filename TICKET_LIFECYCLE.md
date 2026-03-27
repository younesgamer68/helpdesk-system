# Ticket Lifecycle — Complete Flow (A to Z)

---

## Table of Contents

1. [Ticket Statuses](#1-ticket-statuses)
2. [Ticket Creation](#2-ticket-creation)
3. [Verification Flow](#3-verification-flow)
4. [Ticket Assignment](#4-ticket-assignment)
5. [SLA System](#5-sla-system)
6. [Replies & Internal Notes](#6-replies--internal-notes)
7. [Status Transitions](#7-status-transitions)
8. [Resolution](#8-resolution)
9. [Auto-Close Flow](#9-auto-close-flow)
10. [Manual Close](#10-manual-close)
11. [Reopening](#11-reopening)
12. [Follow-Up / Linked Tickets](#12-follow-up--linked-tickets)
13. [Priority System](#13-priority-system)
14. [Categories](#14-categories)
15. [Ticket Sources](#15-ticket-sources)
16. [Ticket Deletion](#16-ticket-deletion)
17. [Audit Logging](#17-audit-logging)
18. [Emails Across the Lifecycle](#18-emails-across-the-lifecycle)
19. [Automation Engine](#19-automation-engine)
20. [Full Lifecycle Diagram](#20-full-lifecycle-diagram)

---

## 1. Ticket Statuses

There are **5 statuses** a ticket can be in:

| Status        | Description                                                                   |
| ------------- | ----------------------------------------------------------------------------- |
| `open`        | Ticket created/verified — awaiting first agent action (default initial state) |
| `in_progress` | Agent is actively working on the ticket                                       |
| `pending`     | Waiting on the customer — set when an agent replies                           |
| `resolved`    | Agent has marked the issue as resolved; awaiting auto-close                   |
| `closed`      | Permanently closed (manual or auto-close)                                     |

**Status rules (strict):**

1. **Create / Verified ticket** → `open`
2. **Agent starts work (first action)** → `open` → `in_progress`
3. **Agent replies** → `pending` (ball is in customer's court)
4. **Customer replies:**
    - if `pending` → `in_progress` (ball is back in agent's court)
    - if `resolved` (within reopen window) → `open`
5. **Agent resolves** → `resolved`
6. **Close (manual or auto)** → `closed`

**Never:** use `pending` as the default state; skip `in_progress` when the agent starts working.

**Status flow summary:**

```
open → in_progress → pending ←→ in_progress → resolved → closed
                        ↑              │
                   customer reply      └── agent resolve
                   if resolved ↘
                               open (reopen)
```

---

## 2. Ticket Creation

Tickets can be created through **4 paths**:

### 2.1 Widget Form (Customer)

**Entry:** Customer submits the form widget embedded on external site.

**Controller:** `WidgetController::submit()`

**Flow:**

1. Validates form input (name, email, subject, description)
2. Creates or updates `Customer` record (matched by email + company)
3. Checks `require_client_verification` company setting:
    - **If true:** Creates ticket with `verified = false` + generates `verification_token` → sends `TicketVerification` email
    - **If false:** Creates ticket with `verified = true` + generates `tracking_token` → sends `TicketVerified` email
4. Notifies admins via `TicketSubmitted` notification
5. If unassigned after automation, notifies admins via `TicketUnassigned`

**Source:** `widget`

### 2.2 Agent "Add Ticket" Button

**Entry:** Agent/Admin clicks "Add Ticket" in the tickets dashboard.

**Component:** `TicketsTable::createTicket()`

**Flow:**

1. Validates form input
2. Generates unique ticket number (`TKT-XXXXXX`)
3. Creates or updates `Customer` record
4. Creates ticket with `verified = true` (auto-verified) + generates `tracking_token`
5. Sends `TicketVerified` email to the customer with tracking link
6. `TicketObserver::created()` fires → runs automation engine (`processNewTicket`)
7. If still unassigned after automation, `TicketAssignmentService` assigns as fallback

**Source:** `agent`

### 2.3 Chatbot Escalation

**Entry:** Customer in AI chatbot clicks "Talk to a human" / escalation button.

**Flow:** Redirects customer to the widget form. The chatbot itself does not directly create tickets — it redirects to the widget URL so the standard widget submission flow handles creation.

**Source:** `widget` (created through widget form)

### 2.4 Follow-Up Ticket (Closed Ticket Reply)

**Entry:** Customer replies to a closed ticket within the `linked_ticket_days` window.

**Component:** `TicketConversation::createLinkedTicket()`

**Flow:**

1. Creates a new ticket with subject `Follow-up: {original subject}`
2. Links to parent ticket via `parent_ticket_id`
3. Inherits company, customer, assigned agent, category, priority from parent
4. Auto-verified with new tracking token
5. Sends `TicketVerified` email with new tracking link
6. Notifies assigned agent via `ClientReplied` notification

**Source:** Inherited from parent ticket's source

---

## 3. Verification Flow

Applies only to **widget-submitted tickets** when `require_client_verification` is enabled on the company.

```
Customer submits widget form
    │
    ▼
Ticket created: verified=false, verification_token=random(64)
    │
    ▼
TicketVerification email sent to customer
    │
    ▼
Customer clicks verification link in email
    │
    ▼
WidgetController::verify()
    │
    ├── Sets verified=true, clears verification_token
    ├── Generates tracking_token=random(64)
    ├── Sends TicketVerified email (with tracking link)
    │
    ▼
TicketObserver::updated() fires (verified changed to true)
    │
    ├── AutomationEngine::processNewTicket() runs all matching rules
    ├── Fallback: TicketAssignmentService::assignTicket() if still unassigned
    │
    ▼
Ticket is now live in the system
```

**Unverified tickets** do NOT trigger automation, assignment, or SLA timers. They remain dormant until verified.

---

## 4. Ticket Assignment

### 4.1 Automatic Assignment (TicketAssignmentService)

Runs as a fallback after automation rules. Uses a smart workload-balanced algorithm:

**Priority order:**

1. **Specialists** — Operators with a category specialty matching the ticket's category (or parent category), lowest workload first
2. **Generalists** — Operators with no specialties, lowest workload first
3. **Any available** — Any online, available operator under max load
4. **Unassigned** — If no one qualifies, ticket stays unassigned → `TicketUnassigned` notification to admins

**Constraints checked:**

- Must be in same `company_id`
- Must have `role = operator`
- Must be `available` (not marked unavailable)
- Must be `online`
- Must have `assigned_tickets_count < max_tickets_per_agent` (default 20, configurable per tenant)

**Tiebreaker:** `last_assigned_at` ascending (round-robin fairness)

### 4.2 Automation Assignment

`AssignmentRule` in the automation engine can assign tickets based on conditions (category, priority, keywords). Runs before the fallback service.

### 4.3 Manual Assignment

Admins can manually assign/reassign tickets from `TicketDetails::assign()`:

- Decrements old agent's `assigned_tickets_count`
- Increments new agent's count + updates `last_assigned_at`
- Sends `TicketAssigned` to new agent
- Sends `TicketReassigned` to old agent
- If new agent is on multiple teams, shows team picker modal

### 4.4 Team Assignment

`TicketAssignmentService::assignToTeam()` — assigns to best available team member:

1. Prefer team members with matching category specialty
2. Fall back to any available team member
3. If no team members available, removes team and falls back to global assignment

### 4.5 Self-Assignment

Operators can self-assign unassigned tickets. Logged as `self_assigned`.

---

## 5. SLA System

### 5.1 SLA Policy Configuration

Each company has one `SlaPolicy` with these settings:

| Setting              | Default | Description                                                   |
| -------------------- | ------- | ------------------------------------------------------------- |
| `is_enabled`         | false   | Master toggle for SLA monitoring                              |
| `urgent_minutes`     | 30      | Response time for urgent tickets                              |
| `high_minutes`       | 120     | Response time for high priority (2 hours)                     |
| `medium_minutes`     | 480     | Response time for medium priority (8 hours)                   |
| `low_minutes`        | 1440    | Response time for low priority (24 hours)                     |
| `warning_hours`      | 24      | Hours before auto-close to send warning email                 |
| `auto_close_hours`   | 48      | Hours after resolution to auto-close ticket                   |
| `reopen_hours`       | 48      | Window (hours) after resolution for customer to reopen        |
| `linked_ticket_days` | 7       | Window (days) after closure for customer to create follow-up  |
| `soft_delete_days`   | 30      | Days before soft-deleted tickets are eligible for hard delete |
| `hard_delete_days`   | 90      | Days before hard deletion                                     |

### 5.2 Due Time Calculation

Handled by `TicketObserver::creating()`:

1. Reads `SlaPolicy` for the ticket's company
2. If SLA is disabled (`is_enabled = false`), `due_time = null`
3. Maps priority to minutes using company policy (or defaults above)
4. Calculates: `now(company_timezone) + minutes → converted to UTC`
5. Stored in `due_time` column

**Recalculated** when priority changes via `TicketObserver::updating()`:

- New due time computed from current time + new priority's minutes
- If ticket was `breached` but new due time is in the future, resets to `on_time`

### 5.3 SLA Status Tracking

Three SLA statuses tracked in `sla_status` column:

| Status     | Condition                           |
| ---------- | ----------------------------------- |
| `on_time`  | More than 25% of SLA time remaining |
| `at_risk`  | 25% or less of SLA time remaining   |
| `breached` | Past due time                       |

**Checked by** `helpdesk:check-sla-breaches` command (runs periodically):

- For each open ticket with a `due_time`, calculates current SLA status
- Transitions: `on_time` → `at_risk` → `breached`
- On **first breach** (transition to breached):
    - Notifies assigned operator via `SlaBreached` notification
    - Runs `TYPE_SLA_BREACH` automation rules
    - Fallback: notifies admins if no automation rule handled it

---

## 6. Replies & Internal Notes

### 6.1 Customer Replies

**Component:** `TicketConversation::submitReply()`

- Creates `TicketReply` with `user_id = null`, `is_internal = false`
- Supports file attachments (max 2 files × 2MB)
- Notifies assigned agent via `ClientReplied` notification
- Broadcasts `NewTicketReply` event for real-time update
- Status transitions:
    - `pending` → `in_progress` (customer has responded; agent's turn)
    - `resolved` → `open` (within reopen window; clears `resolved_at` + `warning_sent_at`)
    - `open` / `in_progress` → unchanged

### 6.2 Agent Replies

**Component:** `TicketDetails::addReply()`

- Creates `TicketReply` with `user_id = agent_id`, `is_internal = false`
- Supports HTML content (sanitized via Purifier)
- If ticket is verified and has tracking token: sends `AgentRepliedToTicket` email to customer
- Sets ticket status to `pending` — waiting on customer to respond (unless `keepOpen` flag is set)
- Notifies assigned agent if the replier is a different admin
- Broadcasts `NewTicketReply` event
- Logged as `reply_added`

### 6.3 Internal Notes

**Component:** `TicketDetails::addInternalNote()`

- Creates `TicketReply` with `is_internal = true` (hidden from customers)
- Supports @mentions — creates `TicketMention` records and sends `UserMentioned` notification
- Notifies admins and assigned agent via `InternalNoteAdded` notification
- Broadcasts `NewTicketReply` for real-time visibility to other agents
- Does NOT change ticket status

---

## 7. Status Transitions

### Agent-Initiated Status Changes

**Component:** `TicketDetails::changeStatus($status)`

- Validates status is one of: `pending`, `open`, `in_progress`, `resolved`, `closed`
- Operators can only change status on tickets assigned to them
- Notifies assigned agent (if different from actor) via `TicketStatusChanged`
- Logged as `status_changed`

### Automatic Status Changes

| Trigger                   | Previous Status | New Status    | Where                               |
| ------------------------- | --------------- | ------------- | ----------------------------------- |
| Agent replies             | Any open status | `pending`     | `TicketDetails::addReply()`         |
| Customer replies          | `pending`       | `in_progress` | `TicketConversation::submitReply()` |
| Customer reopens          | `resolved`      | `open`        | `TicketConversation::submitReply()` |
| Agent takes ticket        | `open`          | `in_progress` | `TicketsTable::takeTicket()`        |
| Agent resolves            | Any             | `resolved`    | `TicketDetails::resolve()`          |
| Agent closes              | Any             | `closed`      | `TicketDetails::closeTicket()`      |
| Auto-close timer          | `resolved`      | `closed`      | `ProcessTicketLifecycle` command    |
| Ticket created / verified | —               | `open`        | Creation / `TicketObserver`         |

---

## 8. Resolution

**Component:** `TicketDetails::resolve()`

**Flow:**

1. Checks ticket is not already resolved
2. Sets `status = resolved`, `resolved_at = now()`
3. Notifies assigned agent (if different) via `TicketStatusChanged`
4. Ensures tracking token exists (generates one if missing)
5. Sends `TicketResolved` email to customer (queued)
6. Logged as `resolved`

**After resolution**, the auto-close timer begins (see section 9).

---

## 9. Auto-Close Flow

**Command:** `app:process-ticket-lifecycle`

Runs periodically (typically hourly via scheduler). For each company, applies the SLA policy's `warning_hours` and `auto_close_hours` settings.

### Phase 1: Send Closure Warnings

Finds resolved tickets where:

- `resolved_at` ≤ `now() - (auto_close_hours - warning_hours)`
- `resolved_at` > `now() - auto_close_hours`
- `warning_sent_at` is null (haven't sent warning yet)

**Action:**

- Ensures tracking token exists
- Sends `TicketClosedWarning` email to customer (includes remaining hours)
- Sets `warning_sent_at = now()` to prevent duplicate warnings

### Phase 2: Auto-Close Tickets

Finds resolved tickets where:

- `resolved_at` ≤ `now() - auto_close_hours`

**Action:**

- Ensures tracking token exists
- Sends `TicketClosed` email to customer with reason `auto_closed`
- Sets `status = closed`, `closed_at = now()`, `close_reason = auto_closed`
- Logged as `auto_closed`

### Timeline Example (default settings: warning=24h, auto_close=48h)

```
 Hour 0          Hour 24          Hour 48
   │                │                │
   ▼                ▼                ▼
Resolved    Warning email     Auto-close
                sent        + TicketClosed email
```

---

## 10. Manual Close

**Component:** `TicketDetails::closeTicket()`

**Flow:**

1. Sets `status = closed`, `closed_at = now()`, `close_reason = manual`
2. Notifies assigned agent (if different) via `TicketStatusChanged`
3. Ensures tracking token exists
4. Sends `TicketClosed` email to customer with reason `manual`
5. Logged as `manually_closed`

**Close reasons:** `manual` (agent action) or `auto_closed` (lifecycle command)

---

## 11. Reopening

When a customer replies to a **resolved** ticket:

**Component:** `TicketConversation::submitReply()`

**Conditions:**

- Ticket status must be `resolved`
- Must be within `reopen_hours` window (default 48 hours from `resolved_at`)

**Flow:**

1. If past the reopen window → error: "The reopen window has passed. Please submit a new support request."
2. If within window → creates reply, then:
    - Sets `status = open`
    - Clears `resolved_at = null`
    - Clears `warning_sent_at = null` (resets auto-close timer)

The ticket returns to the normal active lifecycle with a fresh auto-close timer.

---

## 12. Follow-Up / Linked Tickets

When a customer replies to a **closed** ticket:

**Component:** `TicketConversation::createLinkedTicket()`

**Conditions:**

- Ticket status must be `closed`
- Must be within `linked_ticket_days` window (default 7 days from `closed_at`)

**Flow:**

1. If past the window → error: "This ticket is permanently closed."
2. First reply attempt → shows confirmation prompt ("This will create a new follow-up ticket")
3. On confirmation → creates new ticket:
    - Subject: `Follow-up: {parent subject}`
    - `parent_ticket_id` links to original ticket
    - Inherits: company, customer, assigned agent, category, priority
    - `status = open`, `verified = true`
    - New `tracking_token` generated
4. Sends `TicketVerified` email with new tracking link
5. Notifies assigned agent via `ClientReplied`

The new ticket has its own lifecycle, independent from the parent.

---

## 13. Priority System

**4 levels:** `low`, `medium`, `high`, `urgent`

**Component:** `TicketDetails::changePriority($priority)`

**Flow:**

1. Operators can only change priority on their assigned tickets
2. Updates `priority` column
3. `TicketObserver::updating()` fires → recalculates `due_time` based on new priority
4. If previously `breached` but new due time is future → resets `sla_status = on_time`
5. Notifies assigned agent via `TicketPriorityChanged` notification
6. Logged as `priority_changed`

**Default SLA times per priority:**

| Priority | Default Response Time |
| -------- | --------------------- |
| Urgent   | 30 minutes            |
| High     | 2 hours               |
| Medium   | 8 hours               |
| Low      | 24 hours              |

---

## 14. Categories

**Model:** `TicketCategory` — hierarchical (parent/child via `parent_id`)

**Usage in lifecycle:**

- Set at ticket creation (optional, depends on widget `show_category` setting)
- Determines specialist routing in `TicketAssignmentService`
- Can be used as conditions in automation rules

**Operator specialization:** Operators can be assigned category specialties. The assignment service prefers specialists whose specialty matches the ticket category (or its parent category).

---

## 15. Ticket Sources

The `source` field tracks where a ticket was created:

| Source      | Origin                                                        |
| ----------- | ------------------------------------------------------------- |
| `widget`    | Customer submitted via form widget                            |
| `agent`     | Agent/Admin created via "Add Ticket" button                   |
| `chatbot`   | (Currently redirects to widget, so actual source is `widget`) |
| `follow_up` | Follow-up ticket inherits parent's source                     |

---

## 16. Ticket Deletion

**Soft Delete:** Tickets use `SoftDeletes` trait. `$ticket->delete()` sets `deleted_at` timestamp.

**Who can delete:** Only admins (`role = admin`)

**Side effects on delete (`TicketObserver::deleted()`):**

- If ticket was open (not resolved/closed) and assigned, decrements the operator's `assigned_tickets_count`

**Restore (`TicketObserver::restored()`):**

- Re-increments the operator's `assigned_tickets_count` if ticket was open

**SLA policy cleanup settings:**

- `soft_delete_days` (default 30) — days before soft-deleted tickets become eligible for permanent deletion
- `hard_delete_days` (default 90) — days before hard deletion

---

## 17. Audit Logging

**Model:** `TicketLog`

Every significant action on a ticket is recorded with:

- `ticket_id` — which ticket
- `user_id` — who performed the action (null for system actions)
- `action` — action type string
- `description` — human-readable description
- `company_id` — for multi-tenant scoping

### Action Types

| Action             | Trigger                                  |
| ------------------ | ---------------------------------------- |
| `status_changed`   | Agent changes status                     |
| `resolved`         | Agent resolves ticket                    |
| `manually_closed`  | Agent manually closes ticket             |
| `auto_closed`      | `ProcessTicketLifecycle` command         |
| `assigned`         | Ticket assigned to agent (or unassigned) |
| `team_assigned`    | Ticket assigned to team                  |
| `self_assigned`    | Operator self-assigns                    |
| `priority_changed` | Priority level changed                   |
| `reply_added`      | Agent adds a reply                       |

---

## 18. Emails Across the Lifecycle

Complete list of emails sent during a ticket's lifetime:

| Email Class                  | When Sent                                                             | Recipient | Method                |
| ---------------------------- | --------------------------------------------------------------------- | --------- | --------------------- |
| `TicketVerification`         | Widget submission (verification required)                             | Customer  | `send()`              |
| `TicketVerified`             | After verification / auto-verified widget / agent-created / follow-up | Customer  | `send()` or `queue()` |
| `AutoReplyMail`              | Automation rule fires on verified new ticket                          | Customer  | `queue()`             |
| `AgentRepliedToTicket`       | Agent replies to verified ticket with tracking token                  | Customer  | `send()`              |
| `TicketResolved`             | Agent resolves ticket                                                 | Customer  | `queue()`             |
| `TicketClosedWarning`        | `ProcessTicketLifecycle` warning phase                                | Customer  | `send()`              |
| `TicketClosed`               | Manual close or auto-close                                            | Customer  | `send()`              |
| `EscalationNotificationMail` | Escalation automation rule fires                                      | Admin     | `queue()`             |

### Notification Classes (Database + Broadcast)

| Notification            | When                        | Recipients                 |
| ----------------------- | --------------------------- | -------------------------- |
| `TicketSubmitted`       | New widget ticket           | Admins                     |
| `TicketAssigned`        | Ticket assigned to operator | Assigned operator          |
| `TicketReassigned`      | Ticket reassigned away      | Previous operator          |
| `TicketUnassigned`      | Auto-assignment failed      | Admins                     |
| `TicketStatusChanged`   | Status transition           | Assigned operator          |
| `TicketPriorityChanged` | Priority changed            | Assigned operator          |
| `ClientReplied`         | Customer replies            | Assigned operator          |
| `InternalNoteAdded`     | Internal note created       | Admins + assigned agent    |
| `UserMentioned`         | @mentioned in internal note | Mentioned user             |
| `SlaBreached`           | SLA deadline passed         | Assigned operator + admins |
| `TeamAssigned`          | User added to a team        | User                       |

---

## 19. Automation Engine

The automation engine processes rules on ticket creation/verification. Rules are evaluated in priority order (lower number = higher priority).

### Rule Types

| Type         | Purpose                                           | Trigger                               |
| ------------ | ------------------------------------------------- | ------------------------------------- |
| `assignment` | Auto-assign to specific agent based on conditions | New/verified ticket                   |
| `priority`   | Change priority based on conditions               | New/verified ticket                   |
| `auto_reply` | Send auto-reply email to customer                 | New/verified ticket                   |
| `escalation` | Escalate idle tickets                             | Scheduled (not on creation)           |
| `sla_breach` | Notify/act on SLA breaches                        | `helpdesk:check-sla-breaches` command |

### Processing Flow

```
Ticket created/verified
    │
    ▼
TicketObserver::created() / updated()
    │
    ▼
AutomationEngine::processNewTicket()
    │
    ├── Skips escalation rules (scheduler only)
    ├── Skips sla_breach rules (breach checker only)
    │
    ▼
For each active rule (ordered by priority):
    │
    ├── handler.evaluate(rule, ticket) → checks conditions
    │   └── If false → skip
    │
    └── handler.apply(rule, ticket) → executes actions
        └── Records execution (count + timestamp)
```

Multiple rules CAN fire on the same ticket. For example: an assignment rule at priority 0, a priority rule at priority 1, and an auto-reply rule at priority 2 would all evaluate and potentially fire.

---

## 20. Full Lifecycle Diagram

```
                                 ┌─────────────────┐
                                 │  TICKET CREATION │
                                 └────────┬────────┘
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
              Widget Form           Agent Button         Follow-up Reply
                    │                     │                     │
                    ▼                     │                     │
           ┌───────────────┐              │                     │
           │ Verification   │              │                     │
           │ Required?      │              │                     │
           └───┬───────┬───┘              │                     │
               │       │                  │                     │
              Yes      No                 │                     │
               │       │                  │                     │
               ▼       ▼                  ▼                     ▼
         Unverified  Auto-verified    Auto-verified        Auto-verified
         (dormant)   + tracking email + tracking email     + tracking email
               │       │                  │                     │
               ▼       └──────────────────┼─────────────────────┘
         Email verify                     │
               │                          ▼
               └──────────► ┌──────────────────────────┐
                            │  AUTOMATION ENGINE RUNS   │
                            │  (assignment, priority,   │
                            │   auto-reply rules)       │
                            └────────────┬─────────────┘
                                         │
                                         ▼
                            ┌──────────────────────────┐
                            │  FALLBACK ASSIGNMENT      │
                            │  (if still unassigned)    │
                            └────────────┬─────────────┘
                                         │
                                         ▼
                            ┌──────────────────────────┐
                            │  SLA TIMER STARTS         │
                            │  due_time set by priority │
                            └────────────┬─────────────┘
                                         │
                                         ▼
                ┌────────────────────────────────────────────────┐
                │              ACTIVE LIFECYCLE                   │
                │                                                │
                │  pending ←→ open ←→ in_progress                │
                │     ↕           ↕        ↕                     │
                │  Customer    Agent    Agent                    │
                │  replies     replies  works                    │
                │                                                │
                │  SLA monitoring: on_time → at_risk → breached  │
                └───────────────────────┬────────────────────────┘
                                        │
                          ┌─────────────┼─────────────┐
                          │             │             │
                     Agent resolves   Agent closes  SLA breaches
                          │             │             │
                          ▼             │             ▼
                   ┌──────────┐         │      Notifications
                   │ RESOLVED  │         │      + automation
                   │           │         │
                   │ Emails:   │         │
                   │ Resolved  │         │
                   └─────┬─────┘         │
                         │               │
              ┌──────────┼──────────┐    │
              │          │          │    │
         Customer    Warning     Auto    │
         replies    email at   close at  │
         (reopen)   24h mark   48h mark  │
              │          │          │    │
              ▼          │          ▼    ▼
         Back to         │    ┌──────────┐
         OPEN            └───►│ CLOSED    │
         (timer reset)        │           │
                              │ Reasons:  │
                              │ manual /  │
                              │ auto_close│
                              └─────┬─────┘
                                    │
                              Customer replies
                              within 7 days?
                                    │
                               ┌────┼────┐
                               │         │
                              Yes        No
                               │         │
                               ▼         ▼
                          Follow-up    "Permanently
                          ticket       closed"
                          created      error
```

---

## Key Database Fields on Ticket Model

| Field                | Type     | Purpose                                  |
| -------------------- | -------- | ---------------------------------------- |
| `ticket_number`      | string   | Unique identifier (TKT-XXXXXX)           |
| `company_id`         | FK       | Multi-tenant company                     |
| `customer_id`        | FK       | Link to Customer record                  |
| `assigned_to`        | FK       | Assigned operator (nullable)             |
| `team_id`            | FK       | Assigned team (nullable)                 |
| `category_id`        | FK       | Ticket category (nullable)               |
| `parent_ticket_id`   | FK       | Follow-up link to parent (nullable)      |
| `subject`            | string   | Ticket subject                           |
| `description`        | text     | Initial ticket description               |
| `status`             | enum     | pending/open/in_progress/resolved/closed |
| `priority`           | enum     | low/medium/high/urgent                   |
| `source`             | string   | widget/agent                             |
| `verified`           | boolean  | Whether ticket is verified               |
| `verification_token` | string   | Email verification token (nullable)      |
| `tracking_token`     | string   | Customer portal access token (nullable)  |
| `due_time`           | datetime | SLA deadline                             |
| `sla_status`         | string   | on_time/at_risk/breached                 |
| `resolved_at`        | datetime | When resolved (nullable)                 |
| `closed_at`          | datetime | When closed (nullable)                   |
| `close_reason`       | string   | manual/auto_closed (nullable)            |
| `warning_sent_at`    | datetime | When closure warning was sent (nullable) |
| `draft_reply`        | text     | Saved draft reply (nullable)             |
| `draft_summary`      | text     | Saved AI summary draft (nullable)        |
| `draft_user_id`      | FK       | Who owns the draft (nullable)            |
| `deleted_at`         | datetime | Soft delete timestamp (nullable)         |
