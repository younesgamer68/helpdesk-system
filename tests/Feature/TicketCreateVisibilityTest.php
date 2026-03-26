<?php

use App\Livewire\Tickets\TicketsTable;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('creating ticket clears active filters so new ticket is visible', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('search', 'old search term')
        ->set('statusFilter', 'closed')
        ->set('priorityFilter', 'urgent')
        ->set('categoryFilter', $category->id)
        ->set('customer_name', 'John Doe')
        ->set('customer_email', 'john@example.com')
        ->set('subject', 'Brand new ticket')
        ->set('description', 'Some description')
        ->set('priority', 'medium')
        ->set('status', 'pending')
        ->call('createTicket')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertSet('priorityFilter', '')
        ->assertSet('categoryFilter', '')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('tickets', ['subject' => 'Brand new ticket']);
});

test('operator switches to all tab after creating ticket', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($operator);

    Livewire::test(TicketsTable::class)
        ->assertSet('ticketView', 'mine')
        ->set('customer_name', 'Jane Doe')
        ->set('customer_email', 'jane@example.com')
        ->set('subject', 'Operator created ticket')
        ->set('description', 'Description here')
        ->set('priority', 'medium')
        ->set('status', 'pending')
        ->call('createTicket')
        ->assertSet('ticketView', 'all')
        ->assertDispatched('show-toast');

    $ticket = Ticket::where('subject', 'Operator created ticket')->first();
    expect($ticket)->not->toBeNull();
});

test('admin stays on current view after creating ticket', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('customer_name', 'Admin Test')
        ->set('customer_email', 'admin-test@example.com')
        ->set('subject', 'Admin ticket')
        ->set('description', 'Admin description')
        ->set('priority', 'high')
        ->set('status', 'open')
        ->call('createTicket')
        ->assertSet('ticketView', 'mine')
        ->assertDispatched('show-toast');
});

test('tickets table keeps urgent priority left border color in dark mode', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Ticket::factory()->create([
        'company_id' => $company->id,
        'priority' => 'urgent',
        'verified' => true,
        'subject' => 'Urgent priority stripe test',
    ]);

    Livewire::test(TicketsTable::class)
        ->set('ticketView', 'all')
        ->assertSee('Urgent priority stripe test')
        ->assertSeeHtml('dark:border-l-red-500')
        ->assertSeeHtml('dark:border-b-zinc-800');
});
