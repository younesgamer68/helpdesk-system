<?php

use App\Livewire\Operators\TeamsTable;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->admin = User::factory()->admin()->create(['company_id' => $this->company->id]);
});

test('admin can create a team', function () {
    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->set('name', 'Support Team')
        ->set('description', 'Handles support tickets')
        ->set('color', '#14b8a6')
        ->call('createTeam');

    $this->assertDatabaseHas('teams', [
        'company_id' => $this->company->id,
        'name' => 'Support Team',
        'description' => 'Handles support tickets',
        'color' => '#14b8a6',
    ]);
});

test('team name is required', function () {
    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->set('name', '')
        ->call('createTeam')
        ->assertHasErrors(['name' => 'required']);
});

test('team name must be unique within company', function () {
    Team::factory()->create(['company_id' => $this->company->id, 'name' => 'Alpha Team']);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->set('name', 'Alpha Team')
        ->call('createTeam')
        ->assertHasErrors(['name']);
});

test('same team name allowed in different companies', function () {
    $otherCompany = Company::factory()->create();
    Team::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Alpha Team']);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->set('name', 'Alpha Team')
        ->call('createTeam')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teams', [
        'company_id' => $this->company->id,
        'name' => 'Alpha Team',
    ]);
});

test('admin can update a team', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id, 'name' => 'Old Name']);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->call('editTeam', $team->id)
        ->set('name', 'New Name')
        ->set('description', 'Updated description')
        ->call('updateTeam');

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'New Name',
        'description' => 'Updated description',
    ]);
});

test('admin can delete a team and detach members', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->call('confirmDelete', $team->id)
        ->call('deleteTeam');

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $operator->id]);
});

test('admin can add member to team', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->call('manageMembers', $team->id)
        ->set('addMemberId', $operator->id)
        ->set('addMemberRole', 'member')
        ->call('addMember');

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $operator->id,
        'role' => 'member',
    ]);
});

test('admin can add member as team lead', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->call('manageMembers', $team->id)
        ->set('addMemberId', $operator->id)
        ->set('addMemberRole', 'lead')
        ->call('addMember');

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $operator->id,
        'role' => 'lead',
    ]);
});

test('admin can remove member from team', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->call('manageMembers', $team->id)
        ->call('removeMember', $operator->id);

    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $operator->id,
    ]);
});

test('admin can toggle member role between member and lead', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    Livewire::actingAs($this->admin)
        ->test(TeamsTable::class)
        ->call('manageMembers', $team->id)
        ->call('toggleMemberRole', $operator->id);

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $operator->id,
        'role' => 'lead',
    ]);
});

test('operator can belong to multiple teams', function () {
    $teamA = Team::factory()->create(['company_id' => $this->company->id, 'name' => 'Team A']);
    $teamB = Team::factory()->create(['company_id' => $this->company->id, 'name' => 'Team B']);
    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);

    $teamA->members()->attach($operator->id, ['role' => 'member']);
    $teamB->members()->attach($operator->id, ['role' => 'lead']);

    expect($operator->teams)->toHaveCount(2);
    $this->assertDatabaseHas('team_user', ['team_id' => $teamA->id, 'user_id' => $operator->id, 'role' => 'member']);
    $this->assertDatabaseHas('team_user', ['team_id' => $teamB->id, 'user_id' => $operator->id, 'role' => 'lead']);
});

test('team only shows within same company scope', function () {
    $otherCompany = Company::factory()->create();
    Team::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Other Company Team']);
    $ownTeam = Team::factory()->create(['company_id' => $this->company->id, 'name' => 'Own Team']);

    $livewire = Livewire::actingAs($this->admin)->test(TeamsTable::class);

    // Verify only own company teams are returned by the computed property
    expect($livewire->get('teams'))->toHaveCount(1);
    expect($livewire->get('teams')->first()->name)->toBe('Own Team');
});

test('team leads relationship returns only leads', function () {
    $team = Team::factory()->create(['company_id' => $this->company->id]);
    $lead = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $member = User::factory()->operator()->create(['company_id' => $this->company->id]);

    $team->members()->attach($lead->id, ['role' => 'lead']);
    $team->members()->attach($member->id, ['role' => 'member']);

    expect($team->leads)->toHaveCount(1);
    expect($team->leads->first()->id)->toBe($lead->id);
    expect($team->members)->toHaveCount(2);
});
