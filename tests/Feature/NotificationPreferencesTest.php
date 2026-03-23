<?php

use App\Livewire\Settings\NotificationPreferences;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\ClientReplied;
use App\Notifications\InternalNoteAdded;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketReassigned;
use App\Notifications\TicketStatusChanged;
use App\Notifications\TicketSubmitted;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('notification preferences page renders for authenticated user', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test(NotificationPreferences::class)
        ->assertStatus(200)
        ->assertSee('Ticket Assigned')
        ->assertSee('Client Replied')
        ->assertSee('Internal Notes');
});

test('notification preferences mount with defaults when user has no saved preferences', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test(NotificationPreferences::class)
        ->assertSet('preferences.ticket_assigned', true)
        ->assertSet('preferences.ticket_reassigned', true)
        ->assertSet('preferences.client_replied', true)
        ->assertSet('preferences.status_changed', true)
        ->assertSet('preferences.internal_note', true)
        ->assertSet('preferences.ticket_submitted', true);
});

test('notification preferences mount with saved values when available', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'notification_preferences' => [
            'ticket_assigned' => false,
            'client_replied' => false,
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(NotificationPreferences::class)
        ->assertSet('preferences.ticket_assigned', false)
        ->assertSet('preferences.client_replied', false)
        ->assertSet('preferences.ticket_reassigned', true)
        ->assertSet('preferences.internal_note', true);
});

test('notification preferences can be saved', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test(NotificationPreferences::class)
        ->set('preferences.ticket_assigned', false)
        ->set('preferences.internal_note', true)
        ->call('save')
        ->assertDispatched('notification-preferences-saved');

    $user->refresh();
    expect($user->notification_preferences['ticket_assigned'])->toBeFalse();
    expect($user->notification_preferences['internal_note'])->toBeTrue();
});

test('wantsNotification returns true by default when no preferences set', function () {
    $user = User::factory()->make();

    expect($user->wantsNotification('ticket_assigned'))->toBeTrue();
    expect($user->wantsNotification('client_replied'))->toBeTrue();
});

test('wantsNotification returns false when preference is disabled', function () {
    $user = User::factory()->make([
        'notification_preferences' => ['ticket_assigned' => false],
    ]);

    expect($user->wantsNotification('ticket_assigned'))->toBeFalse();
    expect($user->wantsNotification('client_replied'))->toBeTrue();
});

test('notification via returns empty array when user opts out', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'notification_preferences' => ['ticket_assigned' => false],
    ]);

    $ticket = Ticket::factory()->create(['company_id' => $company->id]);
    $notification = new TicketAssigned($ticket);

    expect($notification->via($user))->toBe([]);
});

test('notification via returns channels when user opts in', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'notification_preferences' => ['ticket_assigned' => true],
    ]);

    $ticket = Ticket::factory()->create(['company_id' => $company->id]);
    $notification = new TicketAssigned($ticket);

    expect($notification->via($user))->toBe(['database', 'broadcast']);
});

test('all notification classes respect user preferences', function () {
    $company = Company::factory()->create();
    $ticket = Ticket::factory()->create(['company_id' => $company->id]);

    $cases = [
        ['class' => TicketAssigned::class, 'key' => 'ticket_assigned', 'args' => [$ticket]],
        ['class' => TicketReassigned::class, 'key' => 'ticket_reassigned', 'args' => [$ticket]],
        ['class' => ClientReplied::class, 'key' => 'client_replied', 'args' => [$ticket]],
        ['class' => InternalNoteAdded::class, 'key' => 'internal_note', 'args' => [$ticket]],
        ['class' => TicketSubmitted::class, 'key' => 'ticket_submitted', 'args' => [$ticket]],
    ];

    foreach ($cases as $case) {
        $userOptedOut = User::factory()->create([
            'company_id' => $company->id,
            'notification_preferences' => [$case['key'] => false],
        ]);

        $notification = new $case['class'](...$case['args']);
        expect($notification->via($userOptedOut))->toBe([])
            ->and($case['class'])->toBeString();
    }
});

test('TicketStatusChanged respects user preferences', function () {
    $company = Company::factory()->create();
    $ticket = Ticket::factory()->create(['company_id' => $company->id]);

    $userOptedOut = User::factory()->create([
        'company_id' => $company->id,
        'notification_preferences' => ['status_changed' => false],
    ]);

    $notification = new TicketStatusChanged($ticket, 'open', 'closed');
    expect($notification->via($userOptedOut))->toBe([]);

    $userOptedIn = User::factory()->create([
        'company_id' => $company->id,
        'notification_preferences' => ['status_changed' => true],
    ]);

    expect($notification->via($userOptedIn))->toBe(['database', 'broadcast']);
});

test('internal note notifies admins and assigned agent', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $assignedAgent = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => $assignedAgent->id,
        'status' => 'open',
    ]);

    $this->actingAs($operator);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'This is an internal note')
        ->call('addInternalNote');

    Notification::assertSentTo($admin, InternalNoteAdded::class);
    Notification::assertSentTo($assignedAgent, InternalNoteAdded::class);
    Notification::assertNotSentTo($operator, InternalNoteAdded::class);
});

test('internal note does not notify the author even if admin', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $otherAdmin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    $this->actingAs($admin);

    Livewire::test(\App\Livewire\Tickets\TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'Admin internal note')
        ->call('addInternalNote');

    Notification::assertSentTo($otherAdmin, InternalNoteAdded::class);
    Notification::assertNotSentTo($admin, InternalNoteAdded::class);
});
