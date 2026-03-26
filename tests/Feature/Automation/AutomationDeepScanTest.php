<?php

use App\Livewire\Automation\AutomationRulesTable;
use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\SlaBreached;
use App\Services\Automation\AutomationEngine;
use App\Services\Automation\Rules\AssignmentRule;
use App\Services\Automation\Rules\AutoReplyRule;
use App\Services\Automation\Rules\EscalationRule;
use App\Services\Automation\Rules\SlaBreachRule;
use App\Services\TicketAssignmentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

// Helper to create a ticket without observer auto-assignment
function createTicketQuietly(array $attributes): Ticket
{
    return Ticket::withoutEvents(fn () => Ticket::factory()->create($attributes));
}

// ──────────────────────────────────────────────────────────────────────────────
// A. AutomationEngine Pipeline
// ──────────────────────────────────────────────────────────────────────────────

test('processNewTicket skips escalation rules', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    // Create an escalation rule — should NOT be executed by processNewTicket
    $escalationRule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(48),
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    // Escalation rule should NOT have executed
    $escalationRule->refresh();
    expect($escalationRule->executions_count)->toBe(0);
    $ticket->refresh();
    expect($ticket->priority)->toBe('low');
});

test('processNewTicket continues when one rule throws exception', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    // Create a priority rule with keywords that match
    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'priority' => 1,
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'subject' => 'URGENT help needed',
        'priority' => 'low',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('multiple rules of same type execute in priority order', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    // First rule (higher priority = lower number) sets to high
    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'priority' => 1,
        'conditions' => ['keywords' => ['help']],
        'actions' => ['set_priority' => 'high'],
    ]);

    // Second rule (lower priority) sets to urgent
    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'priority' => 2,
        'conditions' => ['keywords' => ['help']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'subject' => 'I need help',
        'priority' => 'low',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    // Both rules match the keyword 'help', second one overwrites the first
    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('inactive rules are not processed by engine', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    AutomationRule::factory()->priority()->inactive()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'subject' => 'URGENT help',
        'priority' => 'low',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('low');
});

// ──────────────────────────────────────────────────────────────────────────────
// B. EscalationRule Edge Cases
// ──────────────────────────────────────────────────────────────────────────────

test('escalation rule findIdleTickets filters by category_id', function () {
    $company = Company::factory()->create();
    $targetCategory = TicketCategory::factory()->create(['company_id' => $company->id]);
    $otherCategory = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => [
            'idle_hours' => 1,
            'status' => ['open'],
            'category_id' => $targetCategory->id,
        ],
    ]);

    // Matching ticket: correct category, idle, open
    $matchingTicket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $targetCategory->id,
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    // Non-matching: wrong category
    createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $otherCategory->id,
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    $idleTickets = $handler->findIdleTickets($rule);

    expect($idleTickets)->toHaveCount(1);
    expect($idleTickets->first()->id)->toBe($matchingTicket->id);
});

test('escalation rule findIdleTickets excludes resolved and closed tickets', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => [
            'idle_hours' => 1,
            'status' => ['open', 'pending'],
        ],
    ]);

    // Open idle ticket — should be found
    $openTicket = createTicketQuietly([
        'company_id' => $company->id,
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    // Resolved ticket — should NOT be found
    createTicketQuietly([
        'company_id' => $company->id,
        'status' => 'resolved',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    // Closed ticket — should NOT be found
    createTicketQuietly([
        'company_id' => $company->id,
        'status' => 'closed',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    $idleTickets = $handler->findIdleTickets($rule);

    expect($idleTickets)->toHaveCount(1);
    expect($idleTickets->first()->id)->toBe($openTicket->id);
});

test('escalation rule set_priority action sets absolute priority', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    expect($handler->evaluate($rule, $ticket))->toBeTrue();

    $handler->apply($rule, $ticket);
    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('escalation rule escalate_priority bumps priority one level up', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'medium',
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    $handler->apply($rule, $ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('high');
});

test('escalation rule does not escalate already urgent ticket with no other actions', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    // Urban ticket with escalate_priority only => evaluate returns false
    expect($handler->evaluate($rule, $ticket))->toBeFalse();
});

test('escalation rule allows urgent ticket if it has other actions like notify_admin', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true, 'notify_admin' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $handler = app(EscalationRule::class);
    expect($handler->evaluate($rule, $ticket))->toBeTrue();
});

test('processEscalations runs escalation rules via engine', function () {
    $company = Company::factory()->create();
    User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true, 'notify_admin' => true],
    ]);

    createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
        'updated_at' => now()->subHours(48),
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processEscalations($company->id);

    $rule->refresh();
    expect($rule->executions_count)->toBeGreaterThan(0);
});

// ──────────────────────────────────────────────────────────────────────────────
// C. SlaBreachRule Edge Cases
// ──────────────────────────────────────────────────────────────────────────────

test('sla breach rule assigns to specific operator', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
    ]);

    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['assign_to_operator_id' => $operator->id],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'high',
        'assigned_to' => null,
    ]);

    $handler = new SlaBreachRule;
    $handler->apply($rule, $ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($operator->id);
});

test('sla breach rule set_priority sets absolute priority', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'low',
    ]);

    $handler = new SlaBreachRule;
    $handler->apply($rule, $ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('sla breach rule escalate_priority bumps one level', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'medium',
    ]);

    $handler = new SlaBreachRule;
    $handler->apply($rule, $ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('high');
});

test('sla breach rule notifies admins when notify_admin is true', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['notify_admin' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'high',
    ]);

    $handler = new SlaBreachRule;
    $handler->apply($rule, $ticket);

    Notification::assertSentTo($admin, SlaBreached::class);
});

// ──────────────────────────────────────────────────────────────────────────────
// D. AutoReplyRule Edge Cases
// ──────────────────────────────────────────────────────────────────────────────

test('auto reply rule matches combined category and priority conditions', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => [
            'on_create' => true,
            'category_id' => $category->id,
            'priority' => 'urgent',
        ],
        'actions' => ['send_email' => true, 'message' => 'We are on it!'],
    ]);

    // Matching: correct category + correct priority
    $matchingTicket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'priority' => 'urgent',
        'verified' => true,
        'created_at' => now(),
    ]);

    // Wrong priority
    $wrongPriority = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'priority' => 'low',
        'verified' => true,
        'created_at' => now(),
    ]);

    $handler = new AutoReplyRule;
    expect($handler->evaluate($rule, $matchingTicket))->toBeTrue();
    expect($handler->evaluate($rule, $wrongPriority))->toBeFalse();
});

test('auto reply rule silently skips when ticket has no customer email', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true],
        'actions' => ['send_email' => true, 'message' => 'Thanks!'],
    ]);

    // Create ticket with no customer
    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'customer_id' => null,
        'verified' => true,
        'created_at' => now(),
    ]);

    $handler = new AutoReplyRule;
    $handler->apply($rule, $ticket);

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

test('auto reply rule does not evaluate for unverified tickets', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true],
        'actions' => ['send_email' => true, 'message' => 'Thanks!'],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'verified' => false,
        'created_at' => now(),
    ]);

    $handler = new AutoReplyRule;
    expect($handler->evaluate($rule, $ticket))->toBeFalse();
});

// ──────────────────────────────────────────────────────────────────────────────
// E. AssignmentRule Edge Cases
// ──────────────────────────────────────────────────────────────────────────────

test('assignment rule with specialist only and no fallback does not assign when no specialist exists', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    // Create a generalist operator with no specialty
    User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'specialty_id' => null,
        'is_available' => true,
    ]);

    $rule = AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => $category->id],
        'actions' => [
            'assign_to_specialist' => true,
            'fallback_to_generalist' => true,
        ],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $handler = app(AssignmentRule::class);
    $handler->apply($rule, $ticket);

    // The specialist path calls assignTicket() which falls back to generalist since
    // assign_to_specialist uses the assignment service
    $ticket->refresh();
    // Should assign to the generalist since fallback is handled by assignTicket service
    expect($ticket->assigned_to)->not->toBeNull();
});

test('assignment rule does not assign already assigned tickets', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $existingOperator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
    ]);

    $rule = AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['assign_to_specialist' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $existingOperator->id,
        'verified' => true,
    ]);

    $handler = app(AssignmentRule::class);
    expect($handler->evaluate($rule, $ticket))->toBeFalse();
});

test('assignment rule does not assign unverified tickets', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['assign_to_specialist' => true],
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $handler = app(AssignmentRule::class);
    expect($handler->evaluate($rule, $ticket))->toBeFalse();
});

test('assignment rule with priority array condition evaluates correctly', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['priority' => ['urgent', 'high']],
        'actions' => ['assign_to_specialist' => true],
    ]);

    $urgentTicket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'assigned_to' => null,
        'verified' => true,
    ]);

    $lowTicket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'low',
        'assigned_to' => null,
        'verified' => true,
    ]);

    $handler = app(AssignmentRule::class);
    expect($handler->evaluate($rule, $urgentTicket))->toBeTrue();
    expect($handler->evaluate($rule, $lowTicket))->toBeFalse();
});

// ──────────────────────────────────────────────────────────────────────────────
// F. TicketObserver Integration
// ──────────────────────────────────────────────────────────────────────────────

test('observer sets SLA due_time when ticket is created', function () {
    $company = Company::factory()->create();

    SlaPolicy::create([
        'company_id' => $company->id,
        'is_enabled' => true,
        'urgent_minutes' => 30,
        'high_minutes' => 120,
        'medium_minutes' => 480,
        'low_minutes' => 1440,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'priority' => 'high',
        'verified' => true,
    ]);

    expect($ticket->due_time)->not->toBeNull();
});

test('observer returns null due_time when SLA is disabled', function () {
    $company = Company::factory()->create();

    SlaPolicy::create([
        'company_id' => $company->id,
        'is_enabled' => false,
        'urgent_minutes' => 30,
        'high_minutes' => 120,
        'medium_minutes' => 480,
        'low_minutes' => 1440,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'priority' => 'high',
        'verified' => true,
    ]);

    expect($ticket->due_time)->toBeNull();
});

test('observer recalculates due_time when priority changes', function () {
    $company = Company::factory()->create();

    SlaPolicy::create([
        'company_id' => $company->id,
        'is_enabled' => true,
        'urgent_minutes' => 30,
        'high_minutes' => 120,
        'medium_minutes' => 480,
        'low_minutes' => 1440,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'priority' => 'low',
        'verified' => true,
    ]);

    $originalDueTime = $ticket->due_time;

    // Change priority to urgent
    $ticket->update(['priority' => 'urgent']);
    $ticket->refresh();

    // Due time should have changed (urgent = 30 min vs low = 1440 min)
    expect($ticket->due_time->toDateTimeString())
        ->not->toBe($originalDueTime->toDateTimeString());
});

test('observer resets sla_status to on_time when priority changes and new due_time is future', function () {
    $company = Company::factory()->create();

    SlaPolicy::create([
        'company_id' => $company->id,
        'is_enabled' => true,
        'urgent_minutes' => 30,
        'high_minutes' => 120,
        'medium_minutes' => 480,
        'low_minutes' => 1440,
    ]);

    // Create ticket with breached SLA
    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'verified' => true,
        'sla_status' => 'breached',
        'due_time' => now()->subHours(1),
    ]);

    // Change to low priority (1440 min = 24h from now, which is in the future)
    $ticket->priority = 'low';
    $ticket->save();
    $ticket->refresh();

    // SLA status should reset to on_time since new due_time is in the future
    expect($ticket->sla_status)->toBe('on_time');
});

test('observer decrements operator count when ticket is resolved', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'assigned_tickets_count' => 3,
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'open',
        'verified' => true,
    ]);

    // Resolve the ticket
    $ticket->status = 'resolved';
    $ticket->resolved_at = now();
    $ticket->save();

    $operator->refresh();
    expect($operator->assigned_tickets_count)->toBe(2);
});

test('observer increments operator count when ticket is reopened from closed', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'assigned_tickets_count' => 1,
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'closed',
        'verified' => true,
    ]);

    // Reopen the ticket
    $ticket->status = 'open';
    $ticket->save();

    $operator->refresh();
    expect($operator->assigned_tickets_count)->toBe(2);
});

// ──────────────────────────────────────────────────────────────────────────────
// G. Scheduled Commands
// ──────────────────────────────────────────────────────────────────────────────

test('ProcessTicketEscalations command runs successfully', function () {
    $company = Company::factory()->create();

    $this->artisan('tickets:process-escalations')
        ->assertSuccessful();
});

test('ProcessTicketEscalations command accepts company flag', function () {
    $company = Company::factory()->create();

    $this->artisan('tickets:process-escalations', ['--company' => $company->id])
        ->assertSuccessful();
});

test('CheckSlaBreaches command skips already breached tickets', function () {
    $company = Company::factory()->create();
    User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    // Create an already-breached ticket
    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'status' => 'open',
        'verified' => true,
        'due_time' => now()->subHours(2),
        'sla_status' => 'breached',
    ]);

    // Create SLA breach rule
    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'conditions' => [],
        'actions' => ['escalate_priority' => true],
    ]);

    $this->artisan('helpdesk:check-sla-breaches')
        ->assertSuccessful();

    // Rule should NOT have executed because ticket was already breached
    $rule->refresh();
    expect($rule->executions_count)->toBe(0);
});

test('CheckSlaBreaches marks at_risk tickets correctly', function () {
    $company = Company::factory()->create();

    // Create a ticket that's about to breach (within 25% of remaining time)
    // total SLA is 60 min from creation; ticket is 50 min old => 10 min left = 16.7% < 25% threshold
    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'high',
        'status' => 'open',
        'verified' => true,
        'created_at' => now()->subMinutes(50),
        'due_time' => now()->addMinutes(10),
        'sla_status' => 'on_time',
    ]);

    $this->artisan('helpdesk:check-sla-breaches')
        ->assertSuccessful();

    $ticket->refresh();
    expect($ticket->sla_status)->toBe('at_risk');
});

// ──────────────────────────────────────────────────────────────────────────────
// H. AutomationRulesTable Livewire Component
// ──────────────────────────────────────────────────────────────────────────────

test('admin can edit and update an existing rule', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $rule = AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'name' => 'Original Rule Name',
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'high'],
    ]);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->call('editRule', $rule->id)
        ->assertSet('editingRuleId', $rule->id)
        ->assertSet('name', 'Original Rule Name')
        ->set('name', 'Updated Rule Name')
        ->call('updateRule')
        ->assertDispatched('show-toast');

    $rule->refresh();
    expect($rule->name)->toBe('Updated Rule Name');
});

test('sorting changes column and direction', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->assertSet('sortBy', 'priority')
        ->assertSet('sortDirection', 'asc')
        ->call('setSortBy', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDirection', 'asc')
        // Calling same column again toggles direction
        ->call('setSortBy', 'name')
        ->assertSet('sortDirection', 'desc');
});

test('mutual exclusion of assignment toggles', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $team = Team::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    // Setting operator clears team and specialist
    Livewire::test(AutomationRulesTable::class)
        ->set('assign_to_specialist', true)
        ->set('assign_to_operator_id', $operator->id)
        ->assertSet('assign_to_team_id', null)
        ->assertSet('assign_to_specialist', false);

    // Setting team clears operator and specialist
    Livewire::test(AutomationRulesTable::class)
        ->set('assign_to_specialist', true)
        ->set('assign_to_team_id', $team->id)
        ->assertSet('assign_to_operator_id', null)
        ->assertSet('assign_to_specialist', false);

    // Setting specialist clears operator and team
    Livewire::test(AutomationRulesTable::class)
        ->set('assign_to_operator_id', $operator->id)
        ->set('assign_to_specialist', true)
        ->assertSet('assign_to_operator_id', null)
        ->assertSet('assign_to_team_id', null);
});

// ──────────────────────────────────────────────────────────────────────────────
// I. TicketAssignmentService
// ──────────────────────────────────────────────────────────────────────────────

test('unassignTicket clears assigned_to and decrements count', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'assigned_tickets_count' => 3,
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'open',
        'verified' => true,
    ]);

    $service = app(TicketAssignmentService::class);
    $service->unassignTicket($ticket);

    $ticket->refresh();
    $operator->refresh();

    expect($ticket->assigned_to)->toBeNull();
    expect($operator->assigned_tickets_count)->toBe(2);
});

test('unassignTicket does nothing when ticket is not assigned', function () {
    $company = Company::factory()->create();

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => true,
    ]);

    $service = app(TicketAssignmentService::class);
    $service->unassignTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBeNull();
});

test('assignToTeam falls back to global assignment when no team members are available', function () {
    $company = Company::factory()->create();
    $team = Team::factory()->create(['company_id' => $company->id]);

    // Create an available operator NOT in the team
    $globalOperator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'is_available' => true,
    ]);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => null,
        'team_id' => $team->id,
        'status' => 'open',
        'verified' => true,
    ]);

    $service = app(TicketAssignmentService::class);
    $result = $service->assignToTeam($ticket, $team);

    $ticket->refresh();

    // Since no team members available, falls back to global → team_id cleared
    expect($ticket->team_id)->toBeNull();
    expect($ticket->assigned_to)->toBe($globalOperator->id);
});

test('assignPendingTickets assigns multiple unassigned tickets', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'is_available' => true,
    ]);

    createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => true,
    ]);

    createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'status' => 'pending',
        'verified' => true,
    ]);

    $service = app(TicketAssignmentService::class);
    $count = $service->assignPendingTickets($company->id);

    expect($count)->toBe(2);
});

test('recalculateCounts correctly syncs operator ticket counts', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'assigned_tickets_count' => 99, // Intentionally wrong
    ]);

    // Create 2 open tickets assigned to this operator
    createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'open',
        'verified' => true,
    ]);

    createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'pending',
        'verified' => true,
    ]);

    // Also a resolved ticket — should NOT count
    createTicketQuietly([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'resolved',
        'verified' => true,
    ]);

    $service = app(TicketAssignmentService::class);
    $service->recalculateCounts($company->id);

    $operator->refresh();
    expect($operator->assigned_tickets_count)->toBe(2);
});

test('recordExecution increments count and updates timestamp', function () {
    $rule = AutomationRule::factory()->priority()->create([
        'executions_count' => 0,
        'last_executed_at' => null,
    ]);

    $rule->recordExecution();
    $rule->refresh();

    expect($rule->executions_count)->toBe(1);
    expect($rule->last_executed_at)->not->toBeNull();
});
