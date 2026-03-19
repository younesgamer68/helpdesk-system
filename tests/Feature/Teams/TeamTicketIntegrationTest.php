<?php

use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use App\Services\TicketAssignmentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->company = Company::factory()->create();
    $this->service = app(TicketAssignmentService::class);
    $this->engine = app(AutomationEngine::class);
});

test('automation rule assigns ticket to team with subcategory specialist', function () {
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

    $generalist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $subcategorySpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 2,
    ]);
    $subcategorySpecialist->categories()->attach($subcategory->id);

    $team->members()->attach([
        $generalist->id => ['role' => 'member'],
        $subcategorySpecialist->id => ['role' => 'member'],
    ]);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->team_id)->toBe($team->id);
    expect($ticket->assigned_to)->toBe($subcategorySpecialist->id);
});

test('priority and assignment rules both fire in order for the same ticket', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    // Priority rule (fires first — lower priority number)
    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'priority' => 1,
        'conditions' => ['keywords' => ['urgent']],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    // Assignment rule (fires second)
    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'priority' => 2,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'subject' => 'URGENT: Production down',
        'priority' => 'low',
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
    expect($ticket->team_id)->toBe($team->id);
    expect($ticket->assigned_to)->toBe($operator->id);
});

test('team member receives notification when assigned via automation', function () {
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
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    Notification::assertSentTo($operator, \App\Notifications\TicketAssigned::class);
});

test('subcategory ticket with no team members triggers global specialist assignment', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network Config',
        'parent_id' => $parent->id,
    ]);

    $team = Team::factory()->create(['company_id' => $this->company->id]);
    // No members in the team

    // Global specialist (not in team)
    $specialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $specialist->categories()->attach($parent->id);

    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    // Falls back to global assignment (parent specialist found)
    expect($ticket->assigned_to)->toBe($specialist->id);
    expect($ticket->team_id)->toBeNull();
});

test('team assignment with priority escalation via two automation rules', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Security',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Security Breach',
        'parent_id' => $parent->id,
    ]);

    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    // Priority rule triggered by parent category
    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'priority' => 1,
        'conditions' => ['keywords' => ['breach'], 'category_id' => $parent->id],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    // Team assignment rule without category restriction
    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'priority' => 2,
        'conditions' => ['category_id' => null, 'priority' => null],
        'actions' => ['assign_to_team_id' => $team->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'subject' => 'Possible security breach detected',
        'priority' => 'low',
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
    expect($ticket->team_id)->toBe($team->id);
    expect($ticket->assigned_to)->toBe($operator->id);
});

test('recalculate counts reflects team assignment correctly', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    // Manually create assigned tickets
    Ticket::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'team_id' => $team->id,
        'assigned_to' => $operator->id,
        'status' => 'open',
        'verified' => true,
    ]);

    // Corrupt the counter
    $operator->update(['assigned_tickets_count' => 99]);

    $this->service->recalculateCounts($this->company->id);

    expect($operator->fresh()->assigned_tickets_count)->toBe(3);
});

test('unassigning a ticket clears team assignment counter', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 3,
    ]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'team_id' => $team->id,
        'assigned_to' => $operator->id,
        'verified' => true,
    ]);

    $this->service->unassignTicket($ticket);

    expect($ticket->fresh()->assigned_to)->toBeNull();
    expect($operator->fresh()->assigned_tickets_count)->toBe(2);
});
