# Helpdesk Automation System — Complete Technical Documentation

This document explains **every aspect** of the automation engine: what it is, how it works internally, what triggers it, the five rule types, conditions, actions, the database schema, the execution lifecycle, the SLA integration, the scheduled commands, the UI, and the fallback assignment system.

---

## Table of Contents

1. [High-Level Overview](#1-high-level-overview)
2. [Architecture & Key Files](#2-architecture--key-files)
3. [The Automation Engine](#3-the-automation-engine)
4. [The Rule Interface](#4-the-rule-interface)
5. [When Does Automation Run?](#5-when-does-automation-run)
6. [The Five Rule Types (In Depth)](#6-the-five-rule-types-in-depth)
    - 6.1 [Assignment Rule](#61-assignment-rule-type_assignment--assignment)
    - 6.2 [Priority Rule](#62-priority-rule-type_priority--priority)
    - 6.3 [Auto Reply Rule](#63-auto-reply-rule-type_auto_reply--auto_reply)
    - 6.4 [Escalation Rule](#64-escalation-rule-type_escalation--escalation)
    - 6.5 [SLA Breach Rule](#65-sla-breach-rule-type_sla_breach--sla_breach)
7. [Conditions & Actions — Complete Reference](#7-conditions--actions--complete-reference)
8. [Database Schema](#8-database-schema)
9. [Execution Count & Last Executed](#9-execution-count--last-executed)
10. [Priority Field on Rules](#10-priority-field-on-rules)
11. [The Fallback Assignment Service](#11-the-fallback-assignment-service)
12. [SLA System Integration](#12-sla-system-integration)
13. [Scheduled Commands](#13-scheduled-commands)
14. [The UI — Automation Tab](#14-the-ui--automation-tab)
15. [Complete Lifecycle Walkthrough](#15-complete-lifecycle-walkthrough)
16. [Sequence Diagrams](#16-sequence-diagrams)

---

## 1. High-Level Overview

The automation system allows each company to define **rules** that automatically perform actions on tickets without manual intervention. There are five types of rules:

| Type           | Purpose                                                          |
| -------------- | ---------------------------------------------------------------- |
| **Assignment** | Auto-assign tickets to specialists, teams, or specific operators |
| **Priority**   | Change ticket priority based on keywords or category             |
| **Auto Reply** | Send an automatic email response to the customer                 |
| **Escalation** | Escalate idle tickets (bump priority, reassign, notify admins)   |
| **SLA Breach** | React when a ticket's SLA deadline has been breached             |

Rules are **company-scoped** (each company has its own set), evaluated **in priority order** (lowest number first), and tracked with an **executions count** and **last executed timestamp**.

---

## 2. Architecture & Key Files

```
app/
├── Services/Automation/
│   ├── AutomationEngine.php          ← Central orchestrator
│   └── Rules/
│       ├── RuleInterface.php         ← Contract: evaluate() + apply()
│       ├── AssignmentRule.php         ← Handles type "assignment"
│       ├── PriorityRule.php           ← Handles type "priority"
│       ├── AutoReplyRule.php          ← Handles type "auto_reply"
│       ├── EscalationRule.php         ← Handles type "escalation"
│       └── SlaBreachRule.php          ← Handles type "sla_breach"
├── Models/
│   ├── AutomationRule.php            ← Eloquent model
│   └── SlaPolicy.php                 ← SLA deadline configuration
├── Observers/
│   └── TicketObserver.php            ← Triggers automation on ticket lifecycle
├── Console/Commands/
│   ├── CheckSlaBreaches.php          ← Scheduled: every minute
│   └── ProcessTicketEscalations.php  ← Scheduled: every 15 minutes
├── Livewire/Automation/
│   └── AutomationRulesTable.php      ← UI component for managing rules
├── Services/
│   └── TicketAssignmentService.php   ← Smart assignment logic (specialist → generalist → any)
└── Mail/
    ├── AutoReplyMail.php             ← Email sent by auto-reply rules
    └── EscalationNotificationMail.php← Email sent by escalation rules

database/migrations/
└── 2026_03_09_104729_create_automation_rules_table.php

resources/views/
├── livewire/automation/
│   └── automation-rules-table.blade.php  ← Rule management UI
└── app/
    └── automation.blade.php              ← Page wrapper

routes/
├── web.php          ← /automation, /automation/ticket-rules, /automation/assignment-rules
└── console.php      ← Scheduled task registration
```

---

## 3. The Automation Engine

**File:** `app/Services/Automation/AutomationEngine.php`

The `AutomationEngine` is the central brain. It has a map of rule type → handler class:

```php
protected array $ruleHandlers = [
    'assignment'  => AssignmentRule::class,
    'priority'    => PriorityRule::class,
    'auto_reply'  => AutoReplyRule::class,
    'escalation'  => EscalationRule::class,
    'sla_breach'  => SlaBreachRule::class,
];
```

### Key Methods

| Method                                              | What it does                                                                                                                                                                                                             |
| --------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `processNewTicket(Ticket $ticket)`                  | Fetches ALL active rules for the ticket's company (ordered by `priority` ASC). Iterates through each one, **skipping escalation rules** (those are scheduler-only). For each non-escalation rule, calls `executeRule()`. |
| `processEscalations(int $companyId)`                | Filters company rules to only escalation type. For each, calls `findIdleTickets()` to get qualifying tickets, then runs `executeRule()` on each.                                                                         |
| `executeRule(AutomationRule $rule, Ticket $ticket)` | Resolves the handler class, calls `evaluate()` — if true, calls `apply()` then `recordExecution()`. Logs success/failure. Returns bool.                                                                                  |
| `getRulesOfType(int $companyId, string $type)`      | Returns active rules of a specific type for a company (used by the SLA breach command).                                                                                                                                  |

### How `processNewTicket` works step-by-step:

1. Query: `AutomationRule::where('company_id', X)->active()->ordered()->get()`
2. Loop through ALL rules (sorted by `.priority` ascending — 0 first)
3. Skip any rule where `type === 'escalation'` (escalation is time-based, not event-based)
4. For each remaining rule, call `executeRule()`
5. **All matching rules fire** — it does NOT stop after the first match

> **Important:** Multiple rules CAN fire on the same ticket. If you have an Assignment rule at priority 0 AND a Priority rule at priority 1 AND an Auto Reply rule at priority 2, all three will evaluate and potentially fire.

---

## 4. The Rule Interface

**File:** `app/Services/Automation/Rules/RuleInterface.php`

Every rule handler implements this contract:

```php
interface RuleInterface
{
    public function evaluate(AutomationRule $rule, Ticket $ticket): bool;
    public function apply(AutomationRule $rule, Ticket $ticket): void;
}
```

- **`evaluate()`** — Checks if the rule's conditions match the ticket. Returns `true` if the rule should fire.
- **`apply()`** — Performs the rule's actions on the ticket (assign, change priority, send email, etc.).

The engine calls `evaluate()` first; only if it returns `true` does it call `apply()`.

---

## 5. When Does Automation Run?

There are **three distinct trigger points**:

### Trigger 1: Ticket Created (Verified)

**Where:** `TicketObserver::created()`

When a new ticket is created AND `$ticket->verified === true`, the observer calls:

```php
$this->automationEngine->processNewTicket($ticket);
```

This runs **all** non-escalation rules (assignment, priority, auto_reply, sla_breach).

After automation completes, if the ticket is still unassigned:

```php
$this->assignmentService->assignTicket($ticket);
```

This is the **fallback** — the `TicketAssignmentService` tries to find a suitable operator automatically.

### Trigger 2: Ticket Becomes Verified (Updated)

**Where:** `TicketObserver::updated()`

If a ticket already existed but was unverified (e.g., waiting for email verification), and then becomes verified via an update, the same automation pipeline fires:

```php
if ($ticket->wasChanged('verified') && $ticket->verified) {
    $this->automationEngine->processNewTicket($ticket);
    // + fallback assignment
}
```

### Trigger 3: Scheduled Commands

Two scheduled Artisan commands run periodically:

| Command                       | Schedule         | What it does                                                                                 |
| ----------------------------- | ---------------- | -------------------------------------------------------------------------------------------- |
| `tickets:process-escalations` | Every 15 minutes | Runs escalation rules for ALL companies — finds idle tickets matching each rule's conditions |
| `helpdesk:check-sla-breaches` | Every minute     | Checks all open tickets for SLA status updates; triggers SLA breach rules on first breach    |

---

## 6. The Five Rule Types (In Depth)

### 6.1 Assignment Rule (`TYPE_ASSIGNMENT` = `'assignment'`)

**File:** `app/Services/Automation/Rules/AssignmentRule.php`

**Purpose:** Automatically assign newly created tickets to the right person or team.

#### Evaluation (conditions):

The `evaluate()` method checks:

1. **Already assigned?** → If `$ticket->assigned_to` is set, return `false` (skip — don't re-assign).
2. **Not verified?** → If `$ticket->verified` is `false`, return `false`.
3. **Category match** → If the rule has `conditions.category_id`, check if the ticket's category matches (exact match OR the ticket's category is a child of the condition category).
4. **Priority match** → If the rule has `conditions.priority` (array), check if the ticket's priority is in that array.

All conditions must be met (AND logic). If a condition is empty/null, it's skipped (treated as "any").

#### Application (actions):

The rule supports **three mutually-exclusive assignment strategies**:

| Action                        | Behavior                                                                                                                                         |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `assign_to_specialist: true`  | Delegates to `TicketAssignmentService::assignTicket()` which finds the best specialist by category, then generalist, then any available operator |
| `assign_to_team_id: <id>`     | Delegates to `TicketAssignmentService::assignToTeam()` which finds the best member within that team                                              |
| `assign_to_operator_id: <id>` | Directly assigns to a specific operator via `reassignTicket()`                                                                                   |

Priority: specialist → team → operator (checked in that order in the code).

---

### 6.2 Priority Rule (`TYPE_PRIORITY` = `'priority'`)

**File:** `app/Services/Automation/Rules/PriorityRule.php`

**Purpose:** Automatically change a ticket's priority based on its content or attributes.

#### Evaluation (conditions):

1. **Keywords** → If `conditions.keywords` is set (array of strings), the rule searches the **subject + description** (lowercased) for any matching keyword. If none found → `false`. Example keywords: `["urgent", "critical", "down", "outage", "emergency"]`.
2. **Category match** → Same subcategory-aware check as Assignment.
3. **Current priority** → If `conditions.current_priority` is set, only trigger if the ticket's current priority is in that array. This lets you create rules like "only boost medium priority tickets to urgent if they contain keywords".

#### Application (actions):

- `set_priority: "urgent"` — Sets the ticket's priority to the specified value. Valid values: `low`, `medium`, `high`, `urgent`.
- Uses `$ticket->saveQuietly()` to avoid triggering the observer again (prevents infinite loops).

---

### 6.3 Auto Reply Rule (`TYPE_AUTO_REPLY` = `'auto_reply'`)

**File:** `app/Services/Automation/Rules/AutoReplyRule.php`

**Purpose:** Automatically send a confirmation or informational email to the customer after their ticket is created.

#### Evaluation (conditions):

1. **Verified check** → Must be verified.
2. **on_create** → If `conditions.on_create === true`, only fires if the ticket was created within the last 5 minutes.
3. **Category match** → Same subcategory-aware check.
4. **Priority match** → Optional filter by ticket priority.

#### Application (actions):

```json
{
    "send_email": true,
    "subject": "Re: Your support ticket",
    "message": "Thank you for your ticket. Our team will respond shortly."
}
```

- If `send_email !== true`, does nothing.
- Resolves recipient from `$ticket->customer->email` or `$ticket->customer_email`.
- Sends via `Mail::to($email)->queue(new AutoReplyMail($ticket, $subject, $message))` — **queued**, not synchronous.

---

### 6.4 Escalation Rule (`TYPE_ESCALATION` = `'escalation'`)

**File:** `app/Services/Automation/Rules/EscalationRule.php`

**Purpose:** Escalate tickets that have been idle (no updates) for too long. This is the only rule type that runs on a **schedule** rather than on ticket creation.

#### How it's triggered:

The `ProcessTicketEscalations` command runs every 15 minutes. It calls `AutomationEngine::processEscalations()` which:

1. Gets all active escalation rules for the company.
2. For each rule, calls `EscalationRule::findIdleTickets()`.
3. For each idle ticket, calls `executeRule()`.

#### `findIdleTickets()` — How idle tickets are found:

```php
Ticket::where('company_id', $rule->company_id)
    ->whereIn('status', $conditions['status'])   // e.g. ['pending', 'open']
    ->where('verified', true)
    ->where('updated_at', '<', now()->subHours($idleHours))  // e.g. 24 hours
```

If a category condition exists, it also filters by that category (including subcategories).

#### Evaluation (conditions):

1. **Status match** → Ticket status must be in `conditions.status` array (e.g., `['pending', 'open']`).
2. **Idle hours** → Ticket's `updated_at` must be older than `conditions.idle_hours` hours ago.
3. **Category match** → Optional.
4. **Already urgent check** → If the ticket is already `urgent` AND the only action is priority escalation (no reassign, no notify), skip it.

#### Application (actions):

| Action                                             | Behavior                                                                                              |
| -------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| `escalate_priority: true`                          | Bumps priority up by one level: low→medium, medium→high, high→urgent. Does nothing if already urgent. |
| `set_priority: "high"`                             | Sets an absolute priority (alternative to escalate).                                                  |
| `notify_admin: true`                               | Sends `EscalationNotificationMail` to all admin users of the company.                                 |
| `reassign: true` + `reassign_to_operator_id: <id>` | Reassigns the ticket to a specific operator via `TicketAssignmentService::reassignTicket()`.          |

Multiple actions can fire together (e.g., escalate priority AND notify admin AND reassign).

---

### 6.5 SLA Breach Rule (`TYPE_SLA_BREACH` = `'sla_breach'`)

**File:** `app/Services/Automation/Rules/SlaBreachRule.php`

**Purpose:** Define what happens when a ticket's SLA deadline is breached.

#### How it's triggered:

The `CheckSlaBreaches` command runs **every minute**. When it detects a ticket transitioning to `breached` status for the first time (previous status was NOT `breached`), it:

1. Notifies the assigned operator.
2. Fetches all active `sla_breach` rules for the company.
3. Executes each rule against the breached ticket.
4. **Fallback:** If no rule has `notify_admin: true`, the command itself sends admin notifications directly.

#### Evaluation (conditions):

1. **Category match** → Optional filter.
2. Always returns `true` if category matches (or no category condition) — the scheduler already verified the ticket is breached.

#### Application (actions):

| Action                        | Behavior                                                                                                                                           |
| ----------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| `assign_to_operator_id: <id>` | Reassigns the ticket to a different operator (e.g., a senior agent).                                                                               |
| `escalate_priority: true`     | Bumps priority up one level (low→medium→high→urgent).                                                                                              |
| `set_priority: "urgent"`      | Sets an absolute priority (alternative to escalate). Cannot use both escalate AND set — if `escalate_priority` is true, `set_priority` is ignored. |
| `notify_admin: true`          | Sends `SlaBreached` notification to all company admins.                                                                                            |

Updates are applied via `$ticket->update($updates)` (NOT `saveQuietly`), which means the **TicketObserver** will fire — particularly, if priority changes, the observer recalculates `due_time`. Since the ticket is already breached and the new `due_time` will be in the future, the observer resets `sla_status` back to `'on_time'`, giving the ticket a fresh SLA window.

---

## 7. Conditions & Actions — Complete Reference

### Conditions JSON Structure by Type

#### Assignment

```json
{
    "category_id": 5, // int|null — Match category (or subcategory of this parent)
    "priority": ["high", "urgent"] // array|null — Only match these priorities
}
```

#### Priority

```json
{
    "keywords": ["urgent", "critical", "down"], // array — Words to search in subject+description
    "category_id": 5, // int|null
    "current_priority": ["low", "medium"] // array|null — Only trigger for these current priorities
}
```

#### Auto Reply

```json
{
    "on_create": true, // bool — Only fire within 5 minutes of creation
    "category_id": 5, // int|null
    "priority": ["high"] // array|null
}
```

#### Escalation

```json
{
    "idle_hours": 24, // int — Hours without any update
    "status": ["pending", "open"], // array — Ticket must be in one of these statuses
    "category_id": 5 // int|null (optional)
}
```

#### SLA Breach

```json
{
    "category_id": 5 // int|null (optional)
}
```

### Actions JSON Structure by Type

#### Assignment

```json
// Option A: Smart specialist assignment
{
    "assign_to_specialist": true,
    "fallback_to_generalist": true
}
// Option B: Team assignment
{
    "assign_to_specialist": false,
    "assign_to_team_id": 3
}
// Option C: Specific operator
{
    "assign_to_specialist": false,
    "assign_to_operator_id": 7
}
```

#### Priority

```json
{
    "set_priority": "urgent" // "low" | "medium" | "high" | "urgent"
}
```

#### Auto Reply

```json
{
    "send_email": true,
    "subject": "We received your request",
    "message": "Thank you for your ticket. Our team will respond shortly."
}
```

#### Escalation

```json
{
    "escalate_priority": true, // Bump up one level
    "set_priority": null, // OR set absolutely (ignored if escalate_priority is true)
    "notify_admin": true, // Email all company admins
    "reassign": true, // Enable reassignment
    "reassign_to_operator_id": 7 // New assignee
}
```

#### SLA Breach

```json
{
    "escalate_priority": true, // Bump up one level
    "set_priority": null, // OR set absolutely
    "assign_to_operator_id": 7, // Reassign
    "notify_admin": true // Notify admins
}
```

---

## 8. Database Schema

### `automation_rules` table

| Column             | Type                 | Default | Description                                                                  |
| ------------------ | -------------------- | ------- | ---------------------------------------------------------------------------- |
| `id`               | bigint (PK)          | auto    | Primary key                                                                  |
| `company_id`       | foreignId            | —       | References `companies.id` (cascade delete)                                   |
| `name`             | string               | —       | Human-readable rule name (e.g., "Auto-assign Billing tickets")               |
| `description`      | text (nullable)      | null    | Optional longer description                                                  |
| `type`             | enum                 | —       | One of: `assignment`, `priority`, `auto_reply`, `escalation`, `sla_breach`   |
| `conditions`       | json                 | —       | Conditions that must be met (structure varies by type — see §7)              |
| `actions`          | json                 | —       | Actions to perform when conditions match (structure varies by type — see §7) |
| `is_active`        | boolean              | true    | Whether the rule is enabled                                                  |
| `priority`         | unsigned integer     | 0       | Execution order (lower = first). See §10.                                    |
| `executions_count` | unsigned bigint      | 0       | How many times this rule has fired. See §9.                                  |
| `last_executed_at` | timestamp (nullable) | null    | When it last fired. See §9.                                                  |
| `created_at`       | timestamp            | —       | Standard Laravel                                                             |
| `updated_at`       | timestamp            | —       | Standard Laravel                                                             |

**Indexes:**

- Composite: `(company_id, is_active, type)` — Used by `getActiveRulesForCompany()` and `getRulesOfType()`
- Composite: `(company_id, priority)` — Used for ordered retrieval

---

## 9. Execution Count & Last Executed

Every time a rule successfully fires (i.e., `evaluate()` returns true AND `apply()` completes without exception), the engine calls:

```php
$rule->recordExecution();
```

This method does:

```php
public function recordExecution(): void
{
    $this->increment('executions_count', 1, ['last_executed_at' => now()]);
}
```

This is a single atomic SQL query:

```sql
UPDATE automation_rules
SET executions_count = executions_count + 1, last_executed_at = '2026-03-26 12:00:00'
WHERE id = ?
```

### What this is useful for:

- **Monitoring:** Admins can see in the UI how many times each rule has fired and when it last ran.
- **Debugging:** If a rule has 0 executions, its conditions may never match.
- **Auditing:** Track rule activity over time.

These values are displayed in the automation rules table UI as columns.

---

## 10. Priority Field on Rules

The `priority` field on an `AutomationRule` is **NOT** a ticket priority. It is the **execution order** of the rule within the company's rule set.

- Rules are fetched: `->orderBy('priority', 'asc')`
- **Lower number = runs first.** A rule with priority `0` executes before priority `1`.
- Range: 0–1000 (enforced by validation: `'priority' => 'required|integer|min:0|max:1000'`).
- Default: `0`.

### Why does order matter?

Because **all matching rules fire** (not just the first), the order determines:

1. **Assignment priority:** If two assignment rules both match, the first one to assign wins (the second will skip because `assigned_to` is already set).
2. **Priority cascading:** A priority rule at order 0 might set `medium → high`. A second rule at order 1 might then escalate `high → urgent` if additional conditions match.
3. **Auto-reply before assignment:** You might want the auto-reply to go out before assignment logic runs.

---

## 11. The Fallback Assignment Service

**File:** `app/Services/TicketAssignmentService.php`

If automation rules don't assign the ticket (no assignment rule matched, or no assignment rules exist), the `TicketObserver` calls:

```php
$this->assignmentService->assignTicket($ticket);
```

This service uses intelligent load-balanced assignment:

### Assignment Algorithm

```
1. Does ticket have a category?
   ├── YES → Find Specialist
   │   ├── Pass 1: Exact category specialist
   │   │   Query: operators with matching specialty, available, online,
   │   │          under max ticket load, ordered by open_category_tickets_count ASC,
   │   │          then last_assigned_at ASC (round-robin)
   │   ├── Pass 2: Parent category specialist (if ticket is a subcategory)
   │   │   Same query but for parent category
   │   └── Not found → Fall to Generalist
   └── NO → Find Generalist

2. Find Generalist
   Query: operators with NO specialty, available, online,
          under max ticket load, ordered by open_tickets_count ASC,
          then last_assigned_at ASC
   └── Not found → Try Any Operator

3. Try Any Operator (last resort)
   Query: any operator, available, online, under max load,
          ordered by open_tickets_count ASC, then last_assigned_at ASC
   └── Not found → Notify admins (TicketUnassigned notification)
```

### Key filters applied:

- **`available()`** — Operator's availability status allows assignment
- **`online()`** — Operator is currently online (tracked by activity middleware)
- **`assigned_tickets_count < max_tickets_per_agent`** — Prevents overloading (configurable per company via `TenantConfig`)
- **Round-robin tiebreaker** — `COALESCE(last_assigned_at, '1970-01-01') ASC` ensures fair distribution

### Team Assignment

When an assignment rule specifies `assign_to_team_id`, the service uses `assignToTeam()`:

1. Get all team members who are available, online, and under max load.
2. Prefer members whose specialty matches the ticket's category.
3. Fall back to least-loaded team member.
4. If no team member is available, remove team assignment and fall back to the global `assignTicket()` logic.

---

## 12. SLA System Integration

### How SLA Due Time is Calculated

**Where:** `TicketObserver::creating()`

When a ticket is being created, the observer calculates `due_time`:

```php
$minutes = match ($ticket->priority) {
    'urgent' => $policy ? $policy->urgent_minutes : 30,
    'high'   => $policy ? $policy->high_minutes   : 120,   // 2 hours
    'medium' => $policy ? $policy->medium_minutes  : 480,   // 8 hours
    'low'    => $policy ? $policy->low_minutes     : 1440,  // 24 hours
};
```

If the company has an `SlaPolicy` record with `is_enabled = true`, those custom minutes are used. Otherwise, hardcoded defaults apply.

The due time is: `now($companyTimezone)->addMinutes($minutes)->utc()`

### SLA Status Tracking

**Where:** `CheckSlaBreaches` command (runs every minute)

For every open ticket with a `due_time`:

| Remaining Time                      | Status     |
| ----------------------------------- | ---------- |
| Remaining ≤ 0                       | `breached` |
| Remaining ≤ 25% of total SLA window | `at_risk`  |
| Otherwise                           | `on_time`  |

The "25% threshold" is calculated as:

```
totalSlaSeconds = created_at → due_time (in seconds)
atRiskThreshold = floor(totalSlaSeconds × 0.25)
If remainingSeconds ≤ atRiskThreshold → "at_risk"
```

### When `breached` is detected for the first time:

1. Assigned operator gets a `SlaBreached` notification.
2. All active `sla_breach` type automation rules execute.
3. If no rule has `notify_admin: true`, admins are notified as a fallback.

### Priority change → SLA recalculation

**Where:** `TicketObserver::updating()`

If a ticket's priority changes (by automation or manual action), the observer recalculates `due_time` from NOW using the new priority's SLA minutes. If the ticket was previously `breached` but the new `due_time` is in the future, `sla_status` resets to `on_time`.

This means: **if an SLA breach rule escalates the priority, the SLA timer automatically resets**, giving the ticket a fresh deadline.

---

## 13. Scheduled Commands

Defined in `routes/console.php`:

### `tickets:process-escalations`

| Property          | Value                                                                                                                                                   |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Schedule**      | Every 15 minutes                                                                                                                                        |
| **Guard**         | `withoutOverlapping()` — prevents concurrent runs                                                                                                       |
| **Background**    | Yes                                                                                                                                                     |
| **What it does**  | Iterates all companies → for each, calls `AutomationEngine::processEscalations()` → finds idle tickets matching each escalation rule → executes actions |
| **Optional flag** | `--company=<id>` to process a single company                                                                                                            |

### `helpdesk:check-sla-breaches`

| Property         | Value                                                                                                                                                                       |
| ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Schedule**     | Every minute                                                                                                                                                                |
| **Guard**        | `withoutOverlapping()`                                                                                                                                                      |
| **Background**   | Yes                                                                                                                                                                         |
| **What it does** | Queries ALL open tickets with `due_time` set → updates `sla_status` → on first breach detection: triggers SLA breach rules + notifies operator + notifies admins (fallback) |

### Other related scheduled commands:

| Command                        | Schedule          | Purpose                                                                           |
| ------------------------------ | ----------------- | --------------------------------------------------------------------------------- |
| `app:mark-inactive-users`      | Every minute      | Marks users as offline if inactive (affects operator availability for assignment) |
| `app:process-ticket-lifecycle` | Hourly            | Handles warnings and auto-closing of stale tickets                                |
| `app:cleanup-old-tickets`      | Daily at midnight | Cleans up old closed/soft-deleted tickets                                         |

---

## 14. The UI — Automation Tab

### Routes

| URL                            | filterMode   | Shows                                              |
| ------------------------------ | ------------ | -------------------------------------------------- |
| `/automation`                  | `ticket`     | Priority, Auto Reply, Escalation, SLA Breach rules |
| `/automation/ticket-rules`     | `ticket`     | Same as above                                      |
| `/automation/assignment-rules` | `assignment` | Only Assignment rules                              |
| `/automation/sla-policy`       | —            | SLA Policy configuration page                      |

All routes are authenticated and require `company.access` + `verified` middleware.

### Livewire Component: `AutomationRulesTable`

**File:** `app/Livewire/Automation/AutomationRulesTable.php`

The component provides:

- **Search** — Filters rules by name or description (debounced).
- **Type filter** — Dropdown to show only a specific rule type.
- **Status filter** — Active / Inactive.
- **Sortable columns** — Priority, Name, Executions count. Click to toggle ASC/DESC.
- **Pagination** — 10 rules per page.

### Filter Modes

The `filterMode` property controls which types are visible:

| filterMode     | Types shown                                          | Default new rule type |
| -------------- | ---------------------------------------------------- | --------------------- |
| `'all'`        | All 5 types                                          | `assignment`          |
| `'assignment'` | Only `assignment`                                    | `assignment`          |
| `'ticket'`     | `priority`, `auto_reply`, `escalation`, `sla_breach` | `priority`            |

### Rule Table Columns

| Column     | Description                                                                                                        |
| ---------- | ------------------------------------------------------------------------------------------------------------------ |
| Priority   | Execution order number (badge)                                                                                     |
| Name       | Rule name + description                                                                                            |
| Type       | Color-coded badge (`assignment`=blue, `priority`=amber, `auto_reply`=green, `escalation`=red, `sla_breach`=purple) |
| Status     | Toggle switch (active/inactive)                                                                                    |
| Executions | Count + "last executed X ago"                                                                                      |
| Actions    | Edit button, Delete button                                                                                         |

### Create/Edit Modal

The modal dynamically shows different condition and action fields based on the selected `type`:

- **Assignment:** Category selector, priority filter, assignment strategy (specialist/team/operator)
- **Priority:** Keyword input (tag-style), category, current priority filter, target priority
- **Auto Reply:** On-create toggle, category, priority filter, email subject + message
- **Escalation:** Idle hours input, status checkboxes, escalate vs set priority, notify admin, reassign operator
- **SLA Breach:** Category, escalate vs set priority, reassign operator, notify admin

---

## 15. Complete Lifecycle Walkthrough

### Scenario: A customer submits a support ticket via the widget

```
1. WIDGET SUBMISSION
   └─ WidgetController::submit() creates a Ticket with verified=false

2. EMAIL VERIFICATION
   └─ Customer clicks verification link → ticket.verified = true → save()

3. TICKET OBSERVER: updated()
   └─ Detects verified changed to true
   └─ Calls: automationEngine->processNewTicket($ticket)

4. AUTOMATION ENGINE: processNewTicket()
   └─ Fetches: AutomationRule::where(company_id, X)->active()->ordered()->get()
   └─ Result: [Rule#1 priority:0 assignment, Rule#2 priority:1 priority, Rule#3 priority:2 auto_reply]

   4a. Rule #1 (Assignment, priority:0)
       └─ AssignmentRule::evaluate() → category matches? yes → assigned_to null? yes → TRUE
       └─ AssignmentRule::apply() → assign_to_specialist:true
           └─ TicketAssignmentService::assignTicket()
               └─ Finds specialist for "Network" category → Nadia
               └─ Updates: ticket.assigned_to = Nadia.id
               └─ Increments: Nadia.assigned_tickets_count++
               └─ Sends: TicketAssigned notification to Nadia
       └─ recordExecution() → executions_count++ , last_executed_at = now()

   4b. Rule #2 (Priority, priority:1)
       └─ PriorityRule::evaluate() → keywords ["urgent","critical"]
           └─ Searches subject + description for keywords
           └─ Found "critical" in description → TRUE
       └─ PriorityRule::apply() → set_priority:"urgent"
           └─ ticket.priority = "urgent"
           └─ ticket.saveQuietly() (no observer re-trigger)
       └─ recordExecution()

   4c. Rule #3 (Auto Reply, priority:2)
       └─ AutoReplyRule::evaluate() → on_create:true, created < 5min ago → TRUE
       └─ AutoReplyRule::apply() → send_email:true
           └─ Mail::to(customer)->queue(AutoReplyMail)
       └─ recordExecution()

5. BACK IN OBSERVER: Post-automation check
   └─ ticket.refresh() → assigned_to = Nadia.id (set by Rule #1)
   └─ Already assigned → skip fallback

6. SLA RUNS IN BACKGROUND (every minute)
   └─ CheckSlaBreaches command checks this ticket
   └─ due_time was set in TicketObserver::creating() based on original priority
   └─ But Rule #2 changed priority to "urgent" via saveQuietly()
   └─ Note: saveQuietly() didn't trigger the observer, so due_time was NOT recalculated
   └─ The SLA deadline reflects the original priority's window

7. LATER: Ticket sits idle for 24 hours
   └─ ProcessTicketEscalations runs
   └─ EscalationRule::findIdleTickets() → updated_at > 24h ago, status in [pending,open]
   └─ EscalationRule::evaluate() → status matches, idle_hours met → TRUE
   └─ EscalationRule::apply() → escalate_priority + notify_admin + reassign
```

---

## 16. Sequence Diagrams

### New Ticket Automation Flow

```
Customer          Widget           TicketObserver      AutomationEngine      Rules            AssignmentService
   │                 │                   │                    │                  │                    │
   │── submit ──────>│                   │                    │                  │                    │
   │                 │── create ticket ─>│                    │                  │                    │
   │                 │                   │── creating() ─────>│                  │                    │
   │                 │                   │  (set due_time)    │                  │                    │
   │                 │                   │                    │                  │                    │
   │── verify ──────>│                   │                    │                  │                    │
   │                 │── update ticket ─>│                    │                  │                    │
   │                 │                   │── updated() ──────>│                  │                    │
   │                 │                   │                    │── processNewTicket()                  │
   │                 │                   │                    │── get active rules sorted by priority │
   │                 │                   │                    │                  │                    │
   │                 │                   │                    │── executeRule(assignment_rule) ──────>│
   │                 │                   │                    │                  │── evaluate() ─────>│
   │                 │                   │                    │                  │<── true ──────────>│
   │                 │                   │                    │                  │── apply() ────────>│
   │                 │                   │                    │                  │         │── assignTicket() ────>│
   │                 │                   │                    │                  │         │<── operator assigned ─│
   │                 │                   │                    │── recordExecution()        │                    │
   │                 │                   │                    │                  │                    │
   │                 │                   │                    │── executeRule(priority_rule) ────────>│
   │                 │                   │                    │                  │── evaluate() ─────>│
   │                 │                   │                    │                  │── apply() ────────>│
   │                 │                   │                    │── recordExecution()                   │
   │                 │                   │                    │                  │                    │
   │                 │                   │                    │── executeRule(auto_reply_rule) ──────>│
   │                 │                   │                    │                  │── evaluate() ─────>│
   │                 │                   │                    │                  │── apply() ────────>│
   │                 │                   │                    │── recordExecution()   (queue email)   │
   │                 │                   │                    │                  │                    │
   │                 │                   │── check if still unassigned ────────>│                    │
   │                 │                   │   (already assigned, skip fallback)  │                    │
```

### SLA Breach Detection Flow

```
Scheduler           CheckSlaBreaches      AutomationEngine      SlaBreachRule       Admins
   │                      │                      │                    │                │
   │── every minute ─────>│                      │                    │                │
   │                      │── query open tickets with due_time        │                │
   │                      │── for each ticket:   │                    │                │
   │                      │   calculate sla_status                    │                │
   │                      │   if breached (first time):               │                │
   │                      │   ├── notify assigned operator            │                │
   │                      │   ├── getRulesOfType(sla_breach) ───────>│                │
   │                      │   │                  │── executeRule() ──>│                │
   │                      │   │                  │                    │── evaluate() ──>│
   │                      │   │                  │                    │── apply() ─────>│
   │                      │   │                  │                    │  reassign       │
   │                      │   │                  │                    │  escalate       │
   │                      │   │                  │                    │  notify ───────>│
   │                      │   │                  │── recordExecution()│                │
   │                      │   │                  │                    │                │
   │                      │   └── if no rule notified admins:         │                │
   │                      │       └── fallback notify ───────────────────────────────>│
```

### Escalation Flow (Every 15 Minutes)

```
Scheduler           ProcessEscalations    AutomationEngine      EscalationRule
   │                      │                      │                    │
   │── every 15 min ─────>│                      │                    │
   │                      │── for each company:  │                    │
   │                      │   processEscalations()                    │
   │                      │                      │── get escalation rules
   │                      │                      │── for each rule:   │
   │                      │                      │   findIdleTickets() ──────────────>│
   │                      │                      │                    │── query DB ───>│
   │                      │                      │                    │<── idle tickets│
   │                      │                      │── for each idle ticket:             │
   │                      │                      │   executeRule() ──>│                │
   │                      │                      │                    │── evaluate() ──>
   │                      │                      │                    │── apply() ─────>
   │                      │                      │                    │  escalate_priority
   │                      │                      │                    │  notify_admin
   │                      │                      │                    │  reassign
   │                      │                      │── recordExecution()│
```

---

## Summary

The automation system is a **rule engine** with five specialized rule types, each with its own conditions and actions. Rules are:

- **Company-scoped** — each company configures their own
- **Evaluated in priority order** — lowest `priority` number first
- **Non-exclusive** — multiple rules can fire on the same ticket
- **Tracked** — execution count and last-executed timestamp for monitoring
- **Triggered** by three mechanisms: ticket creation/verification (instant), idle detection (every 15 min), and SLA breach detection (every minute)

If no automation rule assigns a ticket, the **fallback assignment service** uses an intelligent specialist → generalist → any operator algorithm with load balancing and round-robin fairness.
