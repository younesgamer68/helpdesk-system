<?php

use App\Livewire\Onboarding\Wizard;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders the onboarding wizard page for non-onboarded companies', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/onboarding')
        ->assertSuccessful()
        ->assertSee('Setup your Workspace');
});

it('redirects non-onboarded companies to the wizard', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/tickets')
        ->assertRedirect(route('onboarding.wizard', ['company' => $company->slug]));
});

it('allows onboarded companies to access the dashboard', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/tickets')
        ->assertOk();
});

it('completes the onboarding flow and saves data correctly', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($user);

    Livewire::test(Wizard::class)
        ->set('timezone', 'Europe/Paris')
        ->set('slaIsEnabled', true)
        ->set('slaLowMinutes', 1500)
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->set('categories', [
            ['name' => 'IT Support', 'color' => '#ff0000'],
            ['name' => 'HR', 'color' => '#00ff00'],
        ])
        ->call('nextStep')
        ->assertSet('currentStep', 3)
        ->set('invites', [
            ['email' => 'jane@example.com', 'name' => 'Jane Smith', 'role' => 'admin'],
        ])
        ->call('nextStep')
        ->assertSet('currentStep', 4)
        ->set('widgetThemeMode', 'light')
        ->set('widgetWelcomeMessage', 'Hello World!')
        ->call('completeOnboarding')
        ->assertRedirect(route('tickets', ['company' => $company->slug]));

    // Assert database was updated
    $company->refresh();
    expect($company->onboarding_completed_at)->not->toBeNull();
    expect($company->timezone)->toBe('Europe/Paris');

    $this->assertDatabaseHas('ticket_categories', [
        'company_id' => $company->id,
        'name' => 'IT Support',
        'color' => '#ff0000',
    ]);

    $this->assertDatabaseHas('ticket_categories', [
        'company_id' => $company->id,
        'name' => 'HR',
        'color' => '#00ff00',
    ]);

    $this->assertDatabaseHas('users', [
        'company_id' => $company->id,
        'email' => 'jane@example.com',
        'name' => 'Jane Smith',
        'role' => 'admin',
    ]);

    $this->assertDatabaseHas('widget_settings', [
        'company_id' => $company->id,
        'theme_mode' => 'light',
        'welcome_message' => 'Hello World!',
        'is_active' => 1,
    ]);

    $this->assertDatabaseHas('sla_policies', [
        'company_id' => $company->id,
        'is_enabled' => 1,
        'low_minutes' => 1500,
        'medium_minutes' => 480,
        'high_minutes' => 120,
        'urgent_minutes' => 30,
    ]);
});

it('allows skipping the entire wizard', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($user);

    Livewire::test(Wizard::class)
        ->call('skipEntireWizard')
        ->assertRedirect(route('tickets', ['company' => $company->slug]));

    $company->refresh();
    expect($company->onboarding_completed_at)->not->toBeNull();
    expect($company->categories()->count())->toBeGreaterThan(0);
    expect($company->widgetSettings()->exists())->toBeTrue();
});

it('allows skipping individual steps', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($user);

    Livewire::test(Wizard::class)
        ->assertSet('currentStep', 1)
        ->call('skipStep')
        ->assertSet('currentStep', 2)
        ->call('skipStep')
        ->assertSet('currentStep', 3)
        ->call('skipStep')
        ->assertSet('currentStep', 4)
        ->call('skipStep')
        ->assertRedirect(route('tickets', ['company' => $company->slug]));

    expect($company->refresh()->onboarding_completed_at)->not->toBeNull();
});
