<?php

use App\Ai\Agents\SupportReplyAgent;
use App\Livewire\Ai\AutoTriageRules;
use App\Livewire\Ai\ChatHistory;
use App\Livewire\Ai\SuggestedRepliesTraining;
use App\Livewire\Ai\UsageStats;
use App\Livewire\AiChatWidget;
use App\Livewire\Settings\AiCopilot;
use App\Livewire\Tickets\TicketDetails;
use App\Models\AiSuggestionLog;
use App\Models\AutoTriageRule;
use App\Models\ChatbotConversation;
use App\Models\Company;
use App\Models\CompanyAiSettings;
use App\Models\GoldenResponse;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\AutoTriageService;
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
// Auto-Triage Rules
// ──────────────────────────────────────

test('auto-triage rules page renders for admin', function () {
    [$admin] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(AutoTriageRules::class)
        ->assertSuccessful();
});

test('admin can create keyword triage rule', function () {
    [$admin, $company] = createAiAdmin();

    $this->actingAs($admin);

    Livewire::test(AutoTriageRules::class)
        ->call('openCreate')
        ->set('name', 'Billing Issues')
        ->set('type', 'keyword')
        ->set('keywordsInput', 'billing, invoice, payment')
        ->set('priority', 'high')
        ->call('save')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('auto_triage_rules', [
        'company_id' => $company->id,
        'name' => 'Billing Issues',
        'type' => 'keyword',
        'priority' => 'high',
    ]);
});

test('admin can create ai triage rule', function () {
    [$admin, $company] = createAiAdmin();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(AutoTriageRules::class)
        ->call('openCreate')
        ->set('name', 'AI Catch All')
        ->set('type', 'ai')
        ->set('category_id', $category->id)
        ->call('save')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('auto_triage_rules', [
        'company_id' => $company->id,
        'name' => 'AI Catch All',
        'type' => 'ai',
    ]);
});

test('admin can edit triage rule', function () {
    [$admin, $company] = createAiAdmin();
    $rule = AutoTriageRule::factory()->create(['company_id' => $company->id, 'name' => 'Old Name']);

    $this->actingAs($admin);

    Livewire::test(AutoTriageRules::class)
        ->call('openEdit', $rule->id)
        ->set('name', 'New Name')
        ->call('save')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('auto_triage_rules', [
        'id' => $rule->id,
        'name' => 'New Name',
    ]);
});

test('admin can toggle triage rule active', function () {
    [$admin, $company] = createAiAdmin();
    $rule = AutoTriageRule::factory()->create([
        'company_id' => $company->id,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(AutoTriageRules::class)
        ->call('toggleActive', $rule->id);

    expect($rule->fresh()->is_active)->toBeFalse();
});

test('admin can delete triage rule', function () {
    [$admin, $company] = createAiAdmin();
    $rule = AutoTriageRule::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(AutoTriageRules::class)
        ->call('delete', $rule->id)
        ->assertDispatched('show-toast');

    $this->assertDatabaseMissing('auto_triage_rules', ['id' => $rule->id]);
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
// Auto-Triage Service (keyword matching)
// ──────────────────────────────────────

test('auto-triage applies keyword rule to ticket', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Billing']);

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_auto_triage_enabled' => true,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
    ]);

    AutoTriageRule::create([
        'company_id' => $company->id,
        'name' => 'Billing Rule',
        'type' => 'keyword',
        'keywords' => ['invoice', 'billing'],
        'category_id' => $category->id,
        'priority' => 'high',
        'is_active' => true,
        'order' => 1,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'Problem with my invoice',
        'description' => 'I cannot download my invoice',
        'category_id' => null,
        'priority' => 'medium',
    ]);

    $service = new AutoTriageService;
    $service->triage($ticket);

    $ticket->refresh();
    expect($ticket->category_id)->toBe($category->id);
    expect($ticket->priority)->toBe('high');
});

test('auto-triage does nothing when disabled', function () {
    $company = Company::factory()->create();

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_auto_triage_enabled' => false,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'Test',
        'category_id' => null,
        'priority' => 'medium',
    ]);

    $service = new AutoTriageService;
    $service->triage($ticket);

    $ticket->refresh();
    expect($ticket->category_id)->toBeNull();
    expect($ticket->priority)->toBe('medium');
});

test('auto-triage skips when no keywords match', function () {
    $company = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    CompanyAiSettings::create([
        'company_id' => $company->id,
        'ai_auto_triage_enabled' => true,
        'ai_suggestions_enabled' => false,
        'ai_summary_enabled' => false,
        'ai_chatbot_enabled' => false,
        'ai_model' => 'gemini-2.5-flash',
    ]);

    AutoTriageRule::create([
        'company_id' => $company->id,
        'name' => 'Billing Rule',
        'type' => 'keyword',
        'keywords' => ['invoice', 'billing'],
        'category_id' => $category->id,
        'priority' => 'high',
        'is_active' => true,
        'order' => 1,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'My printer is broken',
        'description' => 'Paper jam every time',
        'category_id' => null,
        'priority' => 'medium',
    ]);

    // Fake the agent so generic AI triage doesn't make a real API call
    SupportReplyAgent::fake(['No matching category or priority found.']);

    $service = new AutoTriageService;
    $service->triage($ticket);

    $ticket->refresh();
    // Keyword rules shouldn't match; AI response doesn't contain valid Priority/Category format
    expect($ticket->priority)->toBe('medium');
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
        'ai_auto_triage_enabled' => false,
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
        'ai_auto_triage_enabled' => false,
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
        'ai_auto_triage_enabled' => false,
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
        'ai_auto_triage_enabled' => false,
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
        'ai_auto_triage_enabled' => false,
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
        'ai_auto_triage_enabled' => false,
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
