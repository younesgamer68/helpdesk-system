<?php

use App\Livewire\Tickets\TicketDetails;
use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can assign a ticket to a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id, 'name' => 'Support']);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assignToTeam', $team->id)
        ->assertDispatched('show-toast');

    expect($ticket->fresh()->team_id)->toBe($team->id);
});

test('admin can remove a ticket from a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assignToTeam', null)
        ->assertDispatched('show-toast');

    expect($ticket->fresh()->team_id)->toBeNull();
});

test('assigning same team does nothing', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assignToTeam', $team->id)
        ->assertNotDispatched('show-toast');
});

test('assigning agent with one team auto-sets team_id', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id, 'name' => 'Billing']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach($team->id, ['role' => 'member']);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->open()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => null,
    ]));

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id);

    $fresh = $ticket->fresh();
    expect($fresh->assigned_to)->toBe($operator->id);
    expect($fresh->team_id)->toBe($team->id);
});

test('assigning agent with multiple teams shows team picker when no matching team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $teamA = Team::factory()->create(['company_id' => $company->id, 'name' => 'Billing']);
    $teamB = Team::factory()->create(['company_id' => $company->id, 'name' => 'Technical']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach([$teamA->id => ['role' => 'member'], $teamB->id => ['role' => 'member']]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->open()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => null,
    ]));

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id)
        ->assertSet('showTeamPickerModal', true)
        ->assertSet('pendingAssignAgentId', $operator->id);

    // Ticket should NOT be assigned yet
    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('confirmAssignWithTeam completes assignment for multi-team agent', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $teamA = Team::factory()->create(['company_id' => $company->id, 'name' => 'Billing']);
    $teamB = Team::factory()->create(['company_id' => $company->id, 'name' => 'Technical']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach([$teamA->id => ['role' => 'member'], $teamB->id => ['role' => 'member']]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->open()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => null,
    ]));

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id)
        ->call('confirmAssignWithTeam', $teamB->id)
        ->assertSet('showTeamPickerModal', false)
        ->assertSet('pendingAssignAgentId', null)
        ->assertDispatched('show-toast');

    $fresh = $ticket->fresh();
    expect($fresh->assigned_to)->toBe($operator->id);
    expect($fresh->team_id)->toBe($teamB->id);
});

test('assigning multi-team agent keeps ticket team when agent belongs to it', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $teamA = Team::factory()->create(['company_id' => $company->id, 'name' => 'Billing']);
    $teamB = Team::factory()->create(['company_id' => $company->id, 'name' => 'Technical']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach([$teamA->id => ['role' => 'member'], $teamB->id => ['role' => 'member']]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->open()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $teamA->id,
    ]));

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id)
        ->assertSet('showTeamPickerModal', false)
        ->assertDispatched('show-toast');

    $fresh = $ticket->fresh();
    expect($fresh->assigned_to)->toBe($operator->id);
    expect($fresh->team_id)->toBe($teamA->id);
});

test('cancelAssign clears pending state', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $teamA = Team::factory()->create(['company_id' => $company->id]);
    $teamB = Team::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach([$teamA->id => ['role' => 'member'], $teamB->id => ['role' => 'member']]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->open()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
    ]));

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id)
        ->assertSet('showTeamPickerModal', true)
        ->call('cancelAssign')
        ->assertSet('showTeamPickerModal', false)
        ->assertSet('pendingAssignAgentId', null);

    expect($ticket->fresh()->assigned_to)->toBeNull();
});
