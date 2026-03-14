# Security Issues Report

## Overview
This document outlines the security vulnerabilities identified in the helpdesk system codebase. Issues are categorized by severity and priority for remediation.

---

## CRITICAL (P0)

### 1. Mass Assignment Vulnerability

**Affected Files:**
- `app/Models/Ticket.php` (line 14)
- `app/Models/TicketCategory.php` (line 12)
- `app/Models/Company.php` (line 12)
- `app/Models/AutomationRule.php` (line 35)
- `app/Models/WidgetSetting.php` (line 11)

**Description:**
All models use `protected $guarded = [];` which means ALL attributes are mass-assignable. This allows attackers to override any column including `company_id`, `status`, `verified`, and other sensitive fields via `Model::create()` or `$model->fill()`.

**Risk:**
An attacker could:
- Assign tickets to other companies
- Change ticket status to bypass workflows
- Elevate privileges by modifying role fields
- Override any database column

**Recommendation:**
Replace `$guarded = []` with explicit `$fillable` arrays listing only safe-to-mass-assign fields.

---

## HIGH (P1)

### 2. Insecure Direct Object Reference (IDOR) - Ticket Delete/Restore

**Affected File:** `app/Livewire/Dashboard/TicketsTable.php` (lines 438-474)

**Description:**
The `deleteTicket()` and `restoreTicket()` methods fetch tickets by ID without verifying the ticket belongs to the current user's company:

```php
public function deleteTicket($ticketId)
{
    $ticket = Ticket::findOrFail($ticketId);
    $ticket->delete();
}
```

**Risk:**
Any authenticated user can delete or restore ANY ticket by guessing ticket IDs, regardless of company boundaries.

**Recommendation:**
Add company ownership check:
```php
$ticket = Ticket::where('company_id', auth()->user()->company_id)
    ->findOrFail($ticketId);
```

---

### 3. Missing Authorization on Ticket Assignment

**Affected File:** `app/Livewire/Dashboard/TicketDetails.php` (lines 145-174)

**Description:**
The `assign()` method allows any user to reassign tickets without permission verification:

```php
public function assign($agentId)
{
    $agent = $this->agents()->find($agentId);
    $this->ticket->update(['assigned_to' => $agentId]);
}
```

**Risk:**
Operators could reassign tickets they don't own, disrupting workflow.

**Recommendation:**
Add authorization check using Laravel policies or gate.

---

### 4. Missing Rate Limiting on Public Routes

**Affected Files:**
- `routes/web.php` - `/chatbot/chat` endpoint
- `routes/web.php` - `/register/quick` endpoint
- Widget submission routes (`/widget/{key}/submit`, `/widget/track/{ticketNumber}/{token}/reply`)

**Description:**
Public endpoints have no rate limiting configured, making them vulnerable to abuse.

**Risk:**
- Brute force attacks on registration
- Spam via chatbot
- Automated ticket spam

**Recommendation:**
Apply throttle middleware:
```php
Route::post('/chatbot/chat')->middleware(['throttle:10,1']);
```

---

### 5. Missing File Type Validation on Uploads

**Affected File:** `app/Livewire/Dashboard/TicketDetails.php` (lines 58-59, 275-285)

**Description:**
File validation only checks size but not file type:

```php
#[Validate(['attachments.*' => 'nullable|file|max:10240'])]
public $attachments = [];
```

**Risk:**
Users can upload malicious executables, scripts, or malware disguised as allowed file types.

**Recommendation:**
Add MIME type validation:
```php
'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
```

---

## LOW (P2)

### 6. Token Reuse Vulnerability

**Affected File:** `app/Http/Controllers/WidgetController.php` (lines 114-136)

**Description:**
The `verification_token` column is reused for different purposes. After verification clears the token, it gets reused for ticket tracking:

```php
$ticket->update(['verified' => true, 'verification_token' => null]);
$trackingToken = Str::random(64);
$ticket->update(['verification_token' => $trackingToken]);
```

**Risk:**
Confusing logic that could lead to security issues. Should use separate columns for verification and tracking tokens.

**Recommendation:**
Add a separate `tracking_token` column.

---

### 7. Hardcoded HTTP Protocol

**Affected File:** `app/Http/Controllers/GoogleController.php` (line 70)

**Description:**
Redirect uses hardcoded `http://` instead of dynamic protocol:

```php
return redirect()->to('http://'.$user->company->slug.'.'.config('app.domain').'/tickets');
```

**Risk:**
Users on HTTPS connections will be downgraded to HTTP, exposing session data.

**Recommendation:**
Use dynamic protocol detection:
```php
$protocol = request()->secure() ? 'https' : 'http';
```

---

## Summary Table

| ID | Severity | Issue | Priority |
|----|----------|-------|----------|
| 1 | CRITICAL | Mass Assignment Vulnerability | P0 |
| 2 | HIGH | IDOR - Ticket Delete/Restore | P0 |
| 3 | HIGH | Missing Authorization on Assignment | P1 |
| 4 | HIGH | Missing Rate Limiting | P1 |
| 5 | HIGH | Missing File Type Validation | P1 |
| 6 | LOW | Token Reuse Vulnerability | P2 |
| 7 | LOW | Hardcoded HTTP Protocol | P2 |

---

## Remediation Priority

1. **Immediate (This Week):**
   - Fix mass assignment on all models
   - Add company ownership checks on ticket operations

2. **This Sprint:**
   - Add rate limiting to public routes
   - Add file type validation
   - Add authorization checks on assignment

3. **Next Sprint:**
   - Separate verification/tracking tokens
   - Fix hardcoded protocol
