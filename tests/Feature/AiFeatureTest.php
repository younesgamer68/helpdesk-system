<?php

use App\Ai\Agents\SupportReplyAgent;
use App\Livewire\Ai\ChatHistory;
use App\Livewire\Ai\SuggestedRepliesTraining;
use App\Livewire\Ai\UsageStats;
use App\Livewire\AiChatWidget;
use App\Livewire\Settings\AiCopilot;
use App\Livewire\Tickets\TicketDetails;
use App\Models\AiSuggestionLog;
use App\Models\ChatbotConversation;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\GoldenResponse;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

function createAiAdmin(): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    return [$admin, $company];
}

// ──────────────────────────────────────
// Copilot Settings
// ──────────────────────────────────────

test('copilot settings page renders for admin', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(AiCopilot::class)
        ->assertSuccessful()
        ->assertSee('AI Copilot');
});

test('copilot settings page redirects non-admin', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $operator = User::factory()->operator()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($operator)
        ->get("http://{$company->slug}.".config('app.domain').'/settings/ai-copilot')
        ->assertRedirect(route('tickets', $company->slug));
});

test('copilot settings saves ai settings', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(AiCopilot::class)
        ->set('ai_suggestions_enabled', true)
        ->set('ai_summary_enabled', true)
        ->set('ai_model', 'gpt-4o-mini')
        ->call('save')
        ->assertDispatched('ai-copilot-updated');

    $this->assertDatabaseHas('company_ai_settings', [
        'company_id' => $company->id,
        'ai_suggestions_enabled' => true,
        'ai_summary_enabled' => true,
        'ai_model' => 'gpt-4o-mini',
    ]);
});

test('copilot settings validates model selection', function () {
    [$admin] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(AiCopilot::class)
        ->set('ai_model', 'invalid-model')
        ->call('save')
        ->assertHasErrors(['ai_model']);
});

// ──────────────────────────────────────
// Suggested Replies Training
// ──────────────────────────────────────

test('suggested replies training page renders', function () {
    [$admin] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(SuggestedRepliesTraining::class)
        ->assertSuccessful();
});

test('admin can add golden response', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(SuggestedRepliesTraining::class)
        ->call('openAdd')
        ->set('newContent', 'Thank you for contacting us. We will resolve this within 24 hours.')
        ->call('saveGolden')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('golden_responses', [
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'content' => 'Thank you for contacting us. We will resolve this within 24 hours.',
    ]);
});

test('admin can delete golden response', function () {
    [$admin, $company] = createAiAdmin();
    $golden = GoldenResponse::factory()->create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(SuggestedRepliesTraining::class)
        ->call('deleteGolden', $golden->id)
        ->assertDispatched('show-toast');

    $this->assertDatabaseMissing('golden_responses', ['id' => $golden->id]);
});

test('suggestion feed displays logs', function () {
    [$admin, $company] = createAiAdmin();
    $ticket = Ticket::factory()->create(['company_id' => $company->id]);
    AiSuggestionLog::create([
        'company_id' => $company->id,
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'action' => 'generate',
    ]);

    $this->actingAs($admin);

    Livewire::test(SuggestedRepliesTraining::class)
        ->set('tab', 'feed')
        ->assertSee('Generate');
});

// ──────────────────────────────────────
// Chat History
// ──────────────────────────────────────

test('chat history page renders', function () {
    [$admin] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(ChatHistory::class)
        ->assertSuccessful();
});

test('chat history lists conversations', function () {
    [$admin, $company] = createAiAdmin();
    ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'sess-abc',
        'messages' => [
            ['role' => 'user', 'text' => 'Hello', 'at' => now()->toIso8601String()],
            ['role' => 'bot', 'text' => 'Hi there!', 'at' => now()->toIso8601String()],
        ],
        'outcome' => 'resolved',
    ]);

    $this->actingAs($admin);

    Livewire::test(ChatHistory::class)
        ->assertSee('sess-abc');
});

test('chat history filters by outcome', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'sess-resolved',
        'messages' => [],
        'outcome' => 'resolved',
    ]);
    ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'sess-escalated',
        'messages' => [],
        'outcome' => 'escalated',
    ]);

    Livewire::test(ChatHistory::class)
        ->set('outcomeFilter', 'resolved')
        ->assertSee('sess-resolv')
        ->assertDontSee('sess-escala');
});

test('chat history marks stale active conversations as abandoned', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    $conversation = ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'sess-active-old',
        'messages' => [],
        'outcome' => 'active',
    ]);

    ChatbotConversation::query()
        ->whereKey($conversation->id)
        ->update(['updated_at' => now()->subMinutes(45)]);

    Livewire::test(ChatHistory::class)
        ->assertSuccessful();

    expect($conversation->fresh()->outcome)->toBe('abandoned');
});

test('chat history view detail sets conversation', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    $convo = ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'sess-detail',
        'messages' => [
            ['role' => 'user', 'text' => 'How do I reset?', 'at' => now()->toIso8601String()],
            ['role' => 'bot', 'text' => 'Go to settings.', 'at' => now()->toIso8601String()],
        ],
        'outcome' => 'resolved',
    ]);

    Livewire::test(ChatHistory::class)
        ->call('viewConversation', $convo->id)
        ->assertSet('viewingId', $convo->id)
        ->assertSet('showDetail', true);
});

// ──────────────────────────────────────
// Usage Stats
// ──────────────────────────────────────

test('usage stats page renders', function () {
    [$admin] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(UsageStats::class)
        ->assertSuccessful();
});

test('usage stats computes correct metrics', function () {
    [$admin, $company] = createAiAdmin();
    $ticket = Ticket::factory()->create(['company_id' => $company->id]);

    // 3 generated, 2 used, 1 dismissed
    foreach (['generate', 'generate', 'generate'] as $action) {
        AiSuggestionLog::create([
            'company_id' => $company->id,
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'action' => $action,
        ]);
    }
    foreach (['use', 'use'] as $action) {
        AiSuggestionLog::create([
            'company_id' => $company->id,
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'action' => $action,
        ]);
    }
    AiSuggestionLog::create([
        'company_id' => $company->id,
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'action' => 'dismiss',
    ]);

    // 2 resolved conversations, 1 escalated
    ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'resolved-1',
        'messages' => [],
        'outcome' => 'resolved',
    ]);
    ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'resolved-2',
        'messages' => [],
        'outcome' => 'resolved',
    ]);
    ChatbotConversation::create([
        'company_id' => $company->id,
        'session_id' => 'escalated-1',
        'messages' => [],
        'outcome' => 'escalated',
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(UsageStats::class);

    // Assert stats are visible in rendered output
    $component->assertSee('3')   // suggestions generated
        ->assertSee('66.7')      // acceptance rate
        ->assertSee('22');       // time saved minutes
});

// ──────────────────────────────────────
// Settings Enforcement
// ──────────────────────────────────────

test('ai suggestion is blocked when ai_suggestions_enabled is false', function () {
    [$admin, $company] = createAiAdmin();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gemini-2.5-flash',
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('startAiSuggestion')
        ->assertDispatched('show-toast')
        ->assertSet('showAiSuggestion', false);
});

test('ai suggestion works when ai_suggestions_enabled is true', function () {
    [$admin, $company] = createAiAdmin();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_suggestions_enabled' => true,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gemini-2.5-flash',
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    SupportReplyAgent::fake(['Here is a suggestion for you.']);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('startAiSuggestion')
        ->assertSet('showAiSuggestion', true)
        ->assertSet('aiLoading', true);
});

test('ai summary is blocked when ai_summary_enabled is false', function () {
    [$admin, $company] = createAiAdmin();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gemini-2.5-flash',
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('generateAiSummary')
        ->assertSet('showSummary', false)
        ->assertSet('summaryLoading', false)
        ->assertSet('aiSummary', '');
});

test('ai summary generates when ai_summary_enabled is true', function () {
    [$admin, $company] = createAiAdmin();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => true,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gemini-2.5-flash',
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    SupportReplyAgent::fake(['Issue: Test\nProgress: None\nNext Step: Fix it']);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('generateAiSummary')
        ->assertSet('summaryLoading', false)
        ->assertNotSet('aiSummary', '');
});

test('ai model setting resolves correct provider', function () {
    $settings = new CompanyAiSettings;

    $settings->ai_model = 'gemini-2.5-flash';
    expect($settings->resolveProvider())->toBe(\Laravel\Ai\Enums\Lab::Gemini);

    $settings->ai_model = 'gemini-2.5-pro';
    expect($settings->resolveProvider())->toBe(\Laravel\Ai\Enums\Lab::Gemini);

    $settings->ai_model = 'gpt-4o';
    expect($settings->resolveProvider())->toBe(\Laravel\Ai\Enums\Lab::OpenAI);

    $settings->ai_model = 'gpt-4o-mini';
    expect($settings->resolveProvider())->toBe(\Laravel\Ai\Enums\Lab::OpenAI);

    $settings->ai_model = 'claude-sonnet-4-20250514';
    expect($settings->resolveProvider())->toBe(\Laravel\Ai\Enums\Lab::Anthropic);
});

test('ai suggestion passes configured model to agent', function () {
    [$admin, $company] = createAiAdmin();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_suggestions_enabled' => true,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gpt-4o-mini',
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    SupportReplyAgent::fake(['AI-generated reply']);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->call('generateAiSuggestion')
        ->assertSet('aiSuggestion', 'AI-generated reply');

    SupportReplyAgent::assertPrompted(fn ($prompt) => $prompt->contains('Company name:'));
});

test('chatbot is blocked when ai_chatbot_enabled is false', function () {
    [$admin, $company] = createAiAdmin();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gemini-2.5-flash',
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(AiChatWidget::class)
        ->call('sendMessage')
        ->assertSet('isTyping', false);
});

test('use ai suggestion logs use action', function () {
    [$admin, $company] = createAiAdmin();

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->set('aiSuggestion', 'Original suggestion')
        ->set('showAiSuggestion', true)
        ->call('useAiSuggestion', 'Edited suggestion text')
        ->assertSet('showAiSuggestion', false)
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('ai_suggestion_logs', [
        'company_id' => $company->id,
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'action' => 'use',
        'suggestion_text' => 'Edited suggestion text',
    ]);
});

test('dismiss ai suggestion logs dismiss action', function () {
    [$admin, $company] = createAiAdmin();

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketDetails::class, ['ticket' => $ticket])
        ->set('aiSuggestion', 'Suggestion to dismiss')
        ->set('showAiSuggestion', true)
        ->call('dismissAiSuggestion')
        ->assertSet('showAiSuggestion', false)
        ->assertSet('aiSuggestion', '');

    $this->assertDatabaseHas('ai_suggestion_logs', [
        'company_id' => $company->id,
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'action' => 'dismiss',
    ]);
});
