# Performance Issues Report

## Overview
This document outlines performance bottlenecks and inefficiencies identified in the helpdesk system codebase. Issues are categorized by severity and potential impact on application performance at scale.

---

## HIGH (P1)

### 1. N+1 Query Problem in TicketsController

**Affected File:** `app/Http/Controllers/TicketsController.php` (lines 12-16)

**Description:**
The controller loads the ticket's company, then separately queries for company users:

```php
public function show($company, Ticket $ticket)
{
    $agents = $ticket->company->user;  // Triggers 2 queries
    return view('dashboard.tickets.show', compact('ticket', 'agents'));
}
```

**Impact:**
For each ticket view:
- Query 1: SELECT * FROM tickets WHERE id = ?
- Query 2: SELECT * FROM companies WHERE id = ? (via relationship)
- Query 3: SELECT * FROM users WHERE company_id = ?

**Recommendation:**
Use eager loading:
```php
$ticket = Ticket::with('company.users')
    ->findOrFail($ticket->id);
```

---

### 2. Missing Compound Index for Automation Queries

**Affected File:** `database/migrations/`

**Description:**
The `tickets` table lacks compound indexes for common query patterns used by automation rules:

```php
// In EscalationRule.php - complex query without optimal indexes
$query = Ticket::query()
    ->where('company_id', $rule->company_id)
    ->whereIn('status', $statuses)
    ->where('verified', true)
    ->where('updated_at', '<', now()->subHours($idleHours));
```

**Impact:**
As ticket volume grows, these queries will become slow without proper indexing. Each filter requires a full table scan.

**Recommendation:**
Add compound index:
```php
Schema::table('tickets', function (Blueprint $table) {
    $table->index(['company_id', 'status', 'verified', 'updated_at']);
});
```

---

## MEDIUM (P2)

### 3. Duplicate Queries in TicketDetails Render

**Affected File:** `app/Livewire/Dashboard/TicketDetails.php` (lines 491-507)

**Description:**
Two nearly identical queries execute sequentially to fetch replies and internal notes:

```php
'replies' => TicketReply::where('ticket_id', $this->ticket->id)
    ->where('is_internal', false)
    ->with('user:id,name')
    ->orderBy('created_at', 'asc')
    ->get(),

'internal_notes' => TicketReply::where('ticket_id', $this->ticket->id)
    ->where('is_internal', true)
    ->with('user:id,name')  // Same relation loaded twice
    ->orderBy('created_at', 'asc')
    ->get(),
```

**Impact:**
Two database round trips when one could suffice with a single query.

**Recommendation:**
Combine into single query:
```php
$replies = TicketReply::where('ticket_id', $this->ticket->id)
    ->with('user:id,name')
    ->orderBy('created_at', 'asc')
    ->get()
    ->groupBy('is_internal');
```

---

### 4. Missing Index on TicketCategories

**Affected File:** `database/migrations/2026_02_01_224218_create_ticket_categories_table.php`

**Description:**
The `ticket_categories` table lacks a compound index on `(company_id, name)` which is frequently used for lookups.

**Impact:**
Category queries will slow down as companies create more categories.

**Recommendation:**
Add index:
```php
$table->index(['company_id', 'name']);
```

---

## LOW (P3)

### 5. Static Cache Keys Without Invalidation

**Affected File:** `app/Livewire/Dashboard/TicketsTable.php` (lines 100-105)

**Description:**
Cache keys are static and don't account for when categories are updated:

```php
return cache()->remember(
    'company.'.Auth::user()->company_id.'.categories',
    3600,
    fn () => Auth::user()->company->categories()->select('id', 'name')->get()
);
```

**Impact:**
Users may see stale category data for up to 1 hour after categories are modified.

**Recommendation:**
Use cache tags or event-based invalidation:
```php
cache()->tags('company.'.$companyId.'.categories')->remember(..., fn() => ...);
// Invalidate when categories are modified
cache()->tags('company.'.$companyId.'.categories')->flush();
```

---

### 6. Multiple Queries in Loop for Notifications

**Affected File:** `app/Services/TicketAssignmentService.php` (lines 85-91)

**Description:**
Admins are notified individually in a loop:

```php
$admins = User::where('company_id', $ticket->company_id)
    ->whereIn('role', ['admin', 'super_admin'])
    ->get();

foreach ($admins as $admin) {
    $admin->notify(new TicketUnassigned($ticket));
}
```

**Impact:**
Each notification may trigger additional queries (fetching user preferences, formatting). Should use bulk notification.

**Recommendation:**
Use Laravel's bulk notification:
```php
Notification::send($admins, new TicketUnassigned($ticket));
```

---

### 7. Inefficient Agent Query Duplication

**Affected Files:**
- `app/Livewire/Dashboard/TicketDetails.php` (lines 68-78)
- `app/Livewire/Dashboard/TicketsTable.php` (lines 107-119)
- `app/Services/TicketAssignmentService.php` (lines 44-76)

**Description:**
The same agent lookup logic is duplicated across multiple files:

```php
User::query()
    ->where('company_id', $ticket->company_id)
    ->operators()
    ->available()
    // ...
```

**Impact:**
- Code duplication
- Harder to maintain
- Potential for inconsistent results

**Recommendation:**
Create a reusable AgentService:
```php
class AgentService
{
    public function getAvailableAgents(int $companyId): Collection
    {
        return User::query()
            ->where('company_id', $companyId)
            ->operators()
            ->available()
            ->get();
    }
}
```

---

## Summary Table

| ID | Severity | Issue | Impact |
|----|----------|-------|--------|
| 1 | HIGH | N+1 Query in Controller | 3 queries per page load |
| 2 | HIGH | Missing Compound Index | Slow automation queries at scale |
| 3 | MEDIUM | Duplicate Render Queries | 2 queries when 1 suffices |
| 4 | MEDIUM | Missing Category Index | Slow category lookups |
| 5 | LOW | Static Cache Without Invalidation | Stale data for up to 1 hour |
| 6 | LOW | Loop-Based Notifications | Multiple query batches |
| 7 | LOW | Duplicate Agent Queries | Maintainability issues |

---

## Remediation Priority

1. **Immediate:**
   - Fix N+1 query in TicketsController
   - Add compound index on tickets table

2. **This Sprint:**
   - Optimize TicketDetails render queries
   - Add category index

3. **Next Sprint:**
   - Implement cache invalidation
   - Create AgentService to eliminate duplication
   - Implement bulk notifications
