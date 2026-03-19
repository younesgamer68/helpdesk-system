<?php

use App\Livewire\Tickets\TicketDetails;
use App\Models\Company;
use App\Models\Customer;
use App\Models\KbArticle;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->admin()->create(['company_id' => $this->company->id]);

    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
    ]);

    $this->ticket = Ticket::create([
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'ticket_number' => 'TKT-000001',
        'subject' => 'Test Ticket',
        'description' => 'Test description',
        'status' => 'open',
        'priority' => 'medium',
        'verified' => true,
    ]);

    $this->actingAs($this->user);
});

test('kb search returns published articles matching the search term', function () {
    KbArticle::create([
        'company_id' => $this->company->id,
        'title' => 'How to Reset Password',
        'slug' => 'how-to-reset-password',
        'body' => 'Instructions here.',
        'status' => 'published',
    ]);

    KbArticle::create([
        'company_id' => $this->company->id,
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'body' => 'Not published.',
        'status' => 'draft',
    ]);

    $component = Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('kbSearch', 'Reset');

    $results = $component->instance()->kbResults;

    expect($results)->toHaveCount(1)
        ->and($results->first()['title'])->toBe('How to Reset Password')
        ->and($results->first()['slug'])->toBe('how-to-reset-password');
});

test('kb search returns empty when query is too short', function () {
    KbArticle::create([
        'company_id' => $this->company->id,
        'title' => 'Some Article',
        'slug' => 'some-article',
        'body' => 'Content.',
        'status' => 'published',
    ]);

    $component = Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('kbSearch', 'S');

    expect($component->instance()->kbResults)->toBeEmpty();
});

test('kb search limits results to 6', function () {
    for ($i = 1; $i <= 8; $i++) {
        KbArticle::create([
            'company_id' => $this->company->id,
            'title' => "Guide Part {$i}",
            'slug' => "guide-part-{$i}",
            'body' => 'Content.',
            'status' => 'published',
        ]);
    }

    $component = Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('kbSearch', 'Guide');

    expect($component->instance()->kbResults)->toHaveCount(6);
});

test('insertKbArticle dispatches kb-insert event and resets search', function () {
    $this->ticket->load('company');

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('kbSearch', 'test')
        ->call('insertKbArticle', 'how-to-reset', 'How to Reset')
        ->assertDispatched('kb-insert')
        ->assertSet('kbSearch', '');
});

test('kb search does not return articles from other companies', function () {
    $otherCompany = Company::factory()->create();

    KbArticle::create([
        'company_id' => $otherCompany->id,
        'title' => 'Other Company Article',
        'slug' => 'other-company-article',
        'body' => 'Content.',
        'status' => 'published',
    ]);

    KbArticle::create([
        'company_id' => $this->company->id,
        'title' => 'My Company Article',
        'slug' => 'my-company-article',
        'body' => 'Content.',
        'status' => 'published',
    ]);

    $component = Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('kbSearch', 'Article');

    $results = $component->instance()->kbResults;

    expect($results)->toHaveCount(1)
        ->and($results->first()['title'])->toBe('My Company Article');
});
