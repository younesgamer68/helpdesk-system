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
        'is_available' => true,
    ]);
    $specialist->categories()->attach($category->id);

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
        'is_available' => true,
    ]);
    $busyOperator->categories()->attach($category->id);

    // Give busy operator 3 open tickets in the same category
    Ticket::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => $busyOperator->id,
        'status' => 'open',
        'verified' => false,
    ]);

    $freeOperator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
    ]);
    $freeOperator->categories()->attach($category->id);

    // Give free operator 1 open ticket in the same category
    Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => $freeOperator->id,
        'status' => 'open',
        'verified' => false,
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

test('assigns to specialist with fewest open tickets in same category', function () {
    $categoryA = TicketCategory::factory()->create(['company_id' => $this->company->id]);
    $categoryB = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $operatorA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
    ]);
    $operatorA->categories()->attach($categoryA->id);

    $operatorB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
    ]);
    $operatorB->categories()->attach($categoryA->id);

    // Operator A has 3 open tickets in category A
    Ticket::factory()->count(3)->create([
        'company_id' => $this->company->id,
        'category_id' => $categoryA->id,
        'assigned_to' => $operatorA->id,
        'status' => 'open',
        'verified' => false,
    ]);

    // Operator B has 1 open ticket in category A, but 5 in category B (irrelevant)
    Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $categoryA->id,
        'assigned_to' => $operatorB->id,
        'status' => 'open',
        'verified' => false,
    ]);
    Ticket::factory()->count(5)->create([
        'company_id' => $this->company->id,
        'category_id' => $categoryB->id,
        'assigned_to' => $operatorB->id,
        'status' => 'open',
        'verified' => false,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $categoryA->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    // Should pick operator B (1 open in cat A) over operator A (3 open in cat A)
    expect($assignedOperator->id)->toBe($operatorB->id);
});

test('uses round-robin when operators have equal category workload', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $operatorA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'last_assigned_at' => now()->subHour(),
    ]);
    $operatorA->categories()->attach($category->id);

    $operatorB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'last_assigned_at' => null, // Never assigned → should go first
    ]);
    $operatorB->categories()->attach($category->id);

    // Both have 0 open tickets in this category
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    // Operator B has null last_assigned_at (treated as oldest) → gets ticket first
    expect($assignedOperator->id)->toBe($operatorB->id);
});

test('updates last_assigned_at on assignment', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'last_assigned_at' => null,
    ]);
    $operator->categories()->attach($category->id);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $this->service->assignTicket($ticket);

    expect($operator->fresh()->last_assigned_at)->not->toBeNull();
});

test('distributes 3 tickets fairly across operators with same specialty', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    // Create 3 operators with the same specialty, all available, never assigned
    $operatorA = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'last_assigned_at' => null,
    ]);
    $operatorA->categories()->attach($category->id);

    $operatorB = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'last_assigned_at' => null,
    ]);
    $operatorB->categories()->attach($category->id);

    $operatorC = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => true,
        'last_assigned_at' => null,
    ]);
    $operatorC->categories()->attach($category->id);

    // Ticket 1 → should go to first available (all tied, DB order = A)
    $ticket1 = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => false,
    ]);
    $assigned1 = $this->service->assignTicket($ticket1);
    expect($assigned1->id)->toBe($operatorA->id);

    // Ticket 2 → A has 1 open, B and C have 0 → goes to B (first with 0)
    $ticket2 = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => false,
    ]);
    $assigned2 = $this->service->assignTicket($ticket2);
    expect($assigned2->id)->toBe($operatorB->id);

    // Ticket 3 → A=1, B=1, C=0 → goes to C
    $ticket3 = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => false,
    ]);
    $assigned3 = $this->service->assignTicket($ticket3);
    expect($assigned3->id)->toBe($operatorC->id);

    // All 3 operators now have exactly 1 open ticket each
    // Verify all 3 assigned to different operators
    $assignedIds = [$assigned1->id, $assigned2->id, $assigned3->id];
    expect(array_unique($assignedIds))->toHaveCount(3);

    // Ticket 4 → all have 1 open ticket, round-robin: A was assigned first → A gets it
    $ticket4 = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'status' => 'open',
        'verified' => false,
    ]);
    $assigned4 = $this->service->assignTicket($ticket4);
    expect($assigned4->id)->toBe($operatorA->id);
});

test('does not assign to unavailable operators', function () {
    $category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $unavailableSpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'is_available' => false,
    ]);
    $unavailableSpecialist->categories()->attach($category->id);

    $availableGeneralist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
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
        'is_available' => true,
    ]);
    $operator->categories()->attach($category->id);

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
        'is_available' => true,
    ]);
    $operator->categories()->attach($category->id);

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
        'is_available' => true,
    ]);
    $autoOperator->categories()->attach($category->id);

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
    $otherCompanyOperator = User::factory()->operator()->create([
        'company_id' => $otherCompany->id,
        'is_available' => true,
    ]);
    $otherCompanyOperator->categories()->attach($category->id);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    expect($assignedOperator)->toBeNull();
});

test('assigns ticket matching specific constraints (available, online, <10 tickets, lowest workload)', function () {
    $categoryBilling = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Billing']);
    $categoryTechnical = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Technical']);
    $categoryGeneral = TicketCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'General']);

    // Sara : Billing, General, Disponible ✅, Tickets ouverts 2
    $sara = User::factory()->operator()->create([
        'name' => 'Sara',
        'company_id' => $this->company->id,
        'is_available' => true,
        'status' => 'online',
        'assigned_tickets_count' => 2,
    ]);
    $sara->categories()->attach([$categoryBilling->id, $categoryGeneral->id]);
    Ticket::factory()->count(2)->create(['company_id' => $this->company->id, 'category_id' => $categoryBilling->id, 'assigned_to' => $sara->id, 'status' => 'open', 'verified' => false]);

    // Ahmed : Technical, Disponible ✅, Tickets ouverts 1
    $ahmed = User::factory()->operator()->create([
        'name' => 'Ahmed',
        'company_id' => $this->company->id,
        'is_available' => true,
        'status' => 'online',
        'assigned_tickets_count' => 1,
    ]);
    $ahmed->categories()->attach($categoryTechnical->id);
    Ticket::factory()->count(1)->create(['company_id' => $this->company->id, 'category_id' => $categoryTechnical->id, 'assigned_to' => $ahmed->id, 'status' => 'open', 'verified' => false]);

    // Karim : Technical, Billing, Disponible ✅, Tickets ouverts 3
    $karim = User::factory()->operator()->create([
        'name' => 'Karim',
        'company_id' => $this->company->id,
        'is_available' => true,
        'status' => 'online',
        'assigned_tickets_count' => 3,
    ]);
    $karim->categories()->attach([$categoryTechnical->id, $categoryBilling->id]);
    Ticket::factory()->count(3)->create(['company_id' => $this->company->id, 'category_id' => $categoryBilling->id, 'assigned_to' => $karim->id, 'status' => 'open', 'verified' => false]);

    // Fatima : General, Disponible ❌, Tickets ouverts 0
    $fatima = User::factory()->operator()->create([
        'name' => 'Fatima',
        'company_id' => $this->company->id,
        'is_available' => false,
        'status' => 'offline',
        'assigned_tickets_count' => 0,
    ]);
    $fatima->categories()->attach($categoryGeneral->id);

    // Bob : Billing, Disponible ✅, Tickets ouverts 10 (Should not be assigned because >= 10 tickets)
    $bob = User::factory()->operator()->create([
        'name' => 'Bob',
        'company_id' => $this->company->id,
        'is_available' => true,
        'status' => 'online',
        'assigned_tickets_count' => 10,
    ]);
    $bob->categories()->attach($categoryBilling->id);
    Ticket::factory()->count(10)->create(['company_id' => $this->company->id, 'category_id' => $categoryBilling->id, 'assigned_to' => $bob->id, 'status' => 'open', 'verified' => false]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $categoryBilling->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assignedOperator = $this->service->assignTicket($ticket);

    // Sara should get it because she has 2 open tickets (less than 10) and lowest workload compared to Karim (3). Bob is ignored (10 >= 10), Ahmed is Technical, Fatima is offline.
    expect($assignedOperator->id)->toBe($sara->id);
    expect($assignedOperator->name)->toBe('Sara');
});
