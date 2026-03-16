<?php

use App\Mail\AutoReplyMail;
use App\Mail\EscalationNotificationMail;
use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use Illuminate\Support\Facades\Mail;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

test('automation engine applies assignment rule to new ticket', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'specialty_id' => $category->id,
        'is_available' => true,
    ]);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => $category->id, 'priority' => null],
        'actions' => ['assign_to_specialist' => true, 'fallback_to_generalist' => true],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'verified' => true,
        'assigned_to' => null,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($operator->id);
});

test('automation engine applies priority rule based on keywords', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['urgent', 'emergency']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'URGENT: Server is down',
        'description' => 'Please help immediately',
        'priority' => 'medium',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('automation engine does not apply priority rule without matching keywords', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['urgent', 'emergency']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'Normal Support Request',
        'description' => 'I need help with something',
        'priority' => 'medium',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('medium');
});

test('automation engine sends auto reply email', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true],
        'actions' => [
            'send_email' => true,
            'subject' => 'Thank you',
            'message' => 'We received your ticket.',
        ],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'verified' => true,
        'created_at' => now(),
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    Mail::assertQueued(AutoReplyMail::class, function ($mail) use ($ticket) {
        return $mail->ticket->id === $ticket->id;
    });
});

test('automation engine skips inactive rules', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->priority()->inactive()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'URGENT: Need help',
        'priority' => 'low',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('low');
});

test('automation engine processes rules in priority order', function () {
    $company = Company::factory()->create();

    // Higher priority rule (lower number = first)
    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'priority' => 1,
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'high'],
    ]);

    // Lower priority rule
    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'priority' => 2,
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'URGENT matter',
        'priority' => 'low',
        'verified' => true,
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    // First rule sets to 'high', then second rule sets to 'urgent'
    expect($ticket->priority)->toBe('urgent');
});

test('automation engine records rule execution', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['test']],
        'actions' => ['set_priority' => 'high'],
        'executions_count' => 0,
        'last_executed_at' => null,
    ]);

    // Create ticket without triggering observer to test engine directly
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'This is a test',
        'priority' => 'low',
        'verified' => true,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $rule->refresh();
    expect($rule->executions_count)->toBe(1);
    expect($rule->last_executed_at)->not->toBeNull();
});

test('escalation rule finds idle tickets', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => [
            'idle_hours' => 2,
            'status' => ['pending', 'open'],
        ],
    ]);

    // Idle ticket (updated 3 hours ago)
    $idleTicket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'verified' => true,
        'updated_at' => now()->subHours(3),
    ]);

    // Recent ticket
    Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'verified' => true,
        'updated_at' => now()->subMinutes(30),
    ]);

    $escalationRule = app(\App\Services\Automation\Rules\EscalationRule::class);
    $idleTickets = $escalationRule->findIdleTickets($rule);

    expect($idleTickets)->toHaveCount(1);
    expect($idleTickets->first()->id)->toBe($idleTicket->id);
});

test('escalation rule notifies admin', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => [
            'idle_hours' => 1,
            'status' => ['pending'],
        ],
        'actions' => [
            'escalate_priority' => true,
            'notify_admin' => true,
        ],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'priority' => 'low',
        'verified' => true,
        'updated_at' => now()->subHours(2),
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processEscalations($company->id);

    Mail::assertQueued(EscalationNotificationMail::class);

    $ticket->refresh();
    expect($ticket->priority)->toBe('medium'); // Escalated from low
});

// ========================================================================
// AssignmentRule — Edge Cases
// ========================================================================

test('assignment rule does not reassign already assigned ticket', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $existingOperator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
    ]);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_specialist' => true],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'verified' => true,
        'assigned_to' => $existingOperator->id,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($existingOperator->id);
});

test('assignment rule does not assign unverified ticket', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_specialist' => true],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'verified' => false,
        'assigned_to' => null,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBeNull();
});

test('assignment rule assigns to specific operator by id', function () {
    $company = Company::factory()->create();
    $specificOperator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
    ]);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_operator_id' => $specificOperator->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'verified' => true,
        'assigned_to' => null,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($specificOperator->id);
});

test('assignment rule skips ticket with non-matching category', function () {
    $company = Company::factory()->create();
    $targetCategory = TicketCategory::factory()->create(['company_id' => $company->id]);
    $otherCategory = TicketCategory::factory()->create(['company_id' => $company->id]);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $company->id,
        'conditions' => ['category_id' => $targetCategory->id, 'priority' => null],
        'actions' => ['assign_to_specialist' => true],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $otherCategory->id,
        'verified' => true,
        'assigned_to' => null,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBeNull();
});

// ========================================================================
// PriorityRule — Edge Cases
// ========================================================================

test('priority rule matches keyword in description not just subject', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['critical']],
        'actions' => ['set_priority' => 'high'],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'Normal support request',
        'description' => 'This is a critical issue affecting all users',
        'priority' => 'low',
        'verified' => true,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('high');
});

test('priority rule only applies when current_priority matches', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'conditions' => [
            'keywords' => ['urgent'],
            'current_priority' => ['low'],
        ],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    // Ticket with medium priority should NOT match
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'URGENT: Problem',
        'priority' => 'medium',
        'verified' => true,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('medium'); // Not changed
});

test('priority rule ignores invalid priority value', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->priority()->create([
        'company_id' => $company->id,
        'conditions' => ['keywords' => ['test']],
        'actions' => ['set_priority' => 'invalid_priority'],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'This is a test',
        'priority' => 'low',
        'verified' => true,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('low'); // Unchanged
});

// ========================================================================
// AutoReplyRule — Edge Cases
// ========================================================================

test('auto reply rule does not send email for unverified ticket', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true],
        'actions' => ['send_email' => true, 'message' => 'Thank you'],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'verified' => false,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    Mail::assertNothingQueued();
});

test('auto reply rule does nothing when send_email is false', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true],
        'actions' => ['send_email' => false, 'message' => 'Thank you'],
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'verified' => true,
        'created_at' => now(),
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    Mail::assertNotQueued(AutoReplyMail::class);
});

test('auto reply rule filters by priority condition', function () {
    $company = Company::factory()->create();

    AutomationRule::factory()->autoReply()->create([
        'company_id' => $company->id,
        'conditions' => ['on_create' => true, 'priority' => ['urgent', 'high']],
        'actions' => ['send_email' => true, 'message' => 'Priority acknowledgment'],
    ]);

    // Low priority ticket should NOT get auto reply
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'priority' => 'low',
        'verified' => true,
        'created_at' => now(),
    ]);

    $engine = app(AutomationEngine::class);
    $engine->processNewTicket($ticket);

    Mail::assertNotQueued(AutoReplyMail::class);
});

// ========================================================================
// EscalationRule — Edge Cases
// ========================================================================

test('escalation rule increments priority by one level', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
        'priority' => 'low',
        'verified' => true,
        'updated_at' => now()->subHours(3),
    ]));

    $engine = app(AutomationEngine::class);
    $engine->executeRule($rule, $ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('medium'); // low → medium
});

test('escalation rule does not escalate already urgent ticket', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['pending']],
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'priority' => 'urgent',
        'verified' => true,
        'updated_at' => now()->subHours(3),
    ]));

    $engine = app(AutomationEngine::class);
    $result = $engine->executeRule($rule, $ticket);

    expect($result)->toBeFalse();
    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent'); // Still urgent
});

test('escalation rule reassigns ticket to specific operator', function () {
    $company = Company::factory()->create();
    $newOperator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
    ]);

    $rule = AutomationRule::factory()->escalation()->create([
        'company_id' => $company->id,
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['reassign' => true, 'reassign_to_operator_id' => $newOperator->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
        'priority' => 'medium',
        'verified' => true,
        'assigned_to' => null,
        'updated_at' => now()->subHours(3),
    ]));

    $engine = app(AutomationEngine::class);
    $engine->executeRule($rule, $ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($newOperator->id);
});

// ========================================================================
// SlaBreachRule & CheckSlaBreaches Command
// ========================================================================

test('sla breach rule escalates ticket priority', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->slaBreach()->create([
        'company_id' => $company->id,
        'actions' => ['escalate_priority' => true],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'priority' => 'medium',
        'sla_status' => 'breached',
        'verified' => true,
    ]));

    $engine = app(AutomationEngine::class);
    $engine->executeRule($rule, $ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('high'); // medium → high
});

test('check sla breaches command marks overdue tickets as breached', function () {
    $company = Company::factory()->create();

    // Ticket overdue — should be marked as breached
    $overdueTicket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
        'sla_status' => 'on_time',
        'due_time' => now()->subHour(),
        'verified' => true,
    ]));

    // Ticket NOT overdue — should stay on_time
    $okTicket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
        'sla_status' => 'on_time',
        'due_time' => now()->addHours(2),
        'verified' => true,
    ]));

    $this->artisan('helpdesk:check-sla-breaches')
        ->assertSuccessful();

    $overdueTicket->refresh();
    $okTicket->refresh();

    expect($overdueTicket->sla_status)->toBe('breached');
    expect($okTicket->sla_status)->toBe('on_time');
});
