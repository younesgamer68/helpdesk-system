<?php

use App\Mail\TicketClosed;
use App\Mail\TicketClosedWarning;
use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

test('sends closure warning for resolved tickets approaching auto-close', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'resolved',
        'resolved_at' => now()->subHours(30), // 30hrs ago, warning threshold at 24hrs (48-24=24hrs)
        'warning_sent_at' => null,
    ]);

    $this->artisan('app:process-ticket-lifecycle')->assertExitCode(0);

    expect($ticket->fresh()->warning_sent_at)->not->toBeNull();
    Mail::assertQueued(TicketClosedWarning::class);
});

test('does not send warning if already sent', function () {
    Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'resolved',
        'resolved_at' => now()->subHours(30),
        'warning_sent_at' => now()->subHours(1),
    ]);

    $this->artisan('app:process-ticket-lifecycle')->assertExitCode(0);

    Mail::assertNotQueued(TicketClosedWarning::class);
});

test('does not send warning for tickets not yet reaching warning threshold', function () {
    Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'resolved',
        'resolved_at' => now()->subHours(10), // only 10hrs ago, warning at 24hrs mark
        'warning_sent_at' => null,
    ]);

    $this->artisan('app:process-ticket-lifecycle')->assertExitCode(0);

    Mail::assertNotQueued(TicketClosedWarning::class);
});

test('auto-closes resolved tickets past auto_close_hours', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'resolved',
        'resolved_at' => now()->subHours(50), // past 48hr threshold
        'warning_sent_at' => now()->subHours(26),
    ]);

    $this->artisan('app:process-ticket-lifecycle')->assertExitCode(0);

    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe('closed');
    expect($fresh->closed_at)->not->toBeNull();
    expect($fresh->close_reason)->toBe('auto_closed');

    Mail::assertQueued(TicketClosed::class);
});

test('does not auto-close tickets resolved less than auto_close_hours ago', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'resolved',
        'resolved_at' => now()->subHours(10),
        'warning_sent_at' => null,
    ]);

    $this->artisan('app:process-ticket-lifecycle')->assertExitCode(0);

    expect($ticket->fresh()->status)->toBe('resolved');
});

test('creates auto_closed log entry when auto-closing', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'resolved',
        'resolved_at' => now()->subHours(50),
        'warning_sent_at' => now()->subHours(26),
    ]);

    $this->artisan('app:process-ticket-lifecycle')->assertExitCode(0);

    expect($ticket->fresh()->logs()->where('action', 'auto_closed')->exists())->toBeTrue();
});
