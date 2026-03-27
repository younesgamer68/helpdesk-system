# Automation System — Complete Technical Guide & Demo Script

> **Purpose**: This document explains how the automation system works under the hood and provides a step-by-step jury demo using the `AutomationShowcaseSeeder`.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [How Automation Triggers When a Ticket Is Created](#2-how-automation-triggers-when-a-ticket-is-created)
3. [The 5 Rule Types Explained](#3-the-5-rule-types-explained)
4. [Specialist & Team Assignment Logic](#4-specialist--team-assignment-logic)
5. [Scheduled Background Automation](#5-scheduled-background-automation)
6. [SLA System Integration](#6-sla-system-integration)
7. [Demo Setup Instructions](#7-demo-setup-instructions)
8. [Step-by-Step Jury Demo Script](#8-step-by-step-jury-demo-script)

---

## 1. Architecture Overview

The system uses an **event-driven, rule-based automation engine** built on 4 core components:

```
┌─────────────────────────────────────────────────────────────┐
│                    AUTOMATION ARCHITECTURE                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│   TicketObserver          — Detects ticket lifecycle events │
│         │                                                   │
│         ▼                                                   │
│   AutomationEngine        — Loads & orchestrates rules      │
│         │                                                   │
│         ▼                                                   │
│   Rule Handlers           — Evaluate conditions & apply     │
│   (AssignmentRule,          actions for each rule type      │
│    PriorityRule,                                            │
│    AutoReplyRule,                                           │
│    EscalationRule,                                          │
│    SlaBreachRule)                                           │
│                                                             │
│   Scheduled Commands      — Background processing for      │
│   (every 1min / 15min)      escalation & SLA breach rules  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Key files:**

| File                                               | Role                                              |
| -------------------------------------------------- | ------------------------------------------------- |
| `app/Observers/TicketObserver.php`                 | Listens to ticket create/update events            |
| `app/Services/Automation/AutomationEngine.php`     | Core engine — loads rules, dispatches to handlers |
| `app/Services/Automation/Rules/AssignmentRule.php` | Handles auto-assignment logic                     |
| `app/Services/Automation/Rules/PriorityRule.php`   | Handles keyword-based priority changes            |
| `app/Services/Automation/Rules/AutoReplyRule.php`  | Handles auto-reply emails                         |
| `app/Services/Automation/Rules/EscalationRule.php` | Handles idle ticket escalation                    |
| `app/Services/Automation/Rules/SlaBreachRule.php`  | Handles SLA breach response                       |
| `app/Services/TicketAssignmentService.php`         | Specialist/generalist/team matching               |
| `app/Models/AutomationRule.php`                    | Rule model — stores conditions & actions as JSON  |
| `routes/console.php`                               | Schedules background commands                     |

---

## 2. How Automation Triggers When a Ticket Is Created

This is the **complete flow from ticket creation to automation execution**:

```
 Customer submits ticket         Agent creates ticket
 (via Widget / Email)            (via Dashboard)
         │                              │
         └──────────┬──────────────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │   Ticket is saved     │
        │   in the database     │
        └───────────┬───────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │   TicketObserver      │
        │   creating() fires    │◄── Step 1: Calculate SLA due_time
        │                       │    based on ticket priority
        └───────────┬───────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │   TicketObserver      │
        │   created() fires     │◄── Step 2: Check if ticket is verified
        └───────────┬───────────┘
                    │
           Is ticket verified?
           ┌────┘     └────┐
          YES              NO ──► Wait for email verification
           │                      (when verified, updated() fires
           ▼                       and triggers automation then)
        ┌───────────────────────┐
        │   AutomationEngine    │
        │   processNewTicket()  │◄── Step 3: Load all active rules
        └───────────┬───────────┘    for this company, sorted by
                    │                priority (lower number = first)
                    │
     ┌──────────────┼──────────────┬──────────────┐
     ▼              ▼              ▼              │
  ASSIGNMENT    PRIORITY      AUTO-REPLY         │
  Rules         Rules         Rules              │
     │              │              │              │
     │    (ESCALATION & SLA_BREACH rules are     │
     │     SKIPPED here — they run on schedule)  │
     │              │              │              │
     ▼              ▼              ▼              │
  For each rule:                                 │
  ┌─────────────────────────────┐                │
  │  handler.evaluate(rule, t.) │◄── Step 4:    │
  │                             │    Check if    │
  │  Does ticket match the      │    conditions  │
  │  rule's conditions?         │    match       │
  └────────────┬────────────────┘                │
               │                                 │
          YES? │                                 │
               ▼                                 │
  ┌─────────────────────────────┐                │
  │  handler.apply(rule, ticket)│◄── Step 5:    │
  │                             │    Execute     │
  │  Perform the rule's action  │    the action  │
  │  (assign, set priority,     │                │
  │   send email, etc.)         │                │
  └────────────┬────────────────┘                │
               │                                 │
               ▼                                 │
  ┌─────────────────────────────┐                │
  │  rule.recordExecution()     │◄── Step 6:    │
  │                             │    Track       │
  │  Increment execution count  │    execution   │
  │  Update last_executed_at    │                │
  └─────────────────────────────┘                │
                                                 │
        ┌────────────────────────────────────────┘
        ▼
  ┌─────────────────────────────┐
  │  Fallback: if ticket is     │◄── Step 7: If no assignment
  │  STILL unassigned after     │    rule matched, try automatic
  │  all rules ran, call        │    assignment by category
  │  TicketAssignmentService    │    specialist / generalist
  └─────────────────────────────┘
```

### In plain words:

1. A ticket is saved → Laravel's **TicketObserver** fires automatically
2. The observer calculates the **SLA due time** (e.g., "urgent = respond within 30 minutes")
3. If the ticket is verified, the observer calls `AutomationEngine::processNewTicket()`
4. The engine loads **all active automation rules** for this company, sorted by priority
5. For each rule (assignment, priority, auto-reply), the engine:
    - **Evaluates** — Does this ticket match the rule's conditions? (category, keywords, etc.)
    - **Applies** — If yes, execute the action (assign operator, change priority, send email)
    - **Records** — Increment the rule's execution counter
6. If no assignment rule matched, the system falls back to `TicketAssignmentService` to try automatic specialist/generalist matching

---

## 3. The 5 Rule Types Explained

### 3.1 Assignment Rules

**Purpose**: Automatically assign tickets to the right operator or team.

**How conditions are checked:**

- Is the ticket category matching? (supports parent categories — e.g., "Network" matches "VPN Issues")
- Is the ticket NOT already assigned?

**How actions work:**

- **Specialist mode** (`assign_to_specialist = true`): Uses `TicketAssignmentService` to find an operator who has the ticket's category as a specialty
- **Team mode** (`assign_to_team_id`): Assigns to the best available member of the specified team
- **Specific operator** (`assign_to_operator_id`): Assigns directly to one operator

### 3.2 Priority Rules

**Purpose**: Automatically boost ticket priority based on keywords in the subject or description.

**How conditions are checked:**

- Scan the ticket's `subject` and `description` for keywords (case-insensitive)
- Must match at least ONE keyword from the list
- Optionally filter by category

**How actions work:**

- Sets the ticket priority to the configured level (e.g., "urgent")
- The TicketObserver then **recalculates the SLA due_time** based on the new priority

### 3.3 Auto-Reply Rules

**Purpose**: Send an automatic email confirmation when a ticket is created.

**How conditions are checked:**

- `on_create = true` → ticket was created within the last 5 minutes
- Optionally filter by category or priority

**How actions work:**

- Queues an `AutoReplyMail` to the customer's email
- Uses configured subject and message body

### 3.4 Escalation Rules

**Purpose**: Detect tickets that have been idle (no updates) for too long and escalate them.

**How conditions are checked:**

- Ticket status must be in the configured list (e.g., `pending`, `open`)
- Ticket's `updated_at` is older than `idle_hours` threshold
- Optionally filter by category

**How actions work:**

- Bump priority to next level (e.g., medium → high) or set to specific level
- Reassign to specified operator
- Notify administrators by email

**When it runs**: Every **15 minutes** via `php artisan tickets:process-escalations`

### 3.5 SLA Breach Rules

**Purpose**: Respond automatically when a ticket's SLA deadline is exceeded.

**How conditions are checked:**

- Ticket's `sla_status` is `breached` (due_time has passed)
- Optionally filter by category

**How actions work:**

- Escalate priority (bump one level or set specific)
- Reassign to operator
- Notify administrators

**When it runs**: Every **1 minute** via `php artisan helpdesk:check-sla-breaches`

---

## 4. Specialist & Team Assignment Logic

The `TicketAssignmentService` handles intelligent operator matching:

```
Ticket arrives with a category
         │
         ▼
  ┌──────────────────────────┐
  │ PASS 1: Find Specialist  │
  │                          │
  │ Look for operators who   │
  │ have this EXACT category │
  │ as their specialty       │
  │ (must be available,      │
  │  online, under capacity) │
  └────────┬─────────────────┘
           │
     Found? ──YES──► Assign (least busy first)
           │
          NO
           │
           ▼
  ┌──────────────────────────┐
  │ PASS 2: Parent Category  │
  │                          │
  │ If ticket category is    │
  │ a subcategory, check if  │
  │ anyone specializes in    │
  │ the PARENT category      │
  └────────┬─────────────────┘
           │
     Found? ──YES──► Assign (least busy first)
           │
          NO
           │
           ▼
  ┌──────────────────────────┐
  │ PASS 3: Generalist       │
  │                          │
  │ Find operators who have  │
  │ NO category specialties  │
  │ at all (generalists)     │
  │ Fallback: any available  │
  │ operator                 │
  └────────┬─────────────────┘
           │
           ▼
      Assign (least busy, round-robin)
```

**Load balancing**: Operators are sorted by `assigned_tickets_count` (ascending) and then by `last_assigned_at` (oldest first) for round-robin fairness.

---

## 5. Scheduled Background Automation

Two scheduled commands handle rules that can't run at ticket creation time:

| Command                                   | Frequency    | What It Does                                                                                                                   |
| ----------------------------------------- | ------------ | ------------------------------------------------------------------------------------------------------------------------------ |
| `php artisan tickets:process-escalations` | Every 15 min | Finds idle tickets that exceed the configured `idle_hours` threshold and runs escalation rules against them                    |
| `php artisan helpdesk:check-sla-breaches` | Every 1 min  | Recalculates SLA status for all open tickets. When a ticket transitions to `breached`, it triggers SLA breach automation rules |

These are registered in `routes/console.php` and run via Laravel's task scheduler (`php artisan schedule:run`).

---

## 6. SLA System Integration

Every ticket gets an SLA deadline calculated at creation time:

| Priority | Response Time | Warning Before |
| -------- | ------------- | -------------- |
| Urgent   | 30 minutes    | 6 hours        |
| High     | 2 hours       | 6 hours        |
| Medium   | 8 hours       | 6 hours        |
| Low      | 24 hours      | 6 hours        |

**SLA Status Flow:**

```
on_time  ──►  at_risk  ──►  breached
(green)       (amber)        (red)
              ▲                ▲
              │                │
        warning_hours     due_time has
        before breach      passed
```

When a ticket becomes `breached`, the `CheckSlaBreaches` command automatically triggers any active **SLA Breach rules**.

---

## 7. Demo Setup Instructions

### Prerequisites

- The application is running (`php artisan serve` or Sail/Valet)
- Database is migrated (`php artisan migrate`)
- Mail is captured by Mailpit (or similar, usually at `localhost:8025`)

### Seed the demo data

```bash
php artisan db:seed --class=AutomationShowcaseSeeder
```

This creates:

| What                   | Details                                                                       |
| ---------------------- | ----------------------------------------------------------------------------- |
| **Company**            | Automation Demo Co                                                            |
| **Admin**              | demo-admin@automationdemo.test (password: `password`)                         |
| **Operators**          | Nadia (Network specialist), Bilal (Billing specialist), Sara, Omar            |
| **Teams**              | Network Ops (Nadia lead, Omar member), Billing Team (Bilal lead, Sara member) |
| **Categories**         | Network → [Network Config, VPN Issues], Billing → [Invoices], Software        |
| **6 Rules**            | 2 assignment, 1 priority, 1 auto-reply, 1 escalation, 1 SLA breach            |
| **4 Demo Tickets**     | Pre-staged with different SLA states                                          |
| **6 KB Articles**      | Published articles across categories                                          |
| **7 Chatbot FAQs**     | Pre-loaded Q&A for the AI chatbot                                             |
| **4 Canned Responses** | Golden responses scoped by category                                           |

### The 6 Automation Rules Created

| #   | Name                               | Type       | Trigger Condition                                                       | Action                                          |
| --- | ---------------------------------- | ---------- | ----------------------------------------------------------------------- | ----------------------------------------------- |
| 1   | Auto-assign Network → Specialist   | Assignment | Category = Network (or subcategory)                                     | Assign to specialist (Nadia)                    |
| 2   | Auto-assign Billing → Billing Team | Assignment | Category = Billing (or subcategory)                                     | Assign to Billing Team member (round-robin)     |
| 3   | Keyword Priority Boost → Urgent    | Priority   | Subject/description contains: urgent, critical, down, outage, emergency | Set priority to Urgent                          |
| 4   | Auto-reply on Ticket Creation      | Auto-Reply | Any new ticket created                                                  | Send confirmation email to customer             |
| 5   | Escalate Idle Tickets → Omar       | Escalation | Ticket idle > 2 hours, status = pending or open                         | Bump priority + reassign to Omar + notify admin |
| 6   | SLA Breach → Escalate + Notify     | SLA Breach | Ticket SLA breached                                                     | Bump priority + notify admin                    |

### The 4 Pre-Staged Demo Tickets

| Ticket                                | Status   | SLA State              | Purpose                                           |
| ------------------------------------- | -------- | ---------------------- | ------------------------------------------------- |
| [DEMO] Server response times are slow | open     | on_time (idle 3h)      | Triggers escalation (idle > 2h threshold)         |
| [DEMO] Cannot connect to VPN          | open     | at_risk (due in 15min) | Shows SLA timer approaching deadline              |
| [DEMO] Invoice #4521 incorrect tax    | pending  | breached (past due)    | Triggers SLA breach automation                    |
| [DEMO] DNS resolution failing         | resolved | on_time                | Shows complete ticket lifecycle with conversation |

---

## 8. Step-by-Step Jury Demo Script

> **Login URL**: http://helpdesk-system.test/login
> **Admin credentials**: demo-admin@automationdemo.test / password

---

### Demo 1: Auto-Assignment to Specialist

**What you're showing**: When a ticket is filed under "Network", it automatically assigns to Nadia (the network specialist).

1. Log in as **admin** (demo-admin@automationdemo.test)
2. Go to **Tickets** → Click **New Ticket**
3. Fill in:
    - Customer email: `jury-test@example.com`
    - Subject: `WiFi not working in building B`
    - Category: **Network Config** (or **VPN Issues** — any Network subcategory)
    - Priority: Medium
4. Submit the ticket
5. **Result**: The ticket is immediately assigned to **Nadia** and tagged with **Network Ops** team
6. **Explain**: "The assignment rule detected the Network category, found Nadia as the specialist for Network, and auto-assigned her. She's the least busy specialist available."

---

### Demo 2: Auto-Assignment to Team

**What you're showing**: Billing tickets go to the Billing Team, distributed by workload.

1. Create another ticket:
    - Customer email: `jury-test2@example.com`
    - Subject: `Question about my invoice`
    - Category: **Invoices** (child of Billing)
    - Priority: Medium
2. Submit the ticket
3. **Result**: The ticket is assigned to a **Billing Team** member (Bilal or Sara, whoever has fewer tickets)
4. **Explain**: "This rule assigns to the team rather than a specific specialist. The system picks the team member with the lowest workload."

---

### Demo 3: Keyword Priority Boost

**What you're showing**: Keywords in the subject automatically escalate priority.

1. Create a ticket:
    - Customer email: `jury-test3@example.com`
    - Subject: `CRITICAL: All servers are down`
    - Category: **Software** (or any)
    - Priority: Low ← intentionally set low
2. Submit the ticket
3. **Result**: The priority is automatically changed from **Low** → **Urgent**
4. **Explain**: "The priority rule scanned the subject, detected the keyword 'CRITICAL', and boosted the priority to Urgent. This also recalculated the SLA deadline to 30 minutes."

---

### Demo 4: Auto-Reply Email

**What you're showing**: Every new ticket triggers a confirmation email to the customer.

1. After any of the tickets above, open **Mailpit** (http://localhost:8025)
2. **Result**: You'll see an email sent to the customer with subject "We received your ticket"
3. **Explain**: "An auto-reply rule fires on every new ticket, sending a confirmation email to the customer. This happens automatically — no agent action needed."

---

### Demo 5: Escalation (Idle Ticket)

**What you're showing**: Tickets left idle too long get escalated automatically.

1. Show the **[DEMO] Server response times are slow** ticket — it's assigned to Nadia, priority Medium, created 3 hours ago with no updates
2. Open a terminal and run:
    ```bash
    php artisan tickets:process-escalations
    ```
3. Refresh the ticket page
4. **Result**:
    - Priority bumped from **Medium → High**
    - Ticket reassigned from **Nadia → Omar**
    - Admin receives a notification
5. **Explain**: "The escalation rule detected this ticket has been idle for 3 hours, exceeding the 2-hour threshold. It automatically escalated the priority and reassigned to Omar for faster handling. In production, this command runs every 15 minutes automatically."

---

### Demo 6: SLA Breach Response

**What you're showing**: When an SLA deadline is missed, the system responds automatically.

1. Show the **[DEMO] Invoice #4521 incorrect tax** ticket — it's breached (past due)
2. Open a terminal and run:
    ```bash
    php artisan helpdesk:check-sla-breaches
    ```
3. Refresh the ticket page
4. **Result**:
    - Priority bumped from **Low → Medium**
    - Admin notified about the breach
5. **Explain**: "The SLA monitor runs every minute. It detected this ticket exceeded its 24-hour deadline, marked it as breached, and triggered the SLA breach rule — which escalated the priority and notified administrators. This ensures no ticket falls through the cracks."

---

### Demo 7: View & Configure Rules

**What you're showing**: Admins can see, create, edit, and toggle all automation rules.

1. Navigate to **Automation** in the sidebar (http://helpdesk-system.test/automation)
2. Show the **6 rules** in the table with execution counts
3. Click **Edit** on any rule — show the conditions and actions
4. Toggle a rule **off** — show it becomes inactive
5. **Explain**: "Admins have full control. They can create new rules, set priorities to control execution order, and toggle rules on or off. Each rule tracks how many times it has been executed."

---

### Demo 8: SLA Dashboard

**What you're showing**: Real-time SLA monitoring across all tickets.

1. Go to the **Dashboard** as admin
2. Point out tickets with different SLA statuses:
    - 🟢 **On-time** — within deadline
    - 🟡 **At-risk** — approaching deadline (the VPN ticket)
    - 🔴 **Breached** — past deadline (the Invoice ticket)
3. **Explain**: "The dashboard gives a real-time view of all tickets and their SLA health. Agents can see at a glance which tickets need immediate attention."

---

### Demo 9: Complete Ticket Lifecycle

**What you're showing**: Full conversation history and audit trail.

1. Open the **[DEMO] DNS resolution failing** ticket (resolved)
2. Show:
    - The conversation: Omar's diagnosis → Alice's confirmation → Omar's resolution
    - The **internal note** from Nadia (not visible to the customer)
    - The **activity log**: created → assigned → status changes
3. **Explain**: "Every ticket maintains a complete conversation history and audit trail. Internal notes let agents collaborate without the customer seeing. The activity log tracks every change for accountability."

---

### Demo 10: Knowledge Base & AI Chatbot

**What you're showing**: Self-service options for customers.

1. Go to **Knowledge Base** → Show the 6 published articles with view counts
2. Go to **Channels → AI Chatbot Widget** → Click **Test Chatbot**
3. Type: `How do I connect to VPN?` → The chatbot answers from pre-loaded FAQs
4. Type: `talk to an agent` → The chatbot offers to escalate to a human
5. **Explain**: "Customers can find answers themselves through the knowledge base and chatbot, reducing ticket volume. If the chatbot can't help, it smoothly hands off to a human agent."

---

## Summary for Jury

The automation system has **three layers**:

1. **Real-time automation** (instant, on ticket creation):
    - Auto-assignment (specialist, team, or specific operator)
    - Priority boost (keyword detection)
    - Auto-reply emails

2. **Background automation** (scheduled, periodic):
    - Escalation of idle tickets (every 15 min)
    - SLA breach detection and response (every 1 min)

3. **Admin configuration** (no code needed):
    - Create/edit/delete rules via the UI
    - Set conditions (category, keywords, idle time)
    - Set actions (assign, change priority, send email, notify)
    - Toggle rules on/off, set execution priority

All of this works together to ensure **no ticket is missed, SLAs are enforced, and the right agent gets the right ticket at the right time** — without any manual intervention.
