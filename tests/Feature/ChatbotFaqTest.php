<?php

use App\Models\ChatbotFaq;
use App\Models\Conversation;

it('returns 4 random faqs as json', function () {
    ChatbotFaq::factory()->count(10)->create();

    $response = $this->getJson(route('chatbot.faqs'));

    $response->assertSuccessful()
        ->assertJsonCount(4)
        ->assertJsonStructure([
            ['id', 'question', 'answer'],
        ]);
});

it('returns all faqs when fewer than 4 exist', function () {
    ChatbotFaq::factory()->count(2)->create();

    $response = $this->getJson(route('chatbot.faqs'));

    $response->assertSuccessful()
        ->assertJsonCount(2);
});

it('returns empty array when no faqs exist', function () {
    $response = $this->getJson(route('chatbot.faqs'));

    $response->assertSuccessful()
        ->assertJsonCount(0);
});

it('does not expose timestamps in response', function () {
    ChatbotFaq::factory()->create();

    $response = $this->getJson(route('chatbot.faqs'));

    $response->assertSuccessful()
        ->assertJsonMissing(['created_at'])
        ->assertJsonMissing(['updated_at']);
});

it('returns different faqs on subsequent requests', function () {
    ChatbotFaq::factory()->count(10)->create();

    $results = collect(range(1, 10))->map(
        fn () => $this->getJson(route('chatbot.faqs'))->json()
    )->map(fn ($faqs) => collect($faqs)->pluck('id')->sort()->values()->all());

    expect($results->unique()->count())->toBeGreaterThan(1);
});

it('accepts a chat message and returns a reply', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'Hello there',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['reply']);
});

it('stores conversation in the database', function () {
    $this->postJson(route('chatbot.chat'), [
        'message' => 'Tell me about pricing',
    ]);

    expect(Conversation::query()->count())->toBe(1);

    $conversation = Conversation::query()->first();
    expect($conversation->user_message)->toBe('Tell me about pricing');
    expect($conversation->bot_response)->not->toBeEmpty();
});

it('returns keyword-matched reply for sales', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'I want to talk to sales',
    ]);

    $response->assertSuccessful();
    expect($response->json('reply'))->toContain('sales team');
});

it('returns keyword-matched reply for pricing', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'What is your pricing?',
    ]);

    $response->assertSuccessful();
    expect($response->json('reply'))->toContain('plans');
});

it('returns keyword-matched reply for trial', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'How do I start a trial?',
    ]);

    $response->assertSuccessful();
    expect($response->json('reply'))->toContain('14-day trial');
});

it('returns keyword-matched reply for demo', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'Can I get a demo?',
    ]);

    $response->assertSuccessful();
    expect($response->json('reply'))->toContain('demo');
});

it('returns keyword-matched reply for account help', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'I need help with my account',
    ]);

    $response->assertSuccessful();
    expect($response->json('reply'))->toContain('Settings');
});

it('returns fallback reply for unknown messages', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => 'xyzzy foobar baz',
    ]);

    $response->assertSuccessful();
    expect($response->json('reply'))->toContain('not sure I understand');
});

it('validates message is required', function () {
    $response = $this->postJson(route('chatbot.chat'), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

it('validates message max length', function () {
    $response = $this->postJson(route('chatbot.chat'), [
        'message' => str_repeat('a', 1001),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

it('stores multiple conversations independently', function () {
    $this->postJson(route('chatbot.chat'), ['message' => 'Hello']);
    $this->postJson(route('chatbot.chat'), ['message' => 'Pricing info']);
    $this->postJson(route('chatbot.chat'), ['message' => 'Thanks']);

    expect(Conversation::query()->count())->toBe(3);
});
