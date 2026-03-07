<?php

use App\Livewire\Settings\FormWidget;
use App\Models\Company;
use App\Models\User;
use App\Models\WidgetSetting;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('form widget settings page renders with theme mode select', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(FormWidget::class)
        ->assertStatus(200)
        ->assertSee('Theme Mode')
        ->assertSee('Dark Mode')
        ->assertSee('Light Mode');
});

test('theme mode can be saved as dark', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(FormWidget::class)
        ->set('theme_mode', 'dark')
        ->call('save')
        ->assertHasNoErrors();

    expect(WidgetSetting::where('company_id', $company->id)->first()->theme_mode)->toBe('dark');
});

test('theme mode can be saved as light', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(FormWidget::class)
        ->set('theme_mode', 'light')
        ->call('save')
        ->assertHasNoErrors();

    expect(WidgetSetting::where('company_id', $company->id)->first()->theme_mode)->toBe('light');
});

test('theme mode validation rejects invalid values', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(FormWidget::class)
        ->set('theme_mode', 'invalid-theme')
        ->call('save')
        ->assertHasErrors(['theme_mode']);
});

test('widget form renders dark theme correctly', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    WidgetSetting::create([
        'company_id' => $company->id,
        'theme_mode' => 'dark',
        'form_title' => 'Test Form',
        'success_message' => 'Thanks!',
        'default_status' => 'pending',
        'default_priority' => 'medium',
    ]);

    $this->actingAs($admin);

    $widget = WidgetSetting::where('company_id', $company->id)->first();
    $response = $this->get(route('widget.show', ['company' => $company->slug, 'key' => $widget->widget_key]));
    $response->assertStatus(200);
    $response->assertSee('class="dark"', false);
});

test('widget form renders light theme correctly', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    WidgetSetting::create([
        'company_id' => $company->id,
        'theme_mode' => 'light',
        'form_title' => 'Test Form',
        'success_message' => 'Thanks!',
        'default_status' => 'pending',
        'default_priority' => 'medium',
    ]);

    $this->actingAs($admin);

    $widget = WidgetSetting::where('company_id', $company->id)->first();
    $response = $this->get(route('widget.show', ['company' => $company->slug, 'key' => $widget->widget_key]));
    $response->assertStatus(200);
    $response->assertSee('class=""', false);
});
