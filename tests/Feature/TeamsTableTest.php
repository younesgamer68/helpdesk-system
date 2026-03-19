<?php

use App\Livewire\Operators\TeamsTable;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('teams table renders successfully', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->assertStatus(200);
});

test('teams table displays teams', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    Team::factory()->create(['company_id' => $company->id, 'name' => 'Support Team']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->assertSee('Support Team');
});

test('admin can create a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->set('name', 'Engineering')
        ->set('description', 'Engineering team')
        ->set('color', '#3B82F6')
        ->call('createTeam')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('teams', [
        'company_id' => $company->id,
        'name' => 'Engineering',
        'description' => 'Engineering team',
        'color' => '#3B82F6',
    ]);
});

test('admin can edit a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $team = Team::factory()->create(['company_id' => $company->id, 'name' => 'Old Name']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->call('editTeam', $team->id)
        ->set('name', 'New Name')
        ->set('description', 'Updated')
        ->call('updateTeam')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'New Name',
        'description' => 'Updated',
    ]);
});

test('admin can delete a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $team = Team::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->call('confirmDelete', $team->id)
        ->call('deleteTeam')
        ->assertDispatched('show-toast');

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('admin can add a member to a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $team = Team::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->call('manageMembers', $team->id)
        ->set('addMemberId', $operator->id)
        ->call('addMember')
        ->assertDispatched('show-toast');

    expect($team->members()->where('user_id', $operator->id)->exists())->toBeTrue();
});

test('admin can remove a member from a team', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $team->members()->attach($operator->id, ['role' => 'member']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->call('manageMembers', $team->id)
        ->call('removeMember', $operator->id)
        ->assertDispatched('show-toast');

    expect($team->members()->where('user_id', $operator->id)->exists())->toBeFalse();
});

test('teams only show for current company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company1->id, 'role' => 'admin']);
    Team::factory()->create(['company_id' => $company1->id, 'name' => 'My Team']);
    Team::factory()->create(['company_id' => $company2->id, 'name' => 'Other Team']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->assertSee('My Team')
        ->assertDontSee('Other Team');
});

test('team name is required', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->set('name', '')
        ->call('createTeam')
        ->assertHasErrors(['name']);
});

test('team search filters results', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    Team::factory()->create(['company_id' => $company->id, 'name' => 'Alpha']);
    Team::factory()->create(['company_id' => $company->id, 'name' => 'Beta']);

    $this->actingAs($admin);

    Livewire::test(TeamsTable::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha')
        ->assertDontSee('Beta');
});
