<?php

use App\Livewire\Automation\AutomationRulesTable;
use App\Livewire\Tickets\TicketDetails;
use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Automation\Rules\AutoReplyRule;
use App\Services\Automation\Rules\EscalationRule;
use App\Services\Automation\Rules\SlaBreachRule;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Helper to create a ticket without observer auto-assignment
function makeTicketQuietly(array $attributes): Ticket
{
    return Ticket::withoutEvents(fn () => Ticket::factory()->create($attributes));
}

// ──────────────────────────────────────────────────────────────────────────────
// Phase 1: Automation Engine Fixes
// ──────────────────────────────────────────────────────────────────────────────

test('sla breach rule reads category_id from conditions not model property', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $otherCategory = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'sla_breach',
        'conditions' => ['category_id' => $category->id],
        'actions' => ['notify_admin' => true],
    ]);

    $matchingTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'priority' => 'high',
    ]);

    $nonMatchingTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $otherCategory->id,
        'priority' => 'high',
    ]);

    $slaBreachRule = new SlaBreachRule;

    expect($slaBreachRule->evaluate($rule, $matchingTicket))->toBeTrue();
    expect($slaBreachRule->evaluate($rule, $nonMatchingTicket))->toBeFalse();
});

test('sla breach rule with no category condition applies to all tickets', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'sla_breach',
        'conditions' => [],
        'actions' => ['notify_admin' => true],
    ]);

    $ticket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'priority' => 'medium',
    ]);

    $slaBreachRule = new SlaBreachRule;
    expect($slaBreachRule->evaluate($rule, $ticket))->toBeTrue();
});

test('sla breach conditions are saved via buildConditions', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', 'SLA Breach Category Filter')
        ->set('type', 'sla_breach')
        ->set('category_id', $category->id)
        ->set('notify_admin', true)
        ->call('createRule')
        ->assertDispatched('show-toast');

    $rule = AutomationRule::where('name', 'SLA Breach Category Filter')->first();
    expect($rule)->not->toBeNull();
    expect($rule->conditions['category_id'])->toBe($category->id);
    expect($rule->actions['notify_admin'])->toBeTrue();
});

test('auto reply rule respects 5-minute creation window', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'auto_reply',
        'conditions' => ['on_create' => true],
        'actions' => ['send_email' => true, 'message' => 'Thanks!'],
    ]);

    // Ticket created 3 minutes ago — should match
    $recentTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'verified' => true,
        'created_at' => now()->subMinutes(3),
    ]);

    // Ticket created 10 minutes ago — should NOT match
    $oldTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'verified' => true,
        'created_at' => now()->subMinutes(10),
    ]);

    $autoReplyRule = new AutoReplyRule;

    expect($autoReplyRule->evaluate($rule, $recentTicket))->toBeTrue();
    expect($autoReplyRule->evaluate($rule, $oldTicket))->toBeFalse();
});

test('auto reply rule uses strict category comparison', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'auto_reply',
        'conditions' => ['category_id' => $category->id],
        'actions' => ['send_email' => true, 'message' => 'Thanks!'],
    ]);

    $matchingTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'verified' => true,
    ]);

    $nonMatchingTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => null,
        'verified' => true,
    ]);

    $autoReplyRule = new AutoReplyRule;

    expect($autoReplyRule->evaluate($rule, $matchingTicket))->toBeTrue();
    expect($autoReplyRule->evaluate($rule, $nonMatchingTicket))->toBeFalse();
});

test('escalation rule uses strict category comparison', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'escalation',
        'conditions' => [
            'category_id' => $category->id,
            'idle_hours' => 1,
            'status' => ['pending', 'open'],
        ],
        'actions' => ['escalate_priority' => true, 'notify_admin' => true],
    ]);

    $matchingTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'status' => 'open',
        'priority' => 'low',
        'updated_at' => now()->subHours(2),
    ]);

    $nonMatchingTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => null,
        'status' => 'open',
        'priority' => 'low',
        'updated_at' => now()->subHours(2),
    ]);

    $escalationRule = new EscalationRule;

    expect($escalationRule->evaluate($rule, $matchingTicket))->toBeTrue();
    expect($escalationRule->evaluate($rule, $nonMatchingTicket))->toBeFalse();
});

test('escalation rule allows urgent tickets when other actions exist', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'escalation',
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true, 'notify_admin' => true],
    ]);

    $urgentTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'status' => 'open',
        'updated_at' => now()->subHours(2),
    ]);

    $escalationRule = new EscalationRule;

    // Should evaluate true because notify_admin is an additional action
    expect($escalationRule->evaluate($rule, $urgentTicket))->toBeTrue();
});

test('escalation rule blocks urgent tickets when escalate_priority is only action', function () {
    $company = Company::factory()->create();

    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'type' => 'escalation',
        'conditions' => ['idle_hours' => 1, 'status' => ['open']],
        'actions' => ['escalate_priority' => true],
    ]);

    $urgentTicket = makeTicketQuietly([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'status' => 'open',
        'updated_at' => now()->subHours(2),
    ]);

    $escalationRule = new EscalationRule;

    // Should evaluate false — no point escalating urgent with no other actions
    expect($escalationRule->evaluate($rule, $urgentTicket))->toBeFalse();
});

test('auto reply rule validation requires email message', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', 'Empty Message Rule')
        ->set('type', 'auto_reply')
        ->set('email_message', '')
        ->call('createRule')
        ->assertHasErrors(['email_message']);
});

test('escalation rule validation requires idle_hours', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', 'Bad Escalation Rule')
        ->set('type', 'escalation')
        ->set('idle_hours', 0)
        ->call('createRule')
        ->assertHasErrors(['idle_hours']);
});

// ──────────────────────────────────────────────────────────────────────────────
// Phase 2: Security / Authorization Fixes
// ──────────────────────────────────────────────────────────────────────────────

test('non-assignee operator cannot change ticket priority', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $assignee = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate->teams()->attach($team);

    $ticket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $assignee->id,
        'team_id' => $team->id,
        'priority' => 'low',
    ]);

    $this->actingAs($teammate);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('changePriority', 'high')
        ->assertDispatched('show-toast', fn ($name, $params) => $params['type'] === 'error');

    $ticket->refresh();
    expect($ticket->priority)->toBe('low');
});

test('assignee operator can change ticket priority', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $ticket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $operator->id,
        'priority' => 'low',
    ]);

    $this->actingAs($operator);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('changePriority', 'high')
        ->assertDispatched('show-toast', fn ($name, $params) => $params['type'] === 'success');

    $ticket->refresh();
    expect($ticket->priority)->toBe('high');
});

test('admin can change ticket priority regardless of assignment', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $ticket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'priority' => 'low',
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('changePriority', 'urgent')
        ->assertDispatched('show-toast', fn ($name, $params) => $params['type'] === 'success');

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('non-assignee operator cannot change ticket status', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $assignee = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate->teams()->attach($team);

    $ticket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $assignee->id,
        'team_id' => $team->id,
        'status' => 'open',
    ]);

    $this->actingAs($teammate);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('changeStatus', 'resolved')
        ->assertDispatched('show-toast', fn ($name, $params) => $params['type'] === 'error');

    $ticket->refresh();
    expect($ticket->status)->toBe('open');
});

test('non-assignee operator cannot add reply to ticket', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $assignee = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate->teams()->attach($team);

    $ticket = makeTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $assignee->id,
        'team_id' => $team->id,
        'status' => 'open',
    ]);

    $this->actingAs($teammate);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->set('message', 'Unauthorized reply attempt')
        ->call('addReply')
        ->assertDispatched('show-toast', fn ($name, $params) => $params['type'] === 'error');

    expect(\App\Models\TicketReply::where('ticket_id', $ticket->id)->count())->toBe(0);
});

// ──────────────────────────────────────────────────────────────────────────────
// Phase 5: Company Model
// ──────────────────────────────────────────────────────────────────────────────

test('company has teams relationship', function () {
    $company = Company::factory()->create();
    $team = Team::factory()->create(['company_id' => $company->id]);

    expect($company->teams)->toHaveCount(1);
    expect($company->teams->first()->id)->toBe($team->id);
});
