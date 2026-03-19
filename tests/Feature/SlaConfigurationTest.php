<?php

use App\Livewire\Tickets\SlaConfiguration;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;

test('sla configuration loads company timezone', function () {
    $company = Company::factory()->create(['timezone' => 'Europe/Paris']);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(SlaConfiguration::class)
        ->assertSet('timezone', 'Europe/Paris');
});

test('sla configuration updates company timezone and sla policy', function () {
    $company = Company::factory()->create(['timezone' => 'UTC']);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(SlaConfiguration::class)
        ->set('timezone', 'America/New_York')
        ->set('is_enabled', true)
        ->set('low_minutes', 1500)
        ->set('medium_minutes', 600)
        ->set('high_minutes', 180)
        ->set('urgent_minutes', 45)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('show-toast');

    expect($company->fresh()->timezone)->toBe('America/New_York');

    $this->assertDatabaseHas('sla_policies', [
        'company_id' => $company->id,
        'is_enabled' => 1,
        'low_minutes' => 1500,
        'medium_minutes' => 600,
        'high_minutes' => 180,
        'urgent_minutes' => 45,
    ]);
});

test('sla configuration rejects invalid timezone', function () {
    $company = Company::factory()->create(['timezone' => 'UTC']);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(SlaConfiguration::class)
        ->set('timezone', 'Invalid/Timezone')
        ->call('save')
        ->assertHasErrors(['timezone']);
});

test('sla configuration provides timezone dropdown options', function () {
    $company = Company::factory()->create(['timezone' => 'UTC']);
    $admin = User::factory()->admin()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    $component = Livewire::test(SlaConfiguration::class);
    $timezones = $component->instance()->timezones();

    expect($timezones)->toBeArray()
        ->and($timezones)->toHaveKey('UTC')
        ->and($timezones)->toHaveKey('America/New_York')
        ->and($timezones)->toHaveKey('Europe/Paris');
});
