<?php

use App\Livewire\Settings\CompanyProfile;
use App\Models\Company;
use App\Models\TenantConfig;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('company profile page renders for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->assertSuccessful()
        ->assertSee('Company Profile');
});

test('company profile page is forbidden for non-admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $agent = User::factory()->create(['company_id' => $company->id, 'role' => 'agent']);

    $this->actingAs($agent);

    Livewire::test(CompanyProfile::class)
        ->assertForbidden();
});

test('company profile can be updated', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('companyEmail', 'support@updated.com')
        ->set('companyPhone', '+1234567890')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('company-profile-updated');

    $company->refresh();
    expect($company->email)->toBe('support@updated.com');
    expect($company->phone)->toBe('+1234567890');
});

test('company slug can be updated directly', function () {
    $company = Company::factory()->create([
        'name' => 'Acme Corporation',
        'slug' => 'acme-corporation',
        'onboarding_completed_at' => now(),
    ]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('companySlug', 'New Brand Inc')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectContains(config('app.domain').'/login');

    $company->refresh();
    expect($company->slug)->toBe('new-brand-inc');
});

test('company slug change forces logout and redirects to central login', function () {
    $company = Company::factory()->create([
        'slug' => 'old-slug',
        'onboarding_completed_at' => now(),
    ]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('companySlug', 'new-slug')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectContains(config('app.domain'))
        ->assertRedirectContains('/login');

    expect(auth()->check())->toBeFalse();
});

test('company slug must be unique', function () {
    Company::factory()->create(['slug' => 'existing-slug']);

    $company = Company::factory()->create([
        'slug' => 'my-company',
        'onboarding_completed_at' => now(),
    ]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('companySlug', 'existing-slug')
        ->call('save')
        ->assertHasErrors(['companySlug']);
});

test('company slug input is normalized before save', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('companySlug', 'My Custom Name !!')
        ->call('save')
        ->assertHasNoErrors();

    $company->refresh();
    expect($company->slug)->toBe('my-custom-name');
});

test('company profile validates required fields', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('companyName', '')
        ->set('companyEmail', '')
        ->call('save')
        ->assertHasErrors(['companyName', 'companyEmail']);
});

test('company logo can be uploaded', function () {
    Storage::fake('public');

    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->call('save')
        ->assertHasNoErrors();

    $company->refresh();
    expect($company->logo)->not->toBeNull();
    Storage::disk('public')->assertExists($company->logo);
});

test('admin can update max tickets per agent setting', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    TenantConfig::create([
        'company_id' => $company->id,
        'max_tickets_per_agent' => 20,
    ]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->assertSet('maxTicketsPerAgent', 20)
        ->set('maxTicketsPerAgent', 15)
        ->call('save')
        ->assertHasNoErrors();

    expect(TenantConfig::where('company_id', $company->id)->first()->max_tickets_per_agent)->toBe(15);
});

test('max tickets per agent must be between 1 and 100', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(CompanyProfile::class)
        ->set('maxTicketsPerAgent', 0)
        ->call('save')
        ->assertHasErrors(['maxTicketsPerAgent']);

    Livewire::test(CompanyProfile::class)
        ->set('maxTicketsPerAgent', 101)
        ->call('save')
        ->assertHasErrors(['maxTicketsPerAgent']);
});
