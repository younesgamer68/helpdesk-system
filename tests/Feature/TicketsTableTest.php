<?php

use App\Livewire\Dashboard\TicketsTable;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('tickets table renders successfully', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->assertStatus(200);
});

test('tickets table applies unassigned high priority preset', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->call('applyPreset', 'unassigned_high')
        ->assertSet('priorityFilter', 'high')
        ->assertSet('statusFilter', 'open')
        ->assertSet('assignedFilter', '');
});

test('tickets table saves custom view', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('search', 'Test Search')
        ->set('statusFilter', 'open')
        ->set('customViewName', 'My Custom View')
        ->call('saveCustomView')
        ->assertDispatched('show-toast', message: 'View saved successfully!', type: 'success')
        ->assertSet('customViewName', '')
        ->assertSet('showSaveViewModal', false);

    $this->assertDatabaseHas('saved_filter_views', [
        'user_id' => $admin->id,
        'name' => 'My Custom View',
    ]);
});

test('tickets table applies custom saved view', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $view = \App\Models\SavedFilterView::create([
        'user_id' => $admin->id,
        'name' => 'Legacy View',
        'filters' => ['search' => 'legacy', 'statusFilter' => 'closed'],
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->call('applyPreset', (string) $view->id)
        ->assertSet('search', 'legacy')
        ->assertSet('statusFilter', 'closed');
});

test('tickets table deletes saved view', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $view = \App\Models\SavedFilterView::create([
        'user_id' => $admin->id,
        'name' => 'To Delete',
        'filters' => [],
    ]);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->call('deleteSavedView', $view->id)
        ->assertDispatched('show-toast', message: 'View removed successfully!', type: 'success');

    $this->assertDatabaseMissing('saved_filter_views', ['id' => $view->id]);
});

test('tickets table filters for deleted tickets', function () {
    $company = \App\Models\Company::factory()->create();
    $admin = \App\Models\User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $activeTicket = \App\Models\Ticket::factory()->create(['company_id' => $company->id, 'verified' => true]);
    $deletedTicket = \App\Models\Ticket::factory()->create(['company_id' => $company->id, 'verified' => true]);
    $deletedTicket->delete();

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('showDeletedOnly', true)
        ->assertSee($deletedTicket->ticket_number)
        ->assertDontSee($activeTicket->ticket_number);
});

test('tickets table restores deleted ticket', function () {
    $company = \App\Models\Company::factory()->create();
    $admin = \App\Models\User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $ticket = \App\Models\Ticket::factory()->create(['company_id' => $company->id, 'verified' => true]);
    $ticket->delete();

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->call('restoreTicket', $ticket->id)
        ->assertDispatched('show-toast', message: "Ticket #{$ticket->ticket_number} restored successfully!", type: 'success');

    expect($ticket->fresh())->not->toBeNull();
    expect($ticket->fresh()->deleted_at)->toBeNull();
});

test('bulk status update', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $tickets = Ticket::factory()->count(3)->create(['company_id' => $company->id, 'status' => 'pending', 'verified' => true]);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('selectedTickets', $tickets->pluck('id')->map(fn ($id) => (string) $id)->toArray())
        ->call('bulkSetStatus', 'resolved')
        ->assertDispatched('show-toast');

    foreach ($tickets as $ticket) {
        expect($ticket->fresh()->status)->toBe('resolved');
    }
});

test('bulk priority update', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $tickets = Ticket::factory()->count(3)->create(['company_id' => $company->id, 'priority' => 'low', 'verified' => true]);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('selectedTickets', $tickets->pluck('id')->map(fn ($id) => (string) $id)->toArray())
        ->call('bulkSetPriority', 'high')
        ->assertDispatched('show-toast');

    foreach ($tickets as $ticket) {
        expect($ticket->fresh()->priority)->toBe('high');
    }
});

test('bulk assignment', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $agent = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $tickets = Ticket::factory()->count(3)->create(['company_id' => $company->id, 'assigned_to' => null, 'verified' => true]);

    $this->actingAs($admin);

    Livewire::test(TicketsTable::class)
        ->set('selectedTickets', $tickets->pluck('id')->map(fn ($id) => (string) $id)->toArray())
        ->call('bulkAssignAgent', $agent->id)
        ->assertDispatched('show-toast');

    foreach ($tickets as $ticket) {
        expect($ticket->fresh()->assigned_to)->toBe($agent->id);
    }
});
