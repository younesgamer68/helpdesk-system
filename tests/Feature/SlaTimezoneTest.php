<?php

use App\Console\Commands\CheckSlaBreaches;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->company = Company::factory()->create(['timezone' => 'America/New_York']);
    $this->admin = User::factory()->admin()->create(['company_id' => $this->company->id]);
    $this->category = TicketCategory::factory()->create(['company_id' => $this->company->id]);
    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
    ]);

    SlaPolicy::create([
        'company_id' => $this->company->id,
        'is_enabled' => true,
        'low_minutes' => 1440,
        'medium_minutes' => 480,
        'high_minutes' => 120,
        'urgent_minutes' => 60,
    ]);
});

it('calculates SLA due_time using company timezone when creating a ticket', function () {
    Carbon::setTestNow(now());

    $ticket = Ticket::create([
        'company_id' => $this->company->id,
        'ticket_number' => 'TKT-TZ-001',
        'customer_id' => $this->customer->id,
        'subject' => 'Timezone test',
        'description' => 'Test',
        'category_id' => $this->category->id,
        'priority' => 'urgent',
        'status' => 'open',
        'verified' => true,
    ]);

    // Urgent = 60 minutes, due_time should be ~60 minutes from now
    expect($ticket->due_time)->not->toBeNull();

    $expectedDue = now()->addMinutes(60);
    $diffSeconds = abs($ticket->due_time->diffInSeconds($expectedDue));

    // Allow 2 seconds tolerance for test execution time
    expect($diffSeconds)->toBeLessThan(2);

    Carbon::setTestNow();
});

it('recalculates SLA due_time with company timezone on priority change', function () {
    Carbon::setTestNow(now());

    $ticket = Ticket::create([
        'company_id' => $this->company->id,
        'ticket_number' => 'TKT-TZ-002',
        'customer_id' => $this->customer->id,
        'subject' => 'Priority change test',
        'description' => 'Test',
        'category_id' => $this->category->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
    ]);

    $originalDue = $ticket->due_time;

    // Change priority to urgent - should recalculate
    $ticket->update(['priority' => 'urgent']);
    $ticket->refresh();

    $expectedDue = now()->addMinutes(60);
    $diffSeconds = abs($ticket->due_time->diffInSeconds($expectedDue));

    expect($diffSeconds)->toBeLessThan(2);
    expect($ticket->due_time->lessThan($originalDue))->toBeTrue();

    Carbon::setTestNow();
});

it('resolves SLA breach status using company timezone', function () {
    // Create a ticket with a due_time in the past
    $ticket = Ticket::create([
        'company_id' => $this->company->id,
        'ticket_number' => 'TKT-TZ-003',
        'customer_id' => $this->customer->id,
        'subject' => 'Breach test',
        'description' => 'Test',
        'category_id' => $this->category->id,
        'priority' => 'urgent',
        'status' => 'open',
        'verified' => true,
    ]);

    // Set due_time to 1 hour in the past to simulate breach
    $ticket->update([
        'due_time' => now()->subHour(),
        'sla_status' => 'on_time',
    ]);

    // Load the company relationship for the breach check
    $ticket->load('company:id,timezone');

    $command = new CheckSlaBreaches;
    $reflection = new ReflectionMethod($command, 'resolveSlaStatus');
    $status = $reflection->invoke($command, $ticket);

    expect($status)->toBe('breached');
});

it('resolves SLA on_time status using company timezone', function () {
    $ticket = Ticket::create([
        'company_id' => $this->company->id,
        'ticket_number' => 'TKT-TZ-004',
        'customer_id' => $this->customer->id,
        'subject' => 'On time test',
        'description' => 'Test',
        'category_id' => $this->category->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
    ]);

    // Set due_time to far in the future
    $ticket->update([
        'due_time' => now()->addDay(),
        'sla_status' => 'on_time',
    ]);

    $ticket->load('company:id,timezone');

    $command = new CheckSlaBreaches;
    $reflection = new ReflectionMethod($command, 'resolveSlaStatus');
    $status = $reflection->invoke($command, $ticket);

    expect($status)->toBe('on_time');
});

it('uses UTC as fallback when company has no timezone set', function () {
    // Create company without explicit timezone
    $company = Company::factory()->create(['timezone' => null]);
    $user = User::factory()->admin()->create(['company_id' => $company->id]);
    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => 'Null TZ Customer',
        'email' => 'nulltz@example.com',
    ]);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    Carbon::setTestNow(now());

    $ticket = Ticket::create([
        'company_id' => $company->id,
        'ticket_number' => 'TKT-TZ-005',
        'customer_id' => $customer->id,
        'subject' => 'Null timezone test',
        'description' => 'Test',
        'category_id' => $category->id,
        'priority' => 'high',
        'status' => 'open',
        'verified' => true,
    ]);

    // Should still calculate due_time using UTC fallback (120 min default for high)
    expect($ticket->due_time)->not->toBeNull();

    $expectedDue = now('UTC')->addMinutes(120);
    $diffSeconds = abs($ticket->due_time->diffInSeconds($expectedDue));

    expect($diffSeconds)->toBeLessThan(2);

    Carbon::setTestNow();
});
