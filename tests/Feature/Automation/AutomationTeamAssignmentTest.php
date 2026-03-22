<?php

use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->company = Company::factory()->create();
    $this->engine = app(AutomationEngine::class);
});

test('assignment rule with assign_to_team_id assigns ticket to that team', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'team_id' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($operator->id);
    expect($ticket->team_id)->toBe($team->id);
});

test('assignment rule skips team assignment when team not found', function () {
    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => 99999],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBeNull();
});

test('team assignment via automation respects category condition', function () {
    $category = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);
    $otherCategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Hardware',
        'parent_id' => null,
    ]);

    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => $category->id, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    // Ticket with different category
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $otherCategory->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBeNull();
    expect($ticket->team_id)->toBeNull();
});

test('team assignment via automation fires for subcategory of condition category', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software Bugs',
        'parent_id' => $parent->id,
    ]);

    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => $parent->id, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    // Ticket is in subcategory, condition is parent — should still fire
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($operator->id);
    expect($ticket->team_id)->toBe($team->id);
});

test('team assignment does not reassign already assigned ticket', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $teamOperator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($teamOperator->id, ['role' => 'member']);

    $existingOperator = User::factory()->operator()->create(['company_id' => $this->company->id]);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => $existingOperator->id,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($existingOperator->id);
});

test('team assignment respects priority condition', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => ['urgent', 'high']],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    // Low priority ticket — should NOT be assigned to the team
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'priority' => 'low',
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->team_id)->toBeNull();
});

test('assign_to_specialist takes precedence over assign_to_team_id in same rule', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $teamOperator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($teamOperator->id, ['role' => 'member']);

    // Specialist outside the team
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Network']);
    $specialist = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $specialist->categories()->attach($category->id);

    // Ensure specialist is available
    $specialist->update(['status' => 'online']);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => [
            'assign_to_team_id' => $team->id,
            'assign_to_specialist' => true,
        ],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    
    // Should be assigned to the specialist (globally), ignoring the stale team ID
    expect($ticket->team_id)->toBeNull();
    expect($ticket->assigned_to)->toBe($specialist->id);
});

test('team assignment records rule execution count', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    $rule = AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
        'executions_count' => 0,
        'last_executed_at' => null,
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $rule->refresh();
    expect($rule->executions_count)->toBe(1);
    expect($rule->last_executed_at)->not->toBeNull();
});
