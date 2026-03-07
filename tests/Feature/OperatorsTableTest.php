<?php

use App\Livewire\Dashboard\OperatorsTable;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('operators table renders successfully', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(OperatorsTable::class)
        ->assertStatus(200);
});

test('operators table filters by name and email', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    User::factory()->create(['company_id' => $company->id, 'name' => 'John Doe', 'email' => 'john@old.com']);

    $this->actingAs($admin);

    Livewire::test(OperatorsTable::class)
        ->set('search', 'John')
        ->assertSee('John Doe');
});

test('operators table filters by pending vs active statuses', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    User::factory()->create(['company_id' => $company->id, 'name' => 'Pending Guy', 'password' => null]);
    User::factory()->create(['company_id' => $company->id, 'name' => 'Active Guy', 'password' => 'secret']);

    $this->actingAs($admin);

    Livewire::test(OperatorsTable::class)
        ->set('statusFilter', 'pending')
        ->assertSee('Pending Guy')
        ->assertDontSee('Active Guy');
});

test('admins can remove a operator', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $operator = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(OperatorsTable::class)
        ->call('removeUser', $operator->id)
        ->assertDispatched('show-toast');

    $this->assertDatabaseMissing('users', ['id' => $operator->id]);
});

test('non-admins cannot access operators route via middleware', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $tech = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($tech);

    $url = "http://{$company->slug}.".config('app.domain').'/operators';

    $this->get($url)->assertStatus(403);
});

test('operators table handles resending invites', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $pendingOp = User::factory()->create(['company_id' => $company->id, 'password' => null]);

    $this->actingAs($admin);

    \Illuminate\Support\Facades\Mail::fake();

    Livewire::test(OperatorsTable::class)
        ->call('resendInvite', $pendingOp->id)
        ->assertDispatched('show-toast');

    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\UserInvitationMail::class);
});
