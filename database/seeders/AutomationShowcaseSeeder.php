<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\ChatbotFaq;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\Customer;
use App\Models\GoldenResponse;
use App\Models\KbArticle;
use App\Models\SlaPolicy;
use App\Models\Team;
use App\Models\TenantConfig;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketLog;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\WidgetSetting;
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

        // ── Tenant Config (Plan & Limits) ────────────────────────────────
        TenantConfig::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'plan' => 'enterprise',
                'features' => [
                    'ai_chatbot' => true,
                    'ai_suggestions' => true,
                    'sla_management' => true,
                    'automation_rules' => true,
                    'knowledge_base' => true,
                    'teams' => true,
                    'custom_widget' => true,
                ],
                'limits' => [
                    'max_operators' => 50,
                    'max_tickets_monthly' => 10000,
                    'max_kb_articles' => 500,
                    'max_automation_rules' => 100,
                ],
                'max_tickets_per_agent' => 25,
            ]
        );

        // ── Widget Settings ──────────────────────────────────────────────
        $widget = WidgetSetting::withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'is_active' => true,
                'theme_mode' => 'dark',
                'form_title' => 'Submit a Support Ticket',
                'welcome_message' => 'Welcome to Automation Demo Co support! Fill out the form below and we will get back to you shortly.',
                'success_message' => 'Thank you! Your ticket has been submitted. Check your email for confirmation.',
                'require_phone' => false,
                'show_category' => true,
                'default_status' => 'open',
                'default_priority' => 'medium',
            ]
        );

        // ── AI Settings ──────────────────────────────────────────────────
        CompanyAiSettings::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'ai_suggestions_enabled' => true,
                'ai_summary_enabled' => true,
                'ai_chatbot_enabled' => true,
                'chatbot_greeting' => 'Hi! I am the Automation Demo Co support assistant. How can I help you today?',
                'chatbot_fallback_threshold' => 20,
                'escalation_url_type' => 'standalone',
            ]
        );

        // ── Knowledge Base Articles ──────────────────────────────────────
        KbArticle::withoutGlobalScopes()->where('company_id', $companyId)->delete();

        KbArticle::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'ticket_category_id' => $networkParent->id,
            'title' => 'How to Connect to the Corporate VPN',
            'body' => '<h2>VPN Connection Guide</h2><p>Follow these steps to connect to the corporate VPN:</p><ol><li>Download the VPN client from the IT portal at <strong>portal.automationdemo.test/vpn</strong>.</li><li>Install and launch the application.</li><li>Enter the server address: <code>vpn.automationdemo.test</code></li><li>Use your company email and password to authenticate.</li><li>Select "Full Tunnel" mode for complete network access.</li></ol><h3>Troubleshooting</h3><p>If you receive an "authentication failed" error, try resetting your password through the IT portal. If the issue persists, ensure your account has VPN access enabled by contacting your team lead.</p>',
            'status' => 'published',
            'views' => 142,
            'helpful_yes' => 38,
            'helpful_no' => 3,
            'published_at' => now()->subDays(30),
        ]);

        KbArticle::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'ticket_category_id' => $networkParent->id,
            'title' => 'Network Troubleshooting Guide',
            'body' => '<h2>Basic Network Troubleshooting</h2><p>Before submitting a ticket, try these common fixes:</p><ol><li><strong>Restart your router/modem</strong> — Unplug for 30 seconds, then reconnect.</li><li><strong>Check DNS settings</strong> — Use <code>nslookup automationdemo.test</code> to verify DNS resolution.</li><li><strong>Flush DNS cache</strong> — Run <code>ipconfig /flushdns</code> (Windows) or <code>sudo dscacheutil -flushcache</code> (Mac).</li><li><strong>Test with ping</strong> — Run <code>ping 8.8.8.8</code> to check basic connectivity.</li></ol><h3>When to Escalate</h3><p>If none of the above resolves the issue, submit a ticket under the <strong>Network</strong> category with the output of the commands above.</p>',
            'status' => 'published',
            'views' => 89,
            'helpful_yes' => 24,
            'helpful_no' => 2,
            'published_at' => now()->subDays(25),
        ]);

        KbArticle::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'ticket_category_id' => $billingParent->id,
            'title' => 'Understanding Your Invoice',
            'body' => '<h2>Invoice Breakdown</h2><p>Your monthly invoice includes the following sections:</p><ul><li><strong>Subscription Fee</strong> — Your base plan cost.</li><li><strong>Usage Charges</strong> — Additional charges for overages (API calls, storage, etc.).</li><li><strong>Tax</strong> — Calculated based on your billing region. Standard rates: US 0-10%, EU 15-25%, other regions vary.</li><li><strong>Credits</strong> — Any promotional credits or refunds applied.</li></ul><h3>Common Questions</h3><p><strong>Why is my tax rate different?</strong> Tax rates are determined by your registered billing address. Contact billing support to update your address if needed.</p><p><strong>How do I get a receipt?</strong> Receipts are automatically emailed after payment. You can also download them from Settings → Billing → Invoice History.</p>',
            'status' => 'published',
            'views' => 215,
            'helpful_yes' => 56,
            'helpful_no' => 4,
            'published_at' => now()->subDays(45),
        ]);

        KbArticle::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'ticket_category_id' => $billingParent->id,
            'title' => 'How to Update Your Payment Method',
            'body' => '<h2>Updating Payment Information</h2><p>To update your credit card or payment method:</p><ol><li>Log in to your account dashboard.</li><li>Navigate to <strong>Settings → Billing → Payment Methods</strong>.</li><li>Click "Add Payment Method" to add a new card.</li><li>Set the new method as default, then remove the old one.</li></ol><p><strong>Note:</strong> Changes take effect on your next billing cycle. Any pending charges will still be billed to the previous method.</p>',
            'status' => 'published',
            'views' => 67,
            'helpful_yes' => 19,
            'helpful_no' => 1,
            'published_at' => now()->subDays(20),
        ]);

        KbArticle::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'ticket_category_id' => $software->id,
            'title' => 'Getting Started with the Desktop App',
            'body' => '<h2>Installation & Setup</h2><p>Download the desktop application for your platform:</p><ul><li><strong>Windows:</strong> Download from the portal and run the installer (.exe).</li><li><strong>macOS:</strong> Download the .dmg, drag to Applications.</li><li><strong>Linux:</strong> Use the AppImage or install via snap: <code>snap install automationdemo</code></li></ul><h3>First-Time Setup</h3><ol><li>Launch the app and click "Sign In".</li><li>Enter your company email and password.</li><li>Choose your workspace from the list.</li><li>Configure notification preferences in Settings.</li></ol><p>The app syncs automatically. If you experience sync issues, try signing out and back in.</p>',
            'status' => 'published',
            'views' => 312,
            'helpful_yes' => 78,
            'helpful_no' => 6,
            'published_at' => now()->subDays(60),
        ]);

        KbArticle::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'ticket_category_id' => null,
            'title' => 'Frequently Asked Questions',
            'body' => '<h2>General FAQ</h2><h3>What are your support hours?</h3><p>Our support team is available Monday through Friday, 9 AM to 6 PM UTC. Urgent issues are monitored 24/7.</p><h3>How do I reset my password?</h3><p>Click "Forgot Password" on the login page, enter your email, and follow the reset link sent to your inbox.</p><h3>Can I upgrade or downgrade my plan?</h3><p>Yes. Go to Settings → Billing → Change Plan. Upgrades take effect immediately; downgrades apply at the next billing cycle.</p><h3>Do you offer refunds?</h3><p>We offer a 30-day money-back guarantee for new subscriptions. Contact billing support for refund requests.</p>',
            'status' => 'published',
            'views' => 534,
            'helpful_yes' => 112,
            'helpful_no' => 8,
            'published_at' => now()->subDays(90),
        ]);

        // ── Chatbot FAQs ─────────────────────────────────────────────────
        ChatbotFaq::withoutGlobalScopes()->where('company_id', $companyId)->delete();

        $faqData = [
            ['What are your support hours?', 'Our support team is available Monday through Friday, 9 AM to 6 PM UTC. Urgent issues are monitored 24/7.'],
            ['How do I reset my password?', 'Click "Forgot Password" on the login page, enter your email, and follow the reset link sent to your inbox. The link expires after 60 minutes.'],
            ['How do I connect to the VPN?', 'Download the VPN client from the IT portal, enter server address vpn.automationdemo.test, and log in with your company credentials. See our Knowledge Base for a detailed guide.'],
            ['How do I update my payment method?', 'Go to Settings → Billing → Payment Methods. Add your new card, set it as default, then remove the old one.'],
            ['What is your refund policy?', 'We offer a 30-day money-back guarantee for new subscriptions. Contact billing support for refund requests.'],
            ['How do I submit a support ticket?', 'You can submit a ticket through the support widget on our website, by emailing support@automationdemo.test, or through the chatbot by requesting to speak with an agent.'],
            ['How long will it take to resolve my issue?', 'Response times depend on priority: Urgent (30 min), High (2 hours), Medium (8 hours), Low (24 hours). We aim to resolve most issues within one business day.'],
        ];

        foreach ($faqData as [$question, $answer]) {
            ChatbotFaq::query()->create([
                'company_id' => $companyId,
                'question' => $question,
                'answer' => $answer,
            ]);
        }

        // ── Canned Responses (Golden Responses) ─────────────────────────
        GoldenResponse::withoutGlobalScopes()->where('company_id', $companyId)->delete();

        GoldenResponse::query()->create([
            'company_id' => $companyId,
            'user_id' => $admin->id,
            'content' => 'Thank you for reaching out. I have reviewed your issue and am currently working on a resolution. I will update you shortly.',
            'category_id' => null,
        ]);

        GoldenResponse::query()->create([
            'company_id' => $companyId,
            'user_id' => $nadia->id,
            'content' => 'I have identified the network issue. Please try flushing your DNS cache and restarting your connection. Let me know if the problem persists.',
            'category_id' => $networkParent->id,
        ]);

        GoldenResponse::query()->create([
            'company_id' => $companyId,
            'user_id' => $bilal->id,
            'content' => 'I have checked your invoice and confirmed the discrepancy. A corrected invoice will be issued within 24 hours. You will receive it via email.',
            'category_id' => $billingParent->id,
        ]);

        GoldenResponse::query()->create([
            'company_id' => $companyId,
            'user_id' => $admin->id,
            'content' => 'This issue has been resolved. Please verify on your end and let us know if everything is working correctly. We will close this ticket in 48 hours if we do not hear back.',
        ]);

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

        // 3. KEYWORD ASSIGNMENT — Assign uncategorized networking tickets to Network Ops
        AutomationRule::create([
            'company_id' => $companyId,
            'name' => 'Uncategorized Network Keywords → Network Ops',
            'type' => AutomationRule::TYPE_KEYWORD_ASSIGNMENT,
            'is_active' => true,
            'priority' => 3,
            'conditions' => [
                'keywords' => ['network', 'vpn', 'dns', 'router', 'wifi'],
                'without_category' => true,
            ],
            'actions' => [
                'set_category_id' => $networkParent->id,
            ],
        ]);

        // 4. PRIORITY — Boost to urgent when subject has keywords
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

        // 5. AUTO-REPLY — Send confirmation on new ticket
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

        // 6. ESCALATION — Reassign to Omar + escalate after 2h idle
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

        // 7. SLA BREACH — Escalate priority + notify admin
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
        $resolvedTicket = null;
        Ticket::withoutEvents(function () use ($companyId, $omar, $networkConfig, $alice, $networkOps, $resolvedAt, &$resolvedTicket): void {
            $resolvedTicket = Ticket::factory()->create([
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

        // ── Ticket Replies & Logs ────────────────────────────────────────
        if ($resolvedTicket) {
            TicketReply::query()->where('ticket_id', $resolvedTicket->id)->delete();
            TicketLog::withoutGlobalScopes()->where('ticket_id', $resolvedTicket->id)->delete();

            TicketReply::query()->create([
                'ticket_id' => $resolvedTicket->id,
                'user_id' => $omar->id,
                'message' => 'I have identified the issue — the primary DNS server (10.0.1.5) is unresponsive. Switching all clients to the secondary DNS (10.0.1.6) as a temporary fix.',
                'is_internal' => false,
                'is_technician' => true,
                'created_at' => now()->subHours(8),
            ]);

            TicketReply::query()->create([
                'ticket_id' => $resolvedTicket->id,
                'user_id' => null,
                'customer_name' => 'Alice Martin',
                'message' => 'Thanks for the quick update! DNS seems to be resolving again on my end.',
                'is_internal' => false,
                'is_technician' => false,
                'created_at' => now()->subHours(6),
            ]);

            TicketReply::query()->create([
                'ticket_id' => $resolvedTicket->id,
                'user_id' => $omar->id,
                'message' => 'The primary DNS server has been restarted and is back online. Root cause was a memory leak in the DNS service. I have applied the latest patch to prevent recurrence.',
                'is_internal' => false,
                'is_technician' => true,
                'created_at' => now()->subHours(4),
            ]);

            // Internal note
            TicketReply::query()->create([
                'ticket_id' => $resolvedTicket->id,
                'user_id' => $nadia->id,
                'message' => 'Confirmed fix on network monitoring dashboard — all DNS queries resolving normally. Good catch, Omar.',
                'is_internal' => true,
                'is_technician' => true,
                'created_at' => now()->subHours(4),
            ]);

            TicketLog::query()->create([
                'company_id' => $companyId,
                'ticket_id' => $resolvedTicket->id,
                'user_id' => null,
                'action' => 'created',
                'description' => 'Ticket created via agent.',
                'created_at' => now()->subHours(10),
            ]);

            TicketLog::query()->create([
                'company_id' => $companyId,
                'ticket_id' => $resolvedTicket->id,
                'user_id' => $admin->id,
                'action' => 'assigned',
                'description' => 'Assigned to Omar by automation rule.',
                'created_at' => now()->subHours(10),
            ]);

            TicketLog::query()->create([
                'company_id' => $companyId,
                'ticket_id' => $resolvedTicket->id,
                'user_id' => $omar->id,
                'action' => 'status_changed',
                'description' => 'Status changed from pending to open.',
                'created_at' => now()->subHours(8),
            ]);

            TicketLog::query()->create([
                'company_id' => $companyId,
                'ticket_id' => $resolvedTicket->id,
                'user_id' => $omar->id,
                'action' => 'status_changed',
                'description' => 'Status changed from open to resolved.',
                'created_at' => now()->subHours(4),
            ]);
        }

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

        $this->command?->info('📋 DEMO SCRIPT — 12 Steps');
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

        $this->command?->line('  4. KEYWORD ASSIGNMENT (NO CATEGORY)');
        $this->command?->line('     Create a ticket with no category and text like "vpn down"');
        $this->command?->line('     → Ticket category is auto-set to Network, then assignment rules route it');
        $this->command?->newLine();

        $this->command?->line('  5. AUTO-REPLY');
        $this->command?->line('     Create any new ticket → Check Mailpit for auto-reply email');
        $this->command?->newLine();

        $this->command?->line('  6. ESCALATION');
        $this->command?->line('     Run: php artisan tickets:process-escalations');
        $this->command?->line('     → "[DEMO] Server response times" escalates (idle 3h > 2h threshold)');
        $this->command?->line('     → Reassigned to Omar + priority bumped');
        $this->command?->newLine();

        $this->command?->line('  7. SLA BREACH');
        $this->command?->line('     Run: php artisan helpdesk:check-sla-breaches');
        $this->command?->line('     → "[DEMO] Invoice #4521" triggers breach automation');
        $this->command?->line('     → Priority escalated + admin notified');
        $this->command?->newLine();

        $this->command?->line('  8. VIEW RULES');
        $this->command?->line('     Log in as admin → Automation page → See all 7 rules');
        $this->command?->line('     Toggle rules on/off to show configurability');
        $this->command?->newLine();

        $this->command?->line('  9. KNOWLEDGE BASE');
        $this->command?->line('     Visit Knowledge Base → 6 published articles across categories');
        $this->command?->line('     Articles include view counts and helpful ratings');
        $this->command?->newLine();

        $this->command?->line(' 10. AI CHATBOT');
        $this->command?->line('     Channels → AI Chatbot Widget → Test Chatbot');
        $this->command?->line('     Try: "hi", "how do I connect to VPN?", "talk to an agent"');
        $this->command?->line('     7 pre-loaded FAQs power the chatbot responses');
        $this->command?->newLine();

        $this->command?->line(' 11. CANNED RESPONSES');
        $this->command?->line('     Open a ticket → Reply → 4 golden responses available');
        $this->command?->line('     Responses are scoped by category (Network, Billing, General)');
        $this->command?->newLine();

        $this->command?->line(' 12. TICKET HISTORY');
        $this->command?->line('     Open "[DEMO] DNS resolution" ticket → See full conversation');
        $this->command?->line('     Includes replies, internal notes, and audit trail');
        $this->command?->newLine();

        $this->command?->info('✅ Seeder complete — 5 users, 7 rules, 4 tickets, 6 KB articles,');
        $this->command?->info('   7 FAQs, 4 canned responses, widget & AI settings configured.');
    }
}
