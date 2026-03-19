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
