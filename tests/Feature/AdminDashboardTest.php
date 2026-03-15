<?php

use App\Livewire\Dashboard\AdminDashboard;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders the dashboard for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $response = actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/admin/dashboard')
        ->assertOk();
});

it('shows key elements on the dashboard', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    Livewire::actingAs($user)
        ->test(AdminDashboard::class)
        ->assertSee('Admin Dashboard')
        ->assertSee('Open Tickets')
        ->assertSee('Resolved Today')
        ->assertSee('Unassigned')
        ->assertSee('SLA Breaches');
});
