<?php

use App\Livewire\Tickets\SlaConfiguration;
use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\SlaBreached;
use App\Services\Automation\Rules\AutoReplyRule;
use App\Services\Automation\Rules\EscalationRule;
use App\Services\Automation\Rules\SlaBreachRule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

// Helper to create a ticket without observer side-effects
function quietTicket(array $attributes): Ticket
{
    return Ticket::withoutEvents(fn () => Ticket::factory()->create($attributes));
}

// ──────────────────────────────────────────────────────────────────────────────
// Bug 1 & 2: EscalationRule subcategory matching
// ──────────────────────────────────────────────────────────────────────────────

test('escalation rule evaluate matches subcategory via parent category condition', function () {
    $company = Company::factory()->create();
    $parent = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => null]);
    $child = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => $parent->id]);

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open'], 'category_id' => $parent->id],
        'actions' => ['escalate_priority' => true],
    ]);

    // Ticket in subcategory should match rule targeting parent
    $ticket = quietTicket([
        'company_id' => $company->id,
        'category_id' => $child->id,
        'status' => 'open',
        'priority' => 'low',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    expect($handler->evaluate($rule, $ticket))->toBeTrue();
});

test('escalation rule findIdleTickets includes subcategory tickets', function () {
    $company = Company::factory()->create();
    $parent = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => null]);
    $child = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => $parent->id]);

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open'], 'category_id' => $parent->id],
    ]);

    // Ticket in parent category
    $parentTicket = quietTicket([
        'company_id' => $company->id,
        'category_id' => $parent->id,
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    // Ticket in subcategory
    $childTicket = quietTicket([
        'company_id' => $company->id,
        'category_id' => $child->id,
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    $idleTickets = $handler->findIdleTickets($rule);

    expect($idleTickets)->toHaveCount(2);
    expect($idleTickets->pluck('id')->toArray())->toContain($parentTicket->id, $childTicket->id);
});

// ──────────────────────────────────────────────────────────────────────────────
// Bug 3: AutoReplyRule subcategory matching
// ──────────────────────────────────────────────────────────────────────────────

test('auto reply rule matches subcategory via parent category condition', function () {
    $company = Company::factory()->create();
    $parent = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => null]);
    $child = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => $parent->id]);

    $rule = AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true, 'category_id' => $parent->id],
        'actions' => ['send_email' => true, 'message' => 'Thanks!'],
    ]);

    $ticket = quietTicket([
        'company_id' => $company->id,
        'category_id' => $child->id,
        'verified' => true,
        'created_at' => now(),
    ]);

    $handler = new AutoReplyRule;
    expect($handler->evaluate($rule, $ticket))->toBeTrue();
});

// ──────────────────────────────────────────────────────────────────────────────
// Bug 4: SlaBreachRule subcategory matching
// ──────────────────────────────────────────────────────────────────────────────

test('sla breach rule matches subcategory via parent category condition', function () {
    $company = Company::factory()->create();
    $parent = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => null]);
    $child = TicketCategory::factory()->create(['company_id' => $company->id, 'parent_id' => $parent->id]);

    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => $parent->id],
        'actions' => ['notify_admin' => true],
    ]);

    $ticket = quietTicket([
        'company_id' => $company->id,
        'category_id' => $child->id,
        'priority' => 'high',
    ]);

    $handler = new SlaBreachRule;
    expect($handler->evaluate($rule, $ticket))->toBeTrue();
});

// ──────────────────────────────────────────────────────────────────────────────
// Bug 5: Double admin notification on SLA breach
// ──────────────────────────────────────────────────────────────────────────────

test('CheckSlaBreaches does not double-notify admins when breach rule has notify_admin', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    // Create a breach rule with notify_admin
    AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['notify_admin' => true],
    ]);

    // Create a ticket that will breach
    quietTicket([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'status' => 'open',
        'verified' => true,
        'due_time' => now()->subHours(1),
        'sla_status' => 'on_time',
    ]);

    Notification::fake();

    $this->artisan('helpdesk:check-sla-breaches')->assertSuccessful();

    // Admin should receive exactly 1 notification, not 2
    Notification::assertSentToTimes($admin, SlaBreached::class, 1);
});

test('CheckSlaBreaches still notifies admins when no breach rule has notify_admin', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    // Create a breach rule WITHOUT notify_admin
    AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['escalate_priority' => true],
    ]);

    quietTicket([
        'company_id' => $company->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
        'due_time' => now()->subHours(1),
        'sla_status' => 'on_time',
    ]);

    Notification::fake();

    $this->artisan('helpdesk:check-sla-breaches')->assertSuccessful();

    // Admin should still get notified via fallback
    Notification::assertSentTo($admin, SlaBreached::class);
});

// ──────────────────────────────────────────────────────────────────────────────
// Bug 6: recordExecution single query
// ──────────────────────────────────────────────────────────────────────────────

test('recordExecution updates count and timestamp in one operation', function () {
    $rule = AutomationRule::factory()->priority()->create([
        'executions_count' => 5,
        'last_executed_at' => null,
    ]);

    $rule->recordExecution();
    $rule->refresh();

    expect($rule->executions_count)->toBe(6);
    expect($rule->last_executed_at)->not->toBeNull();

    // Call again to verify incremental behavior
    $rule->recordExecution();
    $rule->refresh();

    expect($rule->executions_count)->toBe(7);
});

// ──────────────────────────────────────────────────────────────────────────────
// Bug 7: SlaConfiguration recalculate missing at_risk
// ──────────────────────────────────────────────────────────────────────────────

test('SLA recalculation detects at_risk status', function () {
    $company = Company::factory()->create(['timezone' => 'UTC']);
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $policy = SlaPolicy::create([
        'company_id' => $company->id,
        'is_enabled' => true,
        'low_minutes' => 1440,
        'medium_minutes' => 480,
        'high_minutes' => 120,
        'urgent_minutes' => 30,
    ]);

    // Create a ticket that is close to its SLA deadline (within 25% of total time)
    // High = 120min total. If ticket was created 100 min ago, 20 min left = 16.7% < 25% = at_risk
    $ticket = quietTicket([
        'company_id' => $company->id,
        'priority' => 'high',
        'status' => 'open',
        'verified' => true,
        'created_at' => now()->subMinutes(100),
        'due_time' => now()->addMinutes(20),
        'sla_status' => 'on_time',
    ]);

    $this->actingAs($admin);

    Livewire::test(SlaConfiguration::class)
        ->set('is_enabled', true)
        ->set('low_minutes', 1440)
        ->set('medium_minutes', 480)
        ->set('high_minutes', 120)
        ->set('urgent_minutes', 30)
        ->set('warning_hours', 24)
        ->set('auto_close_hours', 48)
        ->set('reopen_hours', 24)
        ->set('linked_ticket_days', 7)
        ->set('soft_delete_days', 30)
        ->set('hard_delete_days', 90)
        ->call('save');

    $ticket->refresh();
    // Ticket created 100min ago with 120min SLA → 20 min left → 16.7% < 25% threshold → at_risk
    expect($ticket->sla_status)->toBe('at_risk');
});
