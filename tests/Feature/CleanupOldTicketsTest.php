<?php

use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->admin = User::factory()->create(['company_id' => $this->company->id, 'role' => 'admin']);
    $this->actingAs($this->admin);

    SlaPolicy::create([
        'company_id' => $this->company->id,
        'is_enabled' => true,
        'soft_delete_days' => 30,
        'hard_delete_days' => 90,
    ]);
});

test('soft-deletes closed tickets past soft_delete_days', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'closed',
        'closed_at' => now()->subDays(35),
    ]);

    $this->artisan('app:cleanup-old-tickets')->assertExitCode(0);

    expect(Ticket::find($ticket->id))->toBeNull();
    expect(Ticket::withTrashed()->find($ticket->id))->not->toBeNull();
});

test('does not soft-delete closed tickets within soft_delete_days', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'closed',
        'closed_at' => now()->subDays(10),
    ]);

    $this->artisan('app:cleanup-old-tickets')->assertExitCode(0);

    expect(Ticket::find($ticket->id))->not->toBeNull();
});

test('hard-deletes soft-deleted tickets past hard_delete_days', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'closed',
        'closed_at' => now()->subDays(35),
    ]);

    $ticket->delete();
    $ticket->update(['deleted_at' => now()->subDays(95)]);

    $this->artisan('app:cleanup-old-tickets')->assertExitCode(0);

    expect(Ticket::withTrashed()->find($ticket->id))->toBeNull();
});

test('does not hard-delete soft-deleted tickets within hard_delete_days', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'closed',
        'closed_at' => now()->subDays(35),
    ]);

    $ticket->delete();

    $this->artisan('app:cleanup-old-tickets')->assertExitCode(0);

    expect(Ticket::withTrashed()->find($ticket->id))->not->toBeNull();
});

test('does not affect non-closed tickets', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'open',
    ]);

    $this->artisan('app:cleanup-old-tickets')->assertExitCode(0);

    expect(Ticket::find($ticket->id))->not->toBeNull();
});
