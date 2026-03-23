<?php

use App\Livewire\Tickets\Widget\TicketConversation;
use App\Mail\TicketClosed;
use App\Mail\TicketResolved;
use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->company = Company::factory()->create();
    $this->admin = User::factory()->create(['company_id' => $this->company->id, 'role' => 'admin']);
    $this->actingAs($this->admin);

    SlaPolicy::create([
        'company_id' => $this->company->id,
        'is_enabled' => true,
        'warning_hours' => 24,
        'auto_close_hours' => 48,
        'reopen_hours' => 48,
        'linked_ticket_days' => 7,
        'soft_delete_days' => 30,
        'hard_delete_days' => 90,
    ]);
});

test('resolving ticket sends TicketResolved email and logs resolved action', function () {
    $ticket = Ticket::factory()->open()->create(['company_id' => $this->company->id]);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->call('resolve');

    Mail::assertQueued(TicketResolved::class);

    expect($ticket->fresh()->status)->toBe('resolved');
    expect($ticket->fresh()->resolved_at)->not->toBeNull();
    expect($ticket->fresh()->logs()->where('action', 'resolved')->exists())->toBeTrue();
});

test('unresolving ticket clears warning_sent_at', function () {
    $ticket = Ticket::factory()->resolved()->create([
        'company_id' => $this->company->id,
        'warning_sent_at' => now()->subHours(2),
    ]);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->call('unresolve');

    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe('open');
    expect($fresh->warning_sent_at)->toBeNull();
});

test('closing ticket manually sends TicketClosed email and sets close_reason to manual', function () {
    $ticket = Ticket::factory()->open()->create(['company_id' => $this->company->id]);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->call('closeTicket');

    Mail::assertQueued(TicketClosed::class);

    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe('closed');
    expect($fresh->close_reason)->toBe('manual');
    expect($fresh->logs()->where('action', 'manually_closed')->exists())->toBeTrue();
});

test('client can reopen resolved ticket within reopen window via widget', function () {
    $ticket = Ticket::factory()->resolved()->create([
        'company_id' => $this->company->id,
        'resolved_at' => now()->subHours(10), // within 48hr window
    ]);

    Livewire::test(TicketConversation::class, ['ticket' => $ticket])
        ->set('message', 'My issue is not actually fixed.')
        ->call('submitReply');

    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe('open');
    expect($fresh->resolved_at)->toBeNull();
    expect($fresh->warning_sent_at)->toBeNull();
});

test('client cannot reopen resolved ticket past reopen window via widget', function () {
    $ticket = Ticket::factory()->resolved()->create([
        'company_id' => $this->company->id,
        'resolved_at' => now()->subHours(72), // past 48hr window
    ]);

    Livewire::test(TicketConversation::class, ['ticket' => $ticket])
        ->set('message', 'Still having issues.')
        ->call('submitReply');

    expect($ticket->fresh()->status)->toBe('resolved');
});

test('client can initiate follow-up ticket for closed ticket within linked_ticket_days', function () {
    $ticket = Ticket::factory()->closed()->create([
        'company_id' => $this->company->id,
        'closed_at' => now()->subDays(3), // within 7-day window
    ]);

    Livewire::test(TicketConversation::class, ['ticket' => $ticket])
        ->set('message', 'I need follow-up help.')
        ->call('submitReply'); // first call sets confirmLinkedTicket = true

    expect($ticket->fresh()->status)->toBe('closed'); // original unchanged

    Livewire::test(TicketConversation::class, ['ticket' => $ticket])
        ->set('message', 'I need follow-up help.')
        ->set('confirmLinkedTicket', true)
        ->call('submitReply'); // second call creates linked ticket

    $linked = Ticket::where('parent_ticket_id', $ticket->id)->first();
    expect($linked)->not->toBeNull();
    expect($linked->subject)->toBe('Follow-up: '.$ticket->subject);
});

test('client cannot create linked ticket for closed ticket past linked_ticket_days', function () {
    $ticket = Ticket::factory()->closed()->create([
        'company_id' => $this->company->id,
        'closed_at' => now()->subDays(10), // past 7-day window
    ]);

    Livewire::test(TicketConversation::class, ['ticket' => $ticket])
        ->set('message', 'Still need help.')
        ->call('submitReply');

    expect(Ticket::where('parent_ticket_id', $ticket->id)->count())->toBe(0);
});

test('resolving ticket without tracking_token generates token and sends email', function () {
    $ticket = Ticket::factory()->open()->create([
        'company_id' => $this->company->id,
        'tracking_token' => null,
    ]);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->call('resolve');

    Mail::assertQueued(TicketResolved::class);
    expect($ticket->fresh()->status)->toBe('resolved');
    expect($ticket->fresh()->tracking_token)->not->toBeNull();
});

test('closing ticket manually without tracking_token generates token and sends email', function () {
    $ticket = Ticket::factory()->open()->create([
        'company_id' => $this->company->id,
        'tracking_token' => null,
    ]);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->call('closeTicket');

    Mail::assertQueued(TicketClosed::class);
    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe('closed');
    expect($fresh->close_reason)->toBe('manual');
    expect($fresh->tracking_token)->not->toBeNull();
});
