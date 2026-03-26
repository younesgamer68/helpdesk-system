<?php

use App\Livewire\Operators\OperatorProfile;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('operator profile page renders for admins', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorProfile::class, ['operator' => $operator])
        ->assertStatus(200)
        ->assertSee($operator->name)
        ->assertSee($operator->email);
});

test('operator profile allows updating specialties', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id]);
    $category1 = TicketCategory::factory()->create(['company_id' => $company->id]);
    $category2 = TicketCategory::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorProfile::class, ['operator' => $operator])
        ->set('selectedCategories', [$category1->id, $category2->id])
        ->call('updateSpecialties')
        ->assertDispatched('show-toast');

    expect($operator->fresh()->categories)->toHaveCount(2);
});

test('operator profile allows updating role', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorProfile::class, ['operator' => $operator])
        ->set('role', 'admin')
        ->call('updateRole')
        ->assertDispatched('show-toast');

    expect($operator->fresh()->role)->toBe('admin');
});

test('removing operator reassigns tickets to unassigned', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => 'open',
    ]);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorProfile::class, ['operator' => $operator])
        ->call('removeOperator')
        ->assertDispatched('show-toast');

    expect($ticket->fresh()->assigned_to)->toBeNull();
    $this->assertDatabaseMissing('users', ['id' => $operator->id]);
});

test('operator availability can be toggled', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator', 'is_available' => true]);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorProfile::class, ['operator' => $operator])
        ->call('toggleAvailability');

    expect($operator->fresh()->is_available)->toBeFalse();

    Livewire::test(OperatorProfile::class, ['operator' => $operator])
        ->call('toggleAvailability');

    expect($operator->fresh()->is_available)->toBeTrue();
});
