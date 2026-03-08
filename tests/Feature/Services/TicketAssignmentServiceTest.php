<?php

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketAssignmentService;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->service = app(TicketAssignmentService::class);
});

test('assigns ticket to specialist with matching specialty', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $specialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => true,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator->id)->toBe($specialist->id);
    expect($ticket->fresh()->assigned_to)->toBe($specialist->id);
    expect($specialist->fresh()->assigned_tickets_count)->toBe(1);
});

test('assigns ticket to generalist when no specialist available', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $generalist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => null,
        'is_available' => true,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator->id)->toBe($generalist->id);
    expect($ticket->fresh()->assigned_to)->toBe($generalist->id);
});

test('assigns ticket to operator with lowest workload', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $busyOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => true,
        'assigned_tickets_count' => 5,
    ]);

    $freeOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => true,
        'assigned_tickets_count' => 1,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator->id)->toBe($freeOperator->id);
});

test('does not assign to unavailable operators', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => false,
    ]);

    $availableGeneralist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => null,
        'is_available' => true,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator->id)->toBe($availableGeneralist->id);
});

test('returns null when no operators are available', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => false,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator)->toBeNull();
    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('unassigns ticket and decrements counter', function () {
    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'assigned_tickets_count' => 3,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => $operator->id,
        'verified' => true,
    ]);

    $this->service->unassignTicket($ticket);

    expect($ticket->fresh()->assigned_to)->toBeNull();
    expect($operator->fresh()->assigned_tickets_count)->toBe(2);
});

test('reassigns ticket and updates counters correctly', function () {
    $oldOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'assigned_tickets_count' => 3,
    ]);

    $newOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'assigned_tickets_count' => 1,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'assigned_to' => $oldOperator->id,
        'verified' => true,
    ]);

    $this->service->reassignTicket($ticket, $newOperator);

    expect($ticket->fresh()->assigned_to)->toBe($newOperator->id);
    expect($oldOperator->fresh()->assigned_tickets_count)->toBe(2);
    expect($newOperator->fresh()->assigned_tickets_count)->toBe(2);
});

test('auto-assigns ticket when verified via observer', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => true,
    ]);

    // Create unverified ticket (won't be auto-assigned)
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    expect($ticket->assigned_to)->toBeNull();

    // Verify the ticket - should trigger auto-assignment
    $ticket->update(['verified' => true]);

    expect($ticket->fresh()->assigned_to)->toBe($operator->id);
});

test('auto-assigns verified ticket on creation via observer', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => true,
    ]);

    // Create verified ticket - should be auto-assigned immediately
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => true,
    ]);

    expect($ticket->assigned_to)->toBe($operator->id);
});

test('does not auto-assign when ticket already has assignment', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $manualOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
    ]);

    $autoOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'specialty_id' => $category->id,
        'is_available' => true,
    ]);

    // Create ticket with manual assignment
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => $manualOperator->id,
        'verified' => true,
    ]);

    // Should keep manual assignment
    expect($ticket->assigned_to)->toBe($manualOperator->id);
});

test('only assigns to operators in same company', function () {
    $otherCompany = Company::factory()->create();
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    // Operator in different company
    User::factory()->operator()->create([
        'company_id' => $otherCompany->id,
        'specialty_id' => $category->id,
        'is_available' => true,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator)->toBeNull();
});
