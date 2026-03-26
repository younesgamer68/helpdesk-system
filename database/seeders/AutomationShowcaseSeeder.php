<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SlaPolicy;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AutomationShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Company ──────────────────────────────────────────────────────
        $company = Company::query()->firstOrCreate(
            ['slug' => 'automation-demo'],
            [
                'name' => 'Automation Demo Co',
                'email' => 'support@automationdemo.test',
                'timezone' => 'UTC',
                'onboarding_completed_at' => now(),
            ]
        );

        $companyId = $company->id;

        // ── SLA Policy ───────────────────────────────────────────────────
        SlaPolicy::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'is_enabled' => true,
                'urgent_minutes' => 30,
                'high_minutes' => 120,
                'medium_minutes' => 480,
                'low_minutes' => 1440,
                'warning_hours' => 6,
                'auto_close_hours' => 72,
                'reopen_hours' => 48,
                'soft_delete_days' => 30,
                'hard_delete_days' => 90,
            ]
        );

        // ── Users ────────────────────────────────────────────────────────
        $admin = User::query()->updateOrCreate(
            ['email' => 'demo-admin@automationdemo.test'],
            [
                'company_id' => $companyId,
                'name' => 'Demo Admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_available' => true,
                'status' => 'online',
            ]
        );

        $nadia = User::query()->updateOrCreate(
            ['email' => 'demo-nadia@automationdemo.test'],
            [
                'company_id' => $companyId,
                'name' => 'Nadia',
                'role' => 'operator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_available' => true,
                'status' => 'online',
            ]
        );

        $bilal = User::query()->updateOrCreate(
            ['email' => 'demo-bilal@automationdemo.test'],
            [
                'company_id' => $companyId,
                'name' => 'Bilal',
                'role' => 'operator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_available' => true,
                'status' => 'online',
            ]
        );

        $sara = User::query()->updateOrCreate(
            ['email' => 'demo-sara@automationdemo.test'],
            [
                'company_id' => $companyId,
                'name' => 'Sara',
                'role' => 'operator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_available' => true,
                'status' => 'online',
            ]
        );

        $omar = User::query()->updateOrCreate(
            ['email' => 'demo-omar@automationdemo.test'],
            [
                'company_id' => $companyId,
                'name' => 'Omar',
                'role' => 'operator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_available' => true,
                'status' => 'online',
            ]
        );

        // ── Categories (parent + children) ───────────────────────────────
        $networkParent = TicketCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Network'],
            ['description' => 'Network related issues', 'default_priority' => 'high']
        );

        $networkConfig = TicketCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Network Config'],
            ['description' => 'Network configuration', 'default_priority' => 'high', 'parent_id' => $networkParent->id]
        );

        $vpnIssues = TicketCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'VPN Issues'],
            ['description' => 'VPN connectivity problems', 'default_priority' => 'high', 'parent_id' => $networkParent->id]
        );

        $billingParent = TicketCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Billing'],
            ['description' => 'Billing and invoicing', 'default_priority' => 'medium']
        );

        $invoices = TicketCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Invoices'],
            ['description' => 'Invoice related issues', 'default_priority' => 'medium', 'parent_id' => $billingParent->id]
        );

        $software = TicketCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Software'],
            ['description' => 'Software issues', 'default_priority' => 'medium']
        );

        // Sync operator category specialties
        $nadia->categories()->syncWithoutDetaching([$networkParent->id, $networkConfig->id, $vpnIssues->id]);
        $bilal->categories()->syncWithoutDetaching([$billingParent->id, $invoices->id]);

        // ── Teams ────────────────────────────────────────────────────────
        $networkOps = Team::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Network Ops'],
            ['color' => '#3B82F6', 'description' => 'Network operations team']
        );
        $networkOps->members()->syncWithoutDetaching([
            $nadia->id => ['role' => 'lead'],
            $omar->id => ['role' => 'member'],
        ]);

        $billingTeam = Team::query()->firstOrCreate(
            ['company_id' => $companyId, 'name' => 'Billing Team'],
            ['color' => '#F59E0B', 'description' => 'Billing support team']
        );
        $billingTeam->members()->syncWithoutDetaching([
            $bilal->id => ['role' => 'lead'],
            $sara->id => ['role' => 'member'],
        ]);

        // ── Customers ────────────────────────────────────────────────────
        $alice = Customer::query()->firstOrCreate(
            ['company_id' => $companyId, 'email' => 'alice@client.test'],
            ['name' => 'Alice Martin', 'is_active' => true]
        );

        $bob = Customer::query()->firstOrCreate(
            ['company_id' => $companyId, 'email' => 'bob@client.test'],
            ['name' => 'Bob Johnson', 'is_active' => true]
        );

        $clara = Customer::query()->firstOrCreate(
            ['company_id' => $companyId, 'email' => 'clara@client.test'],
            ['name' => 'Clara Nguyen', 'is_active' => true]
        );

        // ── Cleanup old automation rules & demo tickets ──────────────────
        AutomationRule::withoutGlobalScopes()->where('company_id', $companyId)->delete();

        Ticket::withTrashed()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('subject', 'like', '[DEMO]%')
            ->forceDelete();

        // ── Automation Rules ─────────────────────────────────────────────

        // 1. ASSIGNMENT — Auto-assign Network category to specialist (Nadia)
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'Auto-assign Network → Specialist',
            'type' => AutomationRule::TYPE_ASSIGNMENT,
            'is_active' => true,
            'priority' => 1,
            'conditions' => [
                'category_id' => $networkParent->id,
                'priority' => [],
            ],
            'actions' => [
                'assign_to_specialist' => true,
                'fallback_to_generalist' => true,
                'assign_to_team_id' => null,
                'assign_to_operator_id' => null,
            ],
        ]);

        // 2. ASSIGNMENT — Auto-assign Billing category to team
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'Auto-assign Billing → Billing Team',
            'type' => AutomationRule::TYPE_ASSIGNMENT,
            'is_active' => true,
            'priority' => 2,
            'conditions' => [
                'category_id' => $billingParent->id,
                'priority' => [],
            ],
            'actions' => [
                'assign_to_specialist' => false,
                'fallback_to_generalist' => false,
                'assign_to_team_id' => $billingTeam->id,
                'assign_to_operator_id' => null,
            ],
        ]);

        // 3. PRIORITY — Boost to urgent when subject has keywords
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'Keyword Priority Boost → Urgent',
            'type' => AutomationRule::TYPE_PRIORITY,
            'is_active' => true,
            'priority' => 1,
            'conditions' => [
                'keywords' => ['urgent', 'critical', 'down', 'outage', 'emergency'],
                'category_id' => null,
                'current_priority' => [],
            ],
            'actions' => [
                'set_priority' => 'urgent',
            ],
        ]);

        // 4. AUTO-REPLY — Send confirmation on new ticket
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'Auto-reply on Ticket Creation',
            'type' => AutomationRule::TYPE_AUTO_REPLY,
            'is_active' => true,
            'priority' => 1,
            'conditions' => [
                'on_create' => true,
                'category_id' => null,
                'priority' => [],
            ],
            'actions' => [
                'send_email' => true,
                'subject' => 'We received your ticket',
                'message' => 'Thank you for contacting Automation Demo Co support. Our team has received your ticket and will respond shortly. You can track your ticket status using the link in this email.',
            ],
        ]);

        // 5. ESCALATION — Reassign to Omar + escalate after 2h idle
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'Escalate Idle Tickets → Omar',
            'type' => AutomationRule::TYPE_ESCALATION,
            'is_active' => true,
            'priority' => 1,
            'conditions' => [
                'idle_hours' => 2,
                'status' => ['pending', 'open'],
                'category_id' => null,
            ],
            'actions' => [
                'escalate_priority' => true,
                'set_priority' => null,
                'notify_admin' => true,
                'assign_to_operator_id' => $omar->id,
            ],
        ]);

        // 6. SLA BREACH — Escalate priority + notify admin
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'SLA Breach → Escalate + Notify',
            'type' => AutomationRule::TYPE_SLA_BREACH,
            'is_active' => true,
            'priority' => 1,
            'conditions' => [
                'category_id' => null,
            ],
            'actions' => [
                'assign_to_operator_id' => null,
                'escalate_priority' => true,
                'set_priority' => null,
                'notify_admin' => true,
            ],
        ]);

        // ── Pre-staged Demo Tickets ──────────────────────────────────────

        // Ticket 1: Escalation demo — idle 3h, assigned to Nadia
        Ticket::withoutEvents(function () use ($companyId, $nadia, $networkConfig, $alice, $networkOps): void {
            Ticket::factory()->create([
                'company_id' => $companyId,
                'subject' => '[DEMO] Server response times are slow',
                'description' => 'Our production servers have been responding slowly since this morning. Average response time went from 200ms to 1.5s.',
                'status' => 'open',
                'priority' => 'medium',
                'assigned_to' => $nadia->id,
                'team_id' => $networkOps->id,
                'customer_id' => $alice->id,
                'category_id' => $networkConfig->id,
                'sla_status' => 'on_time',
                'due_time' => now()->addHours(5),
                'verified' => true,
                'source' => 'widget',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]);
        });

        // Ticket 2: At-risk SLA demo — due in 15 minutes
        Ticket::withoutEvents(function () use ($companyId, $nadia, $vpnIssues, $bob, $networkOps): void {
            Ticket::factory()->create([
                'company_id' => $companyId,
                'subject' => '[DEMO] Cannot connect to VPN',
                'description' => 'I am unable to connect to the corporate VPN from my home office. Getting "authentication failed" errors.',
                'status' => 'open',
                'priority' => 'high',
                'assigned_to' => $nadia->id,
                'team_id' => $networkOps->id,
                'customer_id' => $bob->id,
                'category_id' => $vpnIssues->id,
                'sla_status' => 'at_risk',
                'due_time' => now()->addMinutes(15),
                'verified' => true,
                'source' => 'widget',
                'created_at' => now()->subMinutes(105),
                'updated_at' => now()->subMinutes(105),
            ]);
        });

        // Ticket 3: SLA breach demo — past due
        Ticket::withoutEvents(function () use ($companyId, $bilal, $invoices, $clara, $billingTeam): void {
            Ticket::factory()->create([
                'company_id' => $companyId,
                'subject' => '[DEMO] Invoice #4521 incorrect tax',
                'description' => 'The tax amount on invoice #4521 is calculated at 25% instead of the correct 15% rate for our region.',
                'status' => 'pending',
                'priority' => 'low',
                'assigned_to' => $bilal->id,
                'team_id' => $billingTeam->id,
                'customer_id' => $clara->id,
                'category_id' => $invoices->id,
                'sla_status' => 'breached',
                'due_time' => now()->subHours(2),
                'verified' => true,
                'source' => 'widget',
                'created_at' => now()->subHours(26),
                'updated_at' => now()->subHours(26),
            ]);
        });

        // Ticket 4: Resolved example
        $resolvedAt = now()->subHours(4);
        Ticket::withoutEvents(function () use ($companyId, $omar, $networkConfig, $alice, $networkOps, $resolvedAt): void {
            Ticket::factory()->create([
                'company_id' => $companyId,
                'subject' => '[DEMO] DNS resolution failing',
                'description' => 'DNS lookups for internal domains are timing out. Affecting all users on the office network.',
                'status' => 'resolved',
                'priority' => 'urgent',
                'assigned_to' => $omar->id,
                'team_id' => $networkOps->id,
                'customer_id' => $alice->id,
                'category_id' => $networkConfig->id,
                'sla_status' => 'on_time',
                'due_time' => now()->subHours(8),
                'verified' => true,
                'source' => 'agent',
                'resolved_at' => $resolvedAt,
                'created_at' => now()->subHours(10),
                'updated_at' => $resolvedAt,
            ]);
        });

        // ── Recalculate operator assigned counts ─────────────────────────
        $this->recalculateAssignedCounts($companyId);

        // ── Console output ───────────────────────────────────────────────
        $this->printDemoScript($company);
    }

    private function recalculateAssignedCounts(int $companyId): void
    {
        $operators = User::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereIn('role', ['admin', 'operator'])
            ->get();

        foreach ($operators as $operator) {
            $count = Ticket::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('assigned_to', $operator->id)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();

            $operator->update(['assigned_tickets_count' => $count]);
        }
    }

    private function printDemoScript(Company $company): void
    {
        $slug = $company->slug;

        $this->command?->newLine();
        $this->command?->info('╔══════════════════════════════════════════════════════════╗');
        $this->command?->info('║          AUTOMATION SHOWCASE — READY TO DEMO            ║');
        $this->command?->info('╚══════════════════════════════════════════════════════════╝');
        $this->command?->newLine();

        $this->command?->info('🔐 LOGIN CREDENTIALS (password: password)');
        $this->command?->line('   Admin  → demo-admin@automationdemo.test');
        $this->command?->line('   Nadia  → demo-nadia@automationdemo.test');
        $this->command?->line('   Bilal  → demo-bilal@automationdemo.test');
        $this->command?->line('   Sara   → demo-sara@automationdemo.test');
        $this->command?->line('   Omar   → demo-omar@automationdemo.test');
        $this->command?->newLine();

        $this->command?->info('📋 DEMO SCRIPT — 7 Steps');
        $this->command?->newLine();

        $this->command?->line('  1. AUTO-ASSIGN (Specialist)');
        $this->command?->line('     Create a ticket with category "Network" or any subcategory');
        $this->command?->line('     → Ticket auto-assigns to Nadia (Network specialist)');
        $this->command?->newLine();

        $this->command?->line('  2. AUTO-ASSIGN (Team)');
        $this->command?->line('     Create a ticket with category "Billing" or "Invoices"');
        $this->command?->line('     → Ticket auto-assigns to Billing Team (round-robin)');
        $this->command?->newLine();

        $this->command?->line('  3. PRIORITY BOOST');
        $this->command?->line('     Create a ticket with "CRITICAL" or "outage" in the subject');
        $this->command?->line('     → Priority jumps to urgent automatically');
        $this->command?->newLine();

        $this->command?->line('  4. AUTO-REPLY');
        $this->command?->line('     Create any new ticket → Check Mailpit for auto-reply email');
        $this->command?->newLine();

        $this->command?->line('  5. ESCALATION');
        $this->command?->line('     Run: php artisan tickets:process-escalations');
        $this->command?->line('     → "[DEMO] Server response times" escalates (idle 3h > 2h threshold)');
        $this->command?->line('     → Reassigned to Omar + priority bumped');
        $this->command?->newLine();

        $this->command?->line('  6. SLA BREACH');
        $this->command?->line('     Run: php artisan helpdesk:check-sla-breaches');
        $this->command?->line('     → "[DEMO] Invoice #4521" triggers breach automation');
        $this->command?->line('     → Priority escalated + admin notified');
        $this->command?->newLine();

        $this->command?->line('  7. VIEW RULES');
        $this->command?->line('     Log in as admin → Automation page → See all 6 rules');
        $this->command?->line('     Toggle rules on/off to show configurability');
        $this->command?->newLine();

        $this->command?->info('✅ Seeder complete — 5 users, 6 rules, 4 demo tickets created.');
    }
}
