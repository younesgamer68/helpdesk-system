<?php

use App\Livewire\NotificationBell;
use App\Livewire\Notifications\NotificationsPage;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('notifications page dispatches event when clearing or marking read', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create(['company_id' => $company->id]);

    // Add a dummy notification
    $user->notify(new \App\Notifications\TicketAssigned($ticket));

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->call('markAllRead')
        ->assertDispatched('notifications-updated')
        ->call('clearAll')
        ->assertDispatched('notifications-updated');
});

test('notification bell listens for updates', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->dispatch('notifications-updated')
        ->assertStatus(200); // Verify it doesn't crash and handles the event
});

test('notification bell dispatches event when marking read', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create(['company_id' => $company->id]);

    $user->notify(new \App\Notifications\TicketAssigned($ticket));

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->call('markAllRead')
        ->assertDispatched('notifications-updated');
});
