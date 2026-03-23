<?php

use App\Livewire\Tickets\TicketDetails;
use App\Livewire\Tickets\TicketsTable;
use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Helper to create a ticket without observer auto-assignment
function createTicketQuietly(array $attributes): Ticket
{
    return Ticket::withoutEvents(fn () => Ticket::factory()->create($attributes));
}

// --- Computed Property Tests ---

test('isAssignee returns true for the assigned operator', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $operator->id,
    ]);

    $this->actingAs($operator);

    $component = Livewire::test(TicketDetails::class, ['ticket' => $ticket]);
    expect($component->instance()->isAssignee)->toBeTrue();
    expect($component->instance()->isTeammate)->toBeFalse();
    expect($component->instance()->isOutsider)->toBeFalse();
});

test('isTeammate returns true for operator in same team but not assigned', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $assignee = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate->teams()->attach($team);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $assignee->id,
        'team_id' => $team->id,
    ]);

    $this->actingAs($teammate);

    $component = Livewire::test(TicketDetails::class, ['ticket' => $ticket]);
    expect($component->instance()->isTeammate)->toBeTrue();
    expect($component->instance()->isAssignee)->toBeFalse();
    expect($component->instance()->isOutsider)->toBeFalse();
});

test('isOutsider returns true for operator not in ticket team', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $otherTeam = Team::factory()->create(['company_id' => $company->id]);
    $outsider = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $outsider->teams()->attach($otherTeam);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'assigned_to' => null,
    ]);

    $this->actingAs($outsider);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->assertRedirect();
});

test('admin is never flagged as assignee teammate or outsider', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(TicketDetails::class, ['ticket' => $ticket]);
    expect($component->instance()->isAssignee)->toBeFalse();
    expect($component->instance()->isTeammate)->toBeFalse();
    expect($component->instance()->isOutsider)->toBeFalse();
});

// --- Outsider Redirect Test ---

test('outsider operator is redirected from ticket details', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    // operator has no teams

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'assigned_to' => null,
    ]);

    $this->actingAs($operator);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->assertRedirect();
});

// --- Team Tab Query Test ---

test('team view shows tickets from operator teams', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach($team);

    $teamTicket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'verified' => true,
    ]);

    $otherTicket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => null,
        'verified' => true,
    ]);

    $this->actingAs($operator);

    $component = Livewire::test(TicketsTable::class)
        ->call('setTicketView', 'team');

    $tickets = $component->instance()->tickets;
    expect($tickets->pluck('id'))->toContain($teamTicket->id);
    expect($tickets->pluck('id'))->not->toContain($otherTicket->id);
});

test('team view returns empty when operator has no teams', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($operator);

    $component = Livewire::test(TicketsTable::class)
        ->call('setTicketView', 'team');

    expect($component->instance()->tickets)->toHaveCount(0);
});

// --- Take Ticket Tests ---

test('operator can take unassigned ticket from their team', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach($team);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => true,
    ]);

    $this->actingAs($operator);

    Livewire::test(TicketsTable::class)
        ->call('setTicketView', 'team')
        ->call('takeTicket', $ticket->id)
        ->assertDispatched('show-toast');

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($operator->id);
    expect($ticket->status)->toBe('in_progress');
});

test('operator cannot take ticket from different team', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $otherTeam = Team::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach($otherTeam);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $this->actingAs($operator);

    Livewire::test(TicketsTable::class)
        ->call('takeTicket', $ticket->id)
        ->assertDispatched('show-toast', message: 'You cannot take this ticket.', type: 'error');

    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('takeTicket creates a ticket log entry', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $operator->teams()->attach($team);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    $this->actingAs($operator);

    Livewire::test(TicketsTable::class)
        ->call('takeTicket', $ticket->id);

    $this->assertDatabaseHas('ticket_logs', [
        'ticket_id' => $ticket->id,
        'user_id' => $operator->id,
        'action' => 'self_assigned',
    ]);
});

// --- Sidebar Role Restrictions ---

test('teammate sees read-only sidebar without action buttons', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $assignee = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $teammate->teams()->attach($team);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $assignee->id,
        'team_id' => $team->id,
    ]);

    $this->actingAs($teammate);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->assertDontSee('Change Priority')
        ->assertDontSee('Change Status')
        ->assertDontSee('Close ticket');
});

test('assignee can see priority and status dropdowns', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $ticket = createTicketQuietly([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => $operator->id,
    ]);

    $this->actingAs($operator);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->assertSee('Change Priority')
        ->assertSee('Change Status')
        ->assertDontSee('Close ticket');
});
