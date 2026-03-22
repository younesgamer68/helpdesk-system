<?php

use App\Livewire\Dashboard\AdminDashboard;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders the dashboard for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    /** @var User $user */
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $response = actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/admin/dashboard')
        ->assertOk();
});

it('shows key elements on the dashboard', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    /** @var User $user */
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    Livewire::actingAs($user)
        ->test(AdminDashboard::class)
        ->assertSee('Admin Dashboard')
        ->assertSee('Open Tickets')
        ->assertSee('Resolved Today')
        ->assertSee('Unassigned')
        ->assertSee('SLA Breaches');
});

it('does not show tickets from another company', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $otherCompany = Company::factory()->create(['onboarding_completed_at' => now()]);
    /** @var User $user */
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    \App\Models\Ticket::factory()->create([
        'company_id' => $company->id,
        'ticket_number' => 'TKT-LOCAL1',
        'subject' => 'Local company ticket',
        'status' => 'open',
    ]);

    \App\Models\Ticket::factory()->create([
        'company_id' => $otherCompany->id,
        'ticket_number' => 'TKT-OTHER1',
        'subject' => 'Other company ticket',
        'status' => 'open',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/admin/dashboard')
        ->assertSuccessful()
        ->assertSee('TKT-LOCAL1')
        ->assertDontSee('TKT-OTHER1');
});

it('renders recent ticket customers without lazy loading violations', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $customer = Customer::factory()->create([
        'company_id' => $company->id,
        'name' => 'Acme Customer',
    ]);

    Ticket::factory()->create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'ticket_number' => 'TKT-CUSTOMER',
        'subject' => 'Customer relation ticket',
        'status' => 'open',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/admin/dashboard')
        ->assertSuccessful()
        ->assertSee('TKT-CUSTOMER')
        ->assertSee('Acme Customer');
});

it('only renders the admin KPI flyout after a KPI is clicked', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $agent = User::factory()->create(['company_id' => $company->id, 'role' => 'agent', 'email' => 'agent@example.com']);

    Livewire::actingAs($user)
        ->test(AdminDashboard::class)
        ->assertDontSee('All Agents')
        ->assertDontSee('agent@example.com')
        ->call('loadModal', 'total-agents')
        ->assertSet('activeModal', 'total-agents')
        ->assertSee('All Agents')
        ->assertSee('agent@example.com')
        ->call('closeModal')
        ->assertSet('activeModal', null)
        ->assertDontSee('All Agents')
        ->assertDontSee('agent@example.com');
});
