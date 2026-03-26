# Plan: Automation Showcase Seeder for Jury Demo

## TL;DR

Write a comprehensive `AutomationShowcaseSeeder` that creates a fully isolated demo company with all 5 automation rule types configured, pre-staged tickets in various states (idle, at-risk, breached, resolved), and prints a step-by-step demo script to the console. The jury can see each automation type fire live.

---

## Phase 1: Write the AutomationShowcaseSeeder

**Step 1** — Replace the empty `run()` in `database/seeders/AutomationShowcaseSeeder.php` with the full implementation. Creates an isolated demo company (`automation-demo` slug) so it never conflicts with existing data.

### Data created by the seeder:
w
**Company & SLA Policy**

- Company: "Automation Demo Co" (slug `automation-demo`, timezone UTC, onboarding completed)
- SLA Policy: urgent=30min, high=120min, medium=480min, low=1440min, all lifecycle settings populated

**Users** (all with password `password`)

- Admin: `demo-admin@automationdemo.test` (role: admin)
- Nadia: `demo-nadia@automationdemo.test` (operator, specialty=Network, categories synced)
- Bilal: `demo-bilal@automationdemo.test` (operator, specialty=Billing, categories synced)
- Sara: `demo-sara@automationdemo.test` (operator, generalist, no specialty)
- Omar: `demo-omar@automationdemo.test` (operator, senior engineer, escalation target)

**Categories** (parent + subcategories for subcategory matching demo)

- Network (parent) → Network Config, VPN Issues (children)
- Billing (parent) → Invoices (child)
- Software (standalone)

**Teams**

- "Network Ops" (#3B82F6) — Nadia (lead), Omar (member)
- "Billing Team" (#F59E0B) — Bilal (lead), Sara (member)

**Customers**

- Alice Martin (`alice@client.test`), Bob Johnson (`bob@client.test`), Clara Nguyen (`clara@client.test`)

**6 Automation Rules (all 5 types)**

1. ASSIGNMENT — Auto-assign Network → specialist (Nadia)
2. ASSIGNMENT — Auto-assign Billing → team (Billing Team)
3. PRIORITY — Boost to urgent when subject contains keywords [urgent, critical, down, outage, emergency]
4. AUTO-REPLY — Send confirmation email on new ticket creation
5. ESCALATION — Reassign to Omar + escalate priority after 2h idle (status: open, pending)
6. SLA BREACH — Escalate priority + notify admin on SLA breach

**4 Pre-staged Demo Tickets** (all prefixed `[DEMO]`, created with `Ticket::withoutEvents()`)
| Ticket | Purpose | State |
|--------|---------|-------|
| "Server response times are slow" | Escalation demo (idle 3h) | open, medium, assigned Nadia, created 3h ago |
| "Cannot connect to VPN" | At-risk SLA demo | open, high, VPN category, due in 15min |
| "Invoice #4521 incorrect tax" | SLA breach demo | pending, low, Invoices category, due_time past |
| "DNS resolution failing" | Resolved example | resolved, urgent, Network Config |

**Console output**

- Prints login credentials and a numbered 7-step demo script:
    1. Create Network ticket → auto-assigns to Nadia
    2. Create Billing ticket → routes to Billing Team
    3. Create ticket with "CRITICAL" in subject → priority jumps to urgent
    4. Any new ticket → check Mailpit for auto-reply
    5. Run `php artisan tickets:process-escalations` → escalates idle ticket + reassigns to Omar
    6. Run `php artisan helpdesk:check-sla-breaches` → flags breached invoice ticket
    7. View/toggle rules in Automation settings UI

### Key implementation patterns (matching existing codebase)

- Use `firstOrCreate` / `updateOrCreate` for idempotency (Company, Categories, Teams, Users, Customers)
- Use `syncWithoutDetaching` for team members and user categories
- Use `Ticket::withoutEvents()` to bypass TicketObserver (avoids auto-assignment firing during seeding)
- Use `Ticket::factory()->create([...])` inside `withoutEvents` wrapper
- Use `Hash::make('password')` (matching LandingPageTicketSeeder pattern)
- All models need `company_id` (CompanyScope global scope)

### Cleanup logic

- `AutomationRule::withoutGlobalScopes()->where('company_id', ...)->delete()` before recreating rules (idempotent)
- `Ticket::withTrashed()->withoutGlobalScopes()->where('subject', 'like', '[DEMO]%')->forceDelete()` before recreating tickets
- `recalculateAssignedCounts()` private helper at end to sync operator counts

---

## Phase 2: Format & Verify

**Step 2** — Run `vendor/bin/pint --dirty --format agent` to format the seeder file

**Step 3** — Run `php artisan db:seed --class=AutomationShowcaseSeeder` to verify the seeder executes without errors

**Step 4** — Verify the console output shows the demo script and login credentials

---

## Relevant Files

- `database/seeders/AutomationShowcaseSeeder.php` — THE file to modify (currently empty placeholder)
- `database/seeders/LandingPageTicketSeeder.php` — Reference for team/operator/ticket creation patterns
- `database/seeders/DatabaseSeeder.php` — Reference for company/admin/category creation
- `database/factories/TicketFactory.php` — Used for ticket creation
- `database/factories/AutomationRuleFactory.php` — Has states but we create rules directly
- `app/Models/AutomationRule.php` — TYPE constants, `$guarded = []`
- `app/Models/Ticket.php` — Fillable includes all needed fields; uses SoftDeletes
- `app/Models/SlaPolicy.php` — `$guarded = []`

## Decisions

- **Isolated company** — seeder creates its own company so it never interferes with existing data
- **Idempotent** — can be re-run safely; uses firstOrCreate/updateOrCreate + deletes old rules/tickets first
- **withoutEvents** — tickets seeded without observer to avoid auto-assignment logic firing during setup
- **No artisan command wrapper** — the seeder itself prints the demo instructions
- **All passwords are `password`** — simple for demo, not production
