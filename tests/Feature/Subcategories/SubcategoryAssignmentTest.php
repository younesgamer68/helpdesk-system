<?php

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketAssignmentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->company = Company::factory()->create();
    $this->service = app(TicketAssignmentService::class);
});

test('assigns to exact subcategory specialist first', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software Bugs',
        'parent_id' => $parent->id,
    ]);

    $parentSpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $parentSpecialist->categories()->attach($parent->id);

    $subcategorySpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $subcategorySpecialist->categories()->attach($subcategory->id);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assigned = $this->service->assignTicket($ticket);

    expect($assigned->id)->toBe($subcategorySpecialist->id);
});

test('falls back to parent category specialist when no subcategory specialist', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Hardware',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Printers',
        'parent_id' => $parent->id,
    ]);

    $parentSpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $parentSpecialist->categories()->attach($parent->id);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assigned = $this->service->assignTicket($ticket);

    expect($assigned->id)->toBe($parentSpecialist->id);
});

test('falls back to generalist when no parent or subcategory specialist', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network Config',
        'parent_id' => $parent->id,
    ]);

    // No specialist for Network or Network Config - only a generalist
    $generalist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assigned = $this->service->assignTicket($ticket);

    expect($assigned->id)->toBe($generalist->id);
});

test('subcategory specialist preferred over parent specialist with more workload', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Security',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Security Audit',
        'parent_id' => $parent->id,
    ]);

    // Parent specialist with zero workload
    $parentSpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $parentSpecialist->categories()->attach($parent->id);

    // Subcategory specialist with higher workload
    $subcategorySpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 5,
    ]);
    $subcategorySpecialist->categories()->attach($subcategory->id);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assigned = $this->service->assignTicket($ticket);

    // Exact subcategory match preferred (Pass 1) even though higher workload
    expect($assigned->id)->toBe($subcategorySpecialist->id);
});

test('parent category ticket not assigned to subcategory specialist', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Database',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Database Backups',
        'parent_id' => $parent->id,
    ]);

    // Only a subcategory specialist
    $subcategorySpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);
    $subcategorySpecialist->categories()->attach($subcategory->id);

    // A generalist
    $generalist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 0,
    ]);

    // Ticket is for the PARENT category
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $parent->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assigned = $this->service->assignTicket($ticket);

    // Pass 1 looks for specialists in "Database" - subcategorySpecialist has "Database Backups"
    // Pass 2 doesn't apply because parent has no parent_id
    // Falls back to generalist
    expect($assigned->id)->toBe($generalist->id);
});

test('multiple subcategory specialists assigned by workload', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General',
        'parent_id' => null,
    ]);
    $subcategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General Inquiries',
        'parent_id' => $parent->id,
    ]);

    $busySpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 5,
        'last_assigned_at' => now()->subMinutes(5),
    ]);
    $busySpecialist->categories()->attach($subcategory->id);
    // Create open tickets so the query-level open_category_tickets_count reflects workload
    Ticket::factory()->count(5)->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => $busySpecialist->id,
        'status' => 'open',
        'verified' => false,
    ]);

    $freeSpecialist = User::factory()->operator()->create([
        'company_id' => $this->company->id,
        'assigned_tickets_count' => 1,
        'last_assigned_at' => now()->subMinutes(10),
    ]);
    $freeSpecialist->categories()->attach($subcategory->id);
    Ticket::factory()->count(1)->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => $freeSpecialist->id,
        'status' => 'open',
        'verified' => false,
    ]);

    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => false,
    ]);

    $assigned = $this->service->assignTicket($ticket);

    expect($assigned->id)->toBe($freeSpecialist->id);
});
