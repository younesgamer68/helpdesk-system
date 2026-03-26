<?php

use App\Models\ChatbotFaq;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

function setupChatbotCompany(array $aiOverrides = []): array
{
    $company = Company::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    $widget = WidgetSetting::query()->create([
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

    $aiSettings = CompanyAiSettings::query()->create(array_merge([
        'company_id' => $company->id,
        'ai_chatbot_enabled' => true,
        'chatbot_greeting' => 'Hello!',
        'chatbot_fallback_threshold' => 2,
        'escalation_url_type' => 'standalone',
    ], $aiOverrides));

    return [$company, $admin, $widget, $aiSettings];
}

it('returns escalation_url as null when threshold not reached', function () {
    [$company, $admin, $widget] = setupChatbotCompany();

    ChatbotFaq::query()->create([
        'company_id' => $company->id,
        'question' => 'What is your refund policy?',
        'answer' => 'We offer 30-day refunds.',
    ]);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'What is your refund policy?']
    );

    $response->assertSuccessful()
        ->assertJsonStructure(['reply', 'show_ticket_form', 'escalation_url'])
        ->assertJson(['show_ticket_form' => false, 'escalation_url' => null]);
});

it('returns standalone escalation url when escalation_url_type is standalone', function () {
    [$company, $admin, $widget] = setupChatbotCompany([
        'escalation_url_type' => 'standalone',
        'chatbot_fallback_threshold' => 1,
    ]);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'xyzzy random gibberish no faq matches']
    );

    if ($response->json('show_ticket_form')) {
        $expectedBase = (config('app.env') === 'local' ? 'http' : 'https')
            ."://{$company->slug}.".config('app.domain')."/widget/{$widget->widget_key}";

        expect($response->json('escalation_url'))->toBe($expectedBase);
    }

    $response->assertSuccessful()
        ->assertJsonStructure(['reply', 'show_ticket_form', 'escalation_url']);
});

it('returns custom escalation url when escalation_url_type is custom_url', function () {
    [$company, $admin, $widget] = setupChatbotCompany([
        'escalation_url_type' => 'custom_url',
        'custom_escalation_url' => 'https://example.com/my-support-page',
        'chatbot_fallback_threshold' => 1,
    ]);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'xyzzy random gibberish no faq matches']
    );

    if ($response->json('show_ticket_form')) {
        expect($response->json('escalation_url'))->toBe('https://example.com/my-support-page');
    }

    $response->assertSuccessful()
        ->assertJsonStructure(['reply', 'show_ticket_form', 'escalation_url']);
});

it('reads fallback threshold from the database', function () {
    [$company, $admin, $widget] = setupChatbotCompany([
        'chatbot_fallback_threshold' => 5,
    ]);

    // First unanswered message should NOT trigger escalation when threshold is 5
    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'xyzzy random gibberish']
    );

    $response->assertSuccessful()
        ->assertJson(['show_ticket_form' => false]);
});

it('saves escalation_url_type from the admin settings', function () {
    [$company, $admin, $widget, $aiSettings] = setupChatbotCompany();

    actingAs($admin);

    Livewire\Livewire::test(\App\Livewire\Channels\AiChatbotWidget::class)
        ->set('escalation_url_type', 'custom_url')
        ->set('custom_escalation_url', 'https://example.com/support')
        ->set('ai_chatbot_enabled', true)
        ->set('chatbot_greeting', 'Hello!')
        ->set('chatbot_fallback_threshold', 3)
        ->call('saveSettings')
        ->assertDispatched('show-toast');

    expect($aiSettings->fresh()->escalation_url_type)->toBe('custom_url');
    expect($aiSettings->fresh()->custom_escalation_url)->toBe('https://example.com/support');
});

it('validates escalation_url_type must be standalone or custom_url', function () {
    [$company, $admin] = setupChatbotCompany();

    actingAs($admin);

    Livewire\Livewire::test(\App\Livewire\Channels\AiChatbotWidget::class)
        ->set('escalation_url_type', 'invalid')
        ->set('ai_chatbot_enabled', true)
        ->set('chatbot_greeting', 'Hello!')
        ->set('chatbot_fallback_threshold', 3)
        ->call('saveSettings')
        ->assertHasErrors(['escalation_url_type']);
});

it('persists ai_chatbot_enabled immediately when toggled', function () {
    [$company, $admin, $widget, $aiSettings] = setupChatbotCompany([
        'ai_chatbot_enabled' => false,
    ]);

    actingAs($admin);

    // Toggle ON
    Livewire\Livewire::test(\App\Livewire\Channels\AiChatbotWidget::class)
        ->set('ai_chatbot_enabled', true)
        ->assertDispatched('show-toast');

    expect($aiSettings->fresh()->ai_chatbot_enabled)->toBeTrue();

    // The chatbot widget page should now be accessible (no 404)
    $this->get(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}"
    )->assertSuccessful();

    // Toggle OFF
    Livewire\Livewire::test(\App\Livewire\Channels\AiChatbotWidget::class)
        ->set('ai_chatbot_enabled', false)
        ->assertDispatched('show-toast');

    expect($aiSettings->fresh()->ai_chatbot_enabled)->toBeFalse();
});

it('renders the floating chatbot widget with company name in header', function () {
    [$company, $admin, $widget] = setupChatbotCompany();

    $response = $this->get(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}"
    );

    $response->assertSuccessful()
        ->assertSee($company->name)
        ->assertSee('chat-bubble')
        ->assertSee('chat-panel');
});

it('escalates after consecutive unanswered turns for the same chatbot session id', function () {
    [$company, $admin, $widget] = setupChatbotCompany([
        'chatbot_fallback_threshold' => 2,
    ]);

    $sessionId = (string) Str::uuid();
    $url = "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message";

    $first = $this->withHeaders([
        'X-Chatbot-Session' => $sessionId,
    ])->postJson($url, ['message' => 'random unanswered message one']);

    $first->assertSuccessful()
        ->assertJson(['show_ticket_form' => false]);

    $second = $this->withHeaders([
        'X-Chatbot-Session' => $sessionId,
    ])->postJson($url, ['message' => 'random unanswered message two']);

    $second->assertSuccessful()
        ->assertJson(['show_ticket_form' => true])
        ->assertJsonPath('session_id', $sessionId);
});

it('escalates immediately when the customer explicitly asks for a human agent', function () {
    [$company, $admin, $widget] = setupChatbotCompany([
        'chatbot_fallback_threshold' => 5,
    ]);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'Please connect me to a human agent']
    );

    $response->assertSuccessful()
        ->assertJson(['show_ticket_form' => true]);
});
