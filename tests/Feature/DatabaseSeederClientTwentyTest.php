<?php

use App\Models\Customer;
use App\Models\Ticket;
use Database\Seeders\DatabaseSeeder;

test('database seeder creates deterministic client with exactly 20 tickets', function () {
    $this->seed(DatabaseSeeder::class);

    $customer = Customer::query()->where('email', 'client20@example.com')->first();

    expect($customer)->not->toBeNull();

    $ticketCount = Ticket::query()
        ->where('customer_id', $customer->id)
        ->where('ticket_number', 'like', 'TKT-CLIENT20-%')
        ->count();

    expect($ticketCount)->toBe(20);
});
