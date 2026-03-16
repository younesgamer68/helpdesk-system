<?php

use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createOnboardedCompanyAdmin(): array
{
    $company = Company::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    return [$admin, $company];
}

it('renders the channels page for admins', function () {
    [$admin, $company] = createOnboardedCompanyAdmin();

    actingAs($admin)
        ->get("http://{$company->slug}.".config('app.domain').'/channels')
        ->assertOk()
        ->assertSee('Channels')
        ->assertSee('Form Widget')
        ->assertSee('AI Chatbot Widget');
});

it('redirects non-admin users away from channels page', function () {
    $company = Company::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $operator = User::factory()->operator()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    actingAs($operator)
        ->get("http://{$company->slug}.".config('app.domain').'/channels')
        ->assertRedirect(route('tickets', $company->slug));
});

it('returns 404 for disabled chatbot widget', function () {
    [$admin, $company] = createOnboardedCompanyAdmin();

    WidgetSetting::query()->create([
        'company_id' => $company->id,
        'theme_mode' => 'dark',
        'form_title' => 'Submit a Support Ticket',
        'welcome_message' => '',
        'success_message' => 'Thanks',
        'require_phone' => false,
        'show_category' => true,
        'default_status' => 'pending',
        'default_priority' => 'medium',
        'is_active' => true,
    ]);

    CompanyAiSettings::query()->create([
        'company_id' => $company->id,
        'ai_chatbot_enabled' => false,
        'chatbot_greeting' => 'Hi! How can I help you today?',
        'chatbot_fallback_threshold' => 2,
    ]);

    actingAs($admin);

    $key = WidgetSetting::query()->where('company_id', $company->id)->value('widget_key');

    get("http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$key}")
        ->assertNotFound();
});

it('renders chatbot widget when enabled with a valid key', function () {
    [$admin, $company] = createOnboardedCompanyAdmin();

    WidgetSetting::query()->create([
        'company_id' => $company->id,
        'theme_mode' => 'dark',
        'form_title' => 'Submit a Support Ticket',
        'welcome_message' => '',
        'success_message' => 'Thanks',
        'require_phone' => false,
        'show_category' => true,
        'default_status' => 'pending',
        'default_priority' => 'medium',
        'is_active' => true,
    ]);

    CompanyAiSettings::query()->create([
        'company_id' => $company->id,
        'ai_chatbot_enabled' => true,
        'chatbot_greeting' => 'Hi! How can I help you today?',
        'chatbot_fallback_threshold' => 2,
    ]);

    actingAs($admin);

    $key = WidgetSetting::query()->where('company_id', $company->id)->value('widget_key');

    get("http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$key}")
        ->assertOk()
        ->assertSee('Assistant');
});
