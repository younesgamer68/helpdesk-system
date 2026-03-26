# SLA System — Complete Flow Documentation

## Overview

The SLA (Service Level Agreement) system tracks response deadlines per ticket based on priority level and automatically detects breaches. It runs on a per-company basis and can be enabled/disabled.

---

## 1. Configuration

### Where SLA is Configured

- **Onboarding Wizard** (Step 1): Admin sets SLA minutes per priority during initial setup
- **Settings → SLA Configuration** (`app/Livewire/Tickets/SlaConfiguration.php`): Full SLA management page

### SLA Policy Fields (`sla_policies` table)

| Field                | Default | Description                                         |
| -------------------- | ------- | --------------------------------------------------- |
| `is_enabled`         | false   | Master toggle — if disabled, no SLA tracking occurs |
| `urgent_minutes`     | 30      | Deadline for urgent tickets                         |
| `high_minutes`       | 120     | Deadline for high priority tickets                  |
| `medium_minutes`     | 480     | Deadline for medium priority tickets                |
| `low_minutes`        | 1440    | Deadline for low priority tickets                   |
| `warning_hours`      | —       | Hours before auto-close to send warning email       |
| `auto_close_hours`   | —       | Hours after resolved to auto-close                  |
| `reopen_hours`       | —       | Hours within which a closed ticket can be reopened  |
| `linked_ticket_days` | —       | Days to keep linked/related tickets                 |
| `soft_delete_days`   | —       | Days after close to soft-delete                     |
| `hard_delete_days`   | —       | Days after soft-delete to permanently delete        |

### When SLA Config Changes

When an admin saves SLA settings, `recalculateOpenTickets()` runs — it recalculates `due_time` for **all non-closed tickets** in the company based on the new SLA minutes.

---

## 2. Due Time Calculation

**Location:** `app/Observers/TicketObserver.php` → `calculateSlaDueTime()`

### When It Runs

- **Ticket created** → `creating` hook calculates initial `due_time`
- **Priority changed** → `updating` hook recalculates `due_time`
- **SLA config saved** → bulk recalculation for all open tickets

### Algorithm

```
1. Fetch SlaPolicy for ticket's company
2. If SLA disabled → due_time = null (no tracking)
3. Map priority to minutes:
   - urgent → 30 min
   - high   → 120 min
   - medium → 480 min
   - low    → 1440 min
4. Get company timezone
5. due_time = now(company_timezone) + minutes → stored as UTC
```

### Priority Change Recovery

If a ticket was `breached` but its priority changes and the new `due_time` is in the future, the `sla_status` resets to `on_time`.

---

## 3. SLA Status States

Tickets have an `sla_status` enum column with three values:

| Status     | Meaning                                               |
| ---------- | ----------------------------------------------------- |
| `on_time`  | More than 75% of SLA window remaining                 |
| `at_risk`  | Less than 25% of SLA window remaining (early warning) |
| `breached` | Past the deadline (`due_time` has passed)             |

---

## 4. Breach Detection — Scheduled Command

**Command:** `php artisan helpdesk:check-sla-breaches`  
**Schedule:** Every minute (with overlap protection)  
**Location:** `app/Console/Commands/CheckSlaBreaches.php`

### How It Works

```
Every minute:
  1. Query all tickets WHERE due_time IS NOT NULL
     AND status NOT IN ('resolved', 'closed')
  2. For each ticket, resolve current SLA status:

     remaining = now(company_tz).diffInSeconds(due_time)

     IF remaining <= 0 → 'breached'
     ELSE IF remaining <= 25% of total SLA window → 'at_risk'
     ELSE → 'on_time'

  3. If status changed → update ticket in DB
  4. If transitioning TO 'breached' (first time breach):
     a. Notify assigned operator (in-app notification)
     b. Run SLA_BREACH automation rules
     c. If no automation rule handled it → notify all admins (fallback)
```

### At-Risk Threshold

The at-risk threshold is hardcoded at **25% of total SLA window remaining**. Not configurable per company.

Example: A ticket with 120-minute SLA (high priority) becomes `at_risk` when only 30 minutes remain.

---

## 5. What Happens on Breach

### Immediate Actions (CheckSlaBreaches command)

1. **In-app notification to assigned operator** — `SlaBreached` notification via database + broadcast channels
2. **Automation rules execute** — Any active `sla_breach` type automation rules fire

### SLA Breach Automation Rule (`SlaBreachRule`)

Configurable actions when SLA breaches:

| Action                  | What It Does                                          |
| ----------------------- | ----------------------------------------------------- |
| `assign_to_operator_id` | Reassign ticket to a specific operator                |
| `escalate_priority`     | Bump priority up one level (e.g., medium → high)      |
| `set_priority`          | Set to a specific priority level                      |
| `notify_admin`          | Send `SlaBreached` notification to all company admins |

**Priority escalation caps at `urgent`** — won't go higher.

### Fallback

If no automation rule is configured or none handled the breach, the system falls back to notifying all admins directly.

### Notifications Sent

| Notification  | Channel              | Recipient         | Trigger                         |
| ------------- | -------------------- | ----------------- | ------------------------------- |
| `SlaBreached` | Database + Broadcast | Assigned operator | First breach detection          |
| `SlaBreached` | Database + Broadcast | All admins        | Via automation rule or fallback |

**No email is sent on SLA breach** — notifications are in-app only (appear in the notification bell + real-time via broadcast).

---

## 6. Ticket Lifecycle (Post-Resolution)

**Command:** `php artisan tickets:process-lifecycle`  
**Schedule:** Every hour

### Warning Email Flow

```
1. Find resolved tickets where:
   resolved_at <= now - (auto_close_hours - warning_hours)
   AND resolved_at > now - auto_close_hours

2. Send TicketClosedWarning email to customer
   → "Your ticket will be closed in X hours if no response"
```

### Auto-Close Flow

```
1. Find resolved tickets where:
   resolved_at <= now - auto_close_hours

2. Set status to 'closed'
3. Send TicketClosed notification to customer
```

---

## 7. Escalation System (Separate from SLA)

**Command:** `php artisan tickets:process-escalations`  
**Schedule:** Every 15 minutes

Escalation is **idle-time based**, not SLA-based. It fires when a ticket sits without activity for a configured number of hours.

| Config        | Description                                                     |
| ------------- | --------------------------------------------------------------- |
| `idle_hours`  | Hours without activity before escalation triggers (default: 24) |
| `status`      | Which statuses to check (default: open, pending)                |
| `category_id` | Optional category filter                                        |

### Escalation Actions

- Escalate priority (bump up one level)
- Set specific priority
- Reassign to specific operator
- Send `EscalationNotificationMail` email to admins

**Key difference:** Escalation sends an **email**. SLA breach sends **in-app notifications only**.

---

## 8. Dashboard Indicators

### Admin Dashboard

- **SLA Breaches count** — red badge showing total breached tickets across company

### Agent Dashboard

- **My SLA Breaches count** — shows only the agent's assigned breached tickets
- **Breached tickets list** — ordered by `due_time` ascending (oldest breach first)

### Ticket Sidebar

- At-risk visual indicator on individual ticket view

---

## 9. Schedule Summary

| Command                       | Frequency         | Purpose                                                  |
| ----------------------------- | ----------------- | -------------------------------------------------------- |
| `helpdesk:check-sla-breaches` | Every minute      | Detect SLA status changes (on_time → at_risk → breached) |
| `tickets:process-escalations` | Every 15 minutes  | Escalate idle tickets                                    |
| `tickets:process-lifecycle`   | Every hour        | Send warnings + auto-close resolved tickets              |
| `tickets:cleanup-old-tickets` | Daily at midnight | Soft-delete and hard-delete old tickets                  |

---

## 10. Complete Flow Diagram

```
Ticket Created
    │
    ▼
TicketObserver.creating()
    │ → calculateSlaDueTime() → sets due_time based on priority + SLA policy
    │
    ▼
Every Minute: CheckSlaBreaches
    │
    ├─ remaining > 75% of window → sla_status = 'on_time'
    ├─ remaining ≤ 25% of window → sla_status = 'at_risk'
    └─ remaining ≤ 0             → sla_status = 'breached'
                                        │
                                        ▼
                                   First Breach?
                                    ├─ YES
                                    │   ├─ Notify assigned operator (in-app)
                                    │   ├─ Run SLA_BREACH automation rules
                                    │   │   ├─ Reassign ticket?
                                    │   │   ├─ Escalate priority?
                                    │   │   └─ Notify admins?
                                    │   └─ No rules handled it? → Notify all admins (fallback)
                                    └─ NO → skip (already notified)

Priority Changed
    │
    ▼
TicketObserver.updating()
    │ → recalculate due_time
    │ → if new due_time is future & was breached → reset to 'on_time'

Ticket Resolved
    │
    ▼
Every Hour: ProcessTicketLifecycle
    │
    ├─ After (auto_close - warning) hours → Send TicketClosedWarning email
    └─ After auto_close hours              → Auto-close ticket
```
