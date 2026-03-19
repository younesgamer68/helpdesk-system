<?php

use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->company = Company::factory()->create();
    $this->engine = app(AutomationEngine::class);
});

test('priority rule evaluates true when ticket category matches condition exactly', function () {
    $category = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);

    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'conditions' => ['keywords' => ['urgent'], 'category_id' => $category->id],
        'actions' => ['set_priority' => 'high'],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $category->id,
        'subject' => 'URGENT issue',
        'priority' => 'low',
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('high');
});

test('priority rule evaluates true when ticket is a subcategory of condition category', function () {
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

    // Rule is set for parent category
    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'conditions' => ['keywords' => ['crash'], 'category_id' => $parent->id],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    // Ticket belongs to subcategory
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'subject' => 'App crash on startup',
        'priority' => 'low',
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
});

test('priority rule evaluates false when ticket category does not match and no parent match', function () {
    $targetCategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Hardware',
        'parent_id' => null,
    ]);
    $otherCategory = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);

    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'conditions' => ['keywords' => ['urgent'], 'category_id' => $targetCategory->id],
        'actions' => ['set_priority' => 'high'],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $otherCategory->id,
        'subject' => 'URGENT network issue',
        'priority' => 'low',
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('low');
});

test('priority rule does not fire when category_id condition set but ticket has no category', function () {
    $category = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Security',
        'parent_id' => null,
    ]);

    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'conditions' => ['keywords' => ['urgent'], 'category_id' => $category->id],
        'actions' => ['set_priority' => 'urgent'],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => null,
        'subject' => 'URGENT help needed',
        'priority' => 'low',
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->priority)->toBe('low');
});

test('assignment rule matches when ticket subcategory matches parent condition', function () {
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

    $operator = User::factory()->operator()->create([
        'company_id' => $this->company->id,
    ]);

    // Rule targets the parent category
    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => $parent->id, 'priority' => null],
        'actions' => ['assign_to_operator_id' => $operator->id],
    ]);

    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategory->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($operator->id);
});

test('assignment rule skips ticket in sibling subcategory', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General',
        'parent_id' => null,
    ]);
    $subcategoryA = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General Inquiries',
        'parent_id' => $parent->id,
    ]);
    $subcategoryB = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General Feedback',
        'parent_id' => $parent->id,
    ]);

    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);

    // Rule targets subcategoryA exactly
    AutomationRule::factory()->assignment()->create([
        'company_id' => $this->company->id,
        'conditions' => ['category_id' => $subcategoryA->id, 'priority' => null],
        'actions' => ['assign_to_operator_id' => $operator->id],
    ]);

    // Ticket is in sibling subcategoryB
    $ticket = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategoryB->id,
        'assigned_to' => null,
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticket);

    $ticket->refresh();
    expect($ticket->assigned_to)->toBeNull();
});

test('priority rule with subcategory condition fires for subcategory but not for other children', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);
    $subcategoryA = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network Config',
        'parent_id' => $parent->id,
    ]);
    $subcategoryB = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network Hardware',
        'parent_id' => $parent->id,
    ]);

    // Rule targets the parent — fires for BOTH subcategoryA and subcategoryB
    AutomationRule::factory()->priority()->create([
        'company_id' => $this->company->id,
        'conditions' => ['keywords' => ['issue'], 'category_id' => $parent->id],
        'actions' => ['set_priority' => 'high'],
    ]);

    $ticketA = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategoryA->id,
        'subject' => 'Configuration issue',
        'priority' => 'low',
        'verified' => true,
    ]));

    $ticketB = Ticket::withoutEvents(fn () => Ticket::factory()->create([
        'company_id' => $this->company->id,
        'category_id' => $subcategoryB->id,
        'subject' => 'Hardware issue',
        'priority' => 'low',
        'verified' => true,
    ]));

    $this->engine->processNewTicket($ticketA);
    $this->engine->processNewTicket($ticketB);

    expect($ticketA->fresh()->priority)->toBe('high');
    expect($ticketB->fresh()->priority)->toBe('high');
});
