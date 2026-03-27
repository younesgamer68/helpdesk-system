<?php

use App\Ai\Agents\HelpdeskAgent;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Support\Str;

function setupChatbotIntent(array $aiOverrides = []): array
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
        'chatbot_fallback_threshold' => 20,
        'escalation_url_type' => 'standalone',
    ], $aiOverrides));

    return [$company, $admin, $widget, $aiSettings];
}

it('responds to greetings without calling AI', function (string $greeting) {
    [$company, $admin, $widget] = setupChatbotIntent();

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => $greeting]
    );

    $response->assertSuccessful()
        ->assertJson([
            'show_ticket_form' => false,
            'escalation_url' => null,
        ]);

    expect($response->json('reply'))->toContain($company->name);
})->with(['hi', 'hello', 'hey', 'good morning', 'Hi!', 'Hello there']);

it('responds to thanks messages with a friendly reply and marks resolved', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    $sessionId = (string) Str::uuid();

    $response = $this->withHeaders(['X-Chatbot-Session' => $sessionId])
        ->postJson(
            "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
            ['message' => 'Thank you so much!']
        );

    $response->assertSuccessful()
        ->assertJson([
            'show_ticket_form' => false,
            'escalation_url' => null,
        ]);

    expect($response->json('reply'))->toContain('welcome');

    $this->assertDatabaseHas('chatbot_conversations', [
        'company_id' => $company->id,
        'session_id' => $sessionId,
        'outcome' => 'resolved',
    ]);
});

it('does not treat long messages containing thanks as a thanks message', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    HelpdeskAgent::fake(['Our billing plans start at $9/month.']);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'Thanks for the previous answer but I also want to know about billing and pricing plans']
    );

    $response->assertSuccessful();

    // Should go through AI (support question), not the thanks shortcut
    expect($response->json('reply'))->not->toContain('welcome');
});

it('does not show ticket form for greetings even after many messages', function () {
    [$company, $admin, $widget] = setupChatbotIntent([
        'chatbot_fallback_threshold' => 1,
    ]);

    $sessionId = (string) Str::uuid();
    $url = "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message";

    // Send multiple greetings — none should trigger escalation
    foreach (['hi', 'hello', 'hey'] as $greeting) {
        $response = $this->withHeaders(['X-Chatbot-Session' => $sessionId])
            ->postJson($url, ['message' => $greeting]);

        $response->assertSuccessful()
            ->assertJson(['show_ticket_form' => false]);
    }
});

it('escalates immediately when user requests human agent regardless of threshold', function () {
    [$company, $admin, $widget] = setupChatbotIntent([
        'chatbot_fallback_threshold' => 20,
    ]);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'I want to talk to a human agent please']
    );

    $response->assertSuccessful()
        ->assertJson(['show_ticket_form' => true]);

    expect($response->json('escalation_url'))->not->toBeNull();
});

it('escalates when user asks for support team to send a ticket', function () {
    [$company, $admin, $widget] = setupChatbotIntent([
        'chatbot_fallback_threshold' => 20,
    ]);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'give me support team to send ticket']
    );

    $response->assertSuccessful()
        ->assertJson(['show_ticket_form' => true]);

    expect($response->json('escalation_url'))->not->toBeNull();
});

it('does not escalate for ticket status questions', function () {
    [$company, $admin, $widget] = setupChatbotIntent([
        'chatbot_fallback_threshold' => 20,
    ]);

    HelpdeskAgent::fake(['You can track ticket status from your dashboard.']);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'how do I check my ticket status?']
    );

    $response->assertSuccessful()
        ->assertJson(['show_ticket_form' => false]);
});

it('resets unanswered counter when thanks message is received', function () {
    [$company, $admin, $widget] = setupChatbotIntent([
        'chatbot_fallback_threshold' => 2,
    ]);

    HelpdeskAgent::fake(fn () => '');

    $sessionId = (string) Str::uuid();
    $url = "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message";

    // First unanswered question
    $this->withHeaders(['X-Chatbot-Session' => $sessionId])
        ->postJson($url, ['message' => 'xyzzy random gibberish one']);

    // Thanks resets the counter
    $this->withHeaders(['X-Chatbot-Session' => $sessionId])
        ->postJson($url, ['message' => 'thanks']);

    // Another unanswered question — counter should be 1, not 2
    $response = $this->withHeaders(['X-Chatbot-Session' => $sessionId])
        ->postJson($url, ['message' => 'xyzzy random gibberish two']);

    $response->assertSuccessful()
        ->assertJson(['show_ticket_form' => false]);
});

it('returns correct JSON structure for all message types', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    $url = "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message";

    $response = $this->postJson($url, ['message' => 'hello']);

    $response->assertSuccessful()
        ->assertJsonStructure(['reply', 'show_ticket_form', 'escalation_url', 'session_id']);
});

it('saves conversations for greeting messages', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    $sessionId = (string) Str::uuid();

    $this->withHeaders(['X-Chatbot-Session' => $sessionId])
        ->postJson(
            "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
            ['message' => 'hi']
        );

    $this->assertDatabaseHas('chatbot_conversations', [
        'company_id' => $company->id,
        'session_id' => $sessionId,
    ]);
});

it('returns a local fallback response when rate limited', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    HelpdeskAgent::fake(function () {
        throw \Laravel\Ai\Exceptions\RateLimitedException::forProvider('gemini');
    });

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'How do I reset my password?']
    );

    $response->assertSuccessful();

    expect($response->json('reply'))->toContain('experiencing high demand');
    expect($response->json('show_ticket_form'))->toBeFalse();
});

it('uses KB articles as the knowledge source for AI prompts', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    \App\Models\KbArticle::withoutGlobalScopes()->create([
        'company_id' => $company->id,
        'title' => 'Password Reset Guide',
        'slug' => 'password-reset-guide',
        'body' => '<p>To reset your password, go to Settings and click Change Password.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    HelpdeskAgent::fake(['To reset your password, go to Settings and click Change Password.']);

    $response = $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'How do I reset my password?']
    );

    $response->assertSuccessful();

    expect($response->json('reply'))->toContain('password');

    HelpdeskAgent::assertPrompted(function ($prompt) {
        return str_contains($prompt->prompt, 'Password Reset Guide')
            && str_contains($prompt->prompt, 'Knowledge Base')
            && str_contains($prompt->prompt, '/kb/article/password-reset-guide');
    });
});

it('includes company scoping instructions in the AI prompt', function () {
    [$company, $admin, $widget] = setupChatbotIntent();

    HelpdeskAgent::fake(['I can only help with company topics.']);

    $this->postJson(
        "http://{$company->slug}.".config('app.domain')."/chatbot-widget/{$widget->widget_key}/message",
        ['message' => 'What is 2+2?']
    );

    HelpdeskAgent::assertPrompted(function ($prompt) use ($company) {
        return str_contains($prompt->prompt, 'UNRELATED')
            && str_contains($prompt->prompt, $company->name);
    });
});
