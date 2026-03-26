<?php

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;

it('loads operator dashboard without lazy-loading errors when pending tickets are auto-assigned', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);

    $operator = User::factory()->operator()->create([
        'company_id' => $company->id,
        'status' => 'offline',
        'is_available' => true,
        'assigned_tickets_count' => 0,
    ]);

    $category = TicketCategory::factory()->create([
        'company_id' => $company->id,
    ]);

    Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => true,
        'status' => 'open',
    ]);

    $this->actingAs($operator)
        ->get(route('agent.dashboard', ['company' => $company->slug]))
        ->assertOk();
});
