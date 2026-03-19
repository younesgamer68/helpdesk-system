<?php

use App\Livewire\Tickets\Kb\ApiReference;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('rejects custom widget article base url when it is not /kb/article', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user);

    Livewire::test(ApiReference::class)
        ->set('widgetDefaultLinkMode', 'custom')
        ->set('widgetArticleBaseUrl', 'https://youtube.com')
        ->call('saveWidgetDefaults')
        ->assertHasErrors(['widgetArticleBaseUrl' => 'regex']);
});

it('shows inline validation error on widget article base url blur in custom mode', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user);

    Livewire::test(ApiReference::class)
        ->set('widgetDefaultLinkMode', 'custom')
        ->set('widgetArticleBaseUrl', 'https://youtube.com')
        ->assertHasErrors(['widgetArticleBaseUrl' => 'regex']);
});

it('accepts custom widget article base url when it matches /kb/article', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user);

    Livewire::test(ApiReference::class)
        ->set('widgetDefaultLinkMode', 'custom')
        ->set('widgetArticleBaseUrl', 'https://yourcompany.com/kb/article')
        ->call('saveWidgetDefaults')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'kb_widget_link_mode' => 'custom',
        'kb_widget_article_base_url' => 'https://yourcompany.com/kb/article',
    ]);
});
