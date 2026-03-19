<?php

use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketAssignmentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->company = Company::factory()->create();
    $this->service = app(TicketAssignmentService::class);
});

test('assigns ticket to least-loaded team member', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $operatorA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 3,
    ]);
    $operatorB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 1,
    ]);

    $team->members()->attach([$operatorA->id => ['role' => 'member'], $operatorB->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    expect($assigned->id)->toBe($operatorB->id);
    expect($ticket->fresh()->team_id)->toBe($team->id);
});

test('prefers specialist team member matching ticket category', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Network']);
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $generalist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $specialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 2,
    ]);
    $specialist->categories()->attach($category->id);

    $team->members()->attach([$generalist->id => ['role' => 'member'], $specialist->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    expect($assigned->id)->toBe($specialist->id);
});

test('prefers specialist matching parent category for subcategory ticket', function () {
    $parent = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Software']);
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
    $parentSpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 1,
    ]);
    $parentSpecialist->categories()->attach($parent->id);

    $team->members()->attach([$generalist->id => ['role' => 'member'], $parentSpecialist->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    // parentSpecialist matches via ancestor_ids [parent.id, subcategory.id]
    expect($assigned->id)->toBe($parentSpecialist->id);
});

test('falls back to least loaded member when no specialist in team', function () {
    $categoryA = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Network']);
    $categoryB = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Hardware']);

    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $operatorA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 3,
    ]);
    $operatorB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 1,
    ]);
    // Both specialize in categoryB, not categoryA
    $operatorA->categories()->attach($categoryB->id);
    $operatorB->categories()->attach($categoryB->id);

    $team->members()->attach([$operatorA->id => ['role' => 'member'], $operatorB->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $categoryA->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    // Falls back to least-loaded: operatorB
    expect($assigned->id)->toBe($operatorB->id);
});

test('falls back to global assignment when no team members available', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Network']);
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    // All team members unavailable
    $unavailable = User::factory()->operator()->unavailable()->create([
        'company_id' => $this->company->id,
        'status' => 'offline',
    ]);
    $team->members()->attach($unavailable->id, ['role' => 'member']);

    // Available operator outside the team
    $globalOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
    ]);
    $globalOperator->categories()->attach($category->id);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    expect($assigned->id)->toBe($globalOperator->id);
    // Team ID cleared when falling back to global
    expect($ticket->fresh()->team_id)->toBeNull();
});

test('skips unavailable team members', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $unavailable = User::factory()->operator()->unavailable()->create([
        'company_id' => $this->company->id,
        'status' => 'offline',
        'assigned_tickets_count' => 0,
    ]);
    $available = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 5,
    ]);

    $team->members()->attach([$unavailable->id => ['role' => 'member'], $available->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    expect($assigned->id)->toBe($available->id);
});

test('skips offline team members', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $offline = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'status' => 'offline',
        'assigned_tickets_count' => 0,
    ]);
    $online = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'status' => 'online',
        'assigned_tickets_count' => 3,
    ]);

    $team->members()->attach([$offline->id => ['role' => 'member'], $online->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    expect($assigned->id)->toBe($online->id);
});

test('skips team members at max load', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $maxedOut = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 20,
    ]);
    $available = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 5,
    ]);

    $team->members()->attach([$maxedOut->id => ['role' => 'member'], $available->id => ['role' => 'member']]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $assigned = $this->service->assignToTeam($ticket, $team);

    expect($assigned->id)->toBe($available->id);
});

test('increments assigned_tickets_count on team assignment', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 2,
    ]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->service->assignToTeam($ticket, $team);

    expect($operator->fresh()->assigned_tickets_count)->toBe(3);
});

test('sets team_id on ticket after team assignment', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $this->service->assignToTeam($ticket, $team);

    $ticket->refresh();
    expect($ticket->team_id)->toBe($team->id);
    expect($ticket->assigned_to)->toBe($operator->id);
});

test('sends notification to assigned team member', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $this->service->assignToTeam($ticket, $team);

    Notification::assertSentTo($operator, \App\Notifications\TicketAssigned::class);
});

test('uses round-robin among equally loaded team members', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $operatorA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
        'last_assigned_at' => now()->subMinutes(5),
    ]);
    $operatorB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
        'last_assigned_at' => now()->subMinutes(10),
    ]);

    $team->members()->attach([$operatorA->id => ['role' => 'member'], $operatorB->id => ['role' => 'member']]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $assigned = $this->service->assignToTeam($ticket, $team);

    // operatorB was assigned longer ago, gets next ticket
    expect($assigned->id)->toBe($operatorB->id);
});

test('team tickets relationship returns assigned tickets', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    Ticket::factory()->create([
        'company_id' => $this->company->id,
        'team_id' => $team->id,
        'assigned_to' => $operator->id,
        'verified' => true,
    ]);

    Ticket::factory()->create([
        'company_id' => $this->company->id,
        'team_id' => $team->id,
        'assigned_to' => $operator->id,
        'verified' => true,
    ]);

    expect($team->tickets)->toHaveCount(2);
});

test('distributes tickets fairly across team members', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);

    $opA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
        'last_assigned_at' => null,
    ]);
    $opB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
        'last_assigned_at' => null,
    ]);

    $team->members()->attach([$opA->id => ['role' => 'member'], $opB->id => ['role' => 'member']]);

    $ticket1 = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => true,
    ]);
    $assigned1 = $this->service->assignToTeam($ticket1, $team);

    $ticket2 = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => true,
    ]);
    $assigned2 = $this->service->assignToTeam($ticket2, $team);

    // Should distribute to different operators
    expect($assigned1->id)->not->toBe($assigned2->id);
});
