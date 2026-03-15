<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders the customers page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/customers')
        ->assertSuccessful()
        ->assertSee('Customers');
});

it('renders the customer details page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);
    $customer = Customer::query()->create([
        'company_id' => $company->id,
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'phone' => '123456789',
        'is_active' => true,
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain')."/customers/{$customer->id}")
        ->assertSuccessful();
});

it('renders the kb articles page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/articles')
        ->assertSuccessful()
        ->assertSee('Knowledge Base');
});

it('renders the kb categories page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/categories')
        ->assertSuccessful()
        ->assertSee('Knowledge Base');
});

it('renders the automation page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/automation')
        ->assertSuccessful()
        ->assertSee('Automation');
});

it('renders the sla policy page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/automation/sla-policy')
        ->assertSuccessful()
        ->assertSee('Automation')
        ->assertSee('SLA Policy');
});
