<?php

use App\Livewire\Notifications\NotificationsPage;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createOnboardedUser(string $role = 'admin'): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->{$role}()->create(['company_id' => $company->id]);

    return [$user, $company];
}

function createNotification(User $user, string $type, ?string $readAt = null): DatabaseNotification
{
    return DatabaseNotification::create([
        'id' => fake()->uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => [
            'ticket_id' => 1,
            'ticket_number' => 'TKT-TEST01',
            'subject' => 'Test ticket subject',
            'type' => $type,
            'message' => "Test {$type} notification",
        ],
        'read_at' => $readAt,
    ]);
}

it('renders the notifications page for authenticated users', function () {
    [$user, $company] = createOnboardedUser();

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/notifications')
        ->assertOk();
});

it('redirects guests to the login page', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);

    get("http://{$company->slug}.".config('app.domain').'/notifications')
        ->assertRedirect(route('login'));
});

it('defaults to all tab and shows all notifications', function () {
    [$user] = createOnboardedUser();
    createNotification($user, 'assigned');
    createNotification($user, 'client_replied');

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->assertSet('activeTab', 'all')
        ->assertSee('Test assigned notification')
        ->assertSee('Test client_replied notification');
});

it('filters unread notifications', function () {
    [$user] = createOnboardedUser();
    createNotification($user, 'assigned');
    createNotification($user, 'client_replied', now()->toDateTimeString());

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->call('setTab', 'unread')
        ->assertSee('Test assigned notification')
        ->assertDontSee('Test client_replied notification');
});

it('filters assigned notifications', function () {
    [$user] = createOnboardedUser();
    createNotification($user, 'assigned');
    createNotification($user, 'reassigned');
    createNotification($user, 'client_replied');

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->call('setTab', 'assigned')
        ->assertSee('Test assigned notification')
        ->assertSee('Test reassigned notification')
        ->assertDontSee('Test client_replied notification');
});

it('filters replies notifications', function () {
    [$user] = createOnboardedUser();
    createNotification($user, 'client_replied');
    createNotification($user, 'assigned');

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->call('setTab', 'replies')
        ->assertSee('Test client_replied notification')
        ->assertDontSee('Test assigned notification');
});

it('shows system tab only for admin users', function () {
    [$admin] = createOnboardedUser('admin');
    [$operator] = createOnboardedUser('operator');

    Livewire::actingAs($admin)
        ->test(NotificationsPage::class)
        ->assertSee('System')
        ->assertSee('SLA');

    Livewire::actingAs($operator)
        ->test(NotificationsPage::class)
        ->assertDontSee('System')
        ->assertDontSee('SLA');
});

it('prevents operators from accessing system tab', function () {
    [$operator] = createOnboardedUser('operator');

    Livewire::actingAs($operator)
        ->test(NotificationsPage::class)
        ->call('setTab', 'system')
        ->assertSet('activeTab', 'all');
});

it('prevents operators from accessing sla tab', function () {
    [$operator] = createOnboardedUser('operator');

    Livewire::actingAs($operator)
        ->test(NotificationsPage::class)
        ->call('setTab', 'sla')
        ->assertSet('activeTab', 'all');
});

it('filters sla notifications for admins', function () {
    [$admin] = createOnboardedUser('admin');
    createNotification($admin, 'sla_breached');
    createNotification($admin, 'assigned');

    Livewire::actingAs($admin)
        ->test(NotificationsPage::class)
        ->call('setTab', 'sla')
        ->assertSee('Test sla_breached notification')
        ->assertDontSee('Test assigned notification');
});

it('marks all notifications as read', function () {
    [$user] = createOnboardedUser();
    createNotification($user, 'assigned');
    createNotification($user, 'client_replied');

    expect($user->unreadNotifications()->count())->toBe(2);

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->call('markAllRead');

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('marks a single notification as read and redirects', function () {
    [$user, $company] = createOnboardedUser();
    $notification = createNotification($user, 'assigned');

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->call('markRead', $notification->id)
        ->assertRedirect(route('details', [
            'company' => $company->slug,
            'ticket' => 'TKT-TEST01',
        ]));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('loads more notifications', function () {
    [$user] = createOnboardedUser();

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->assertSet('perPage', 20)
        ->call('loadMore')
        ->assertSet('perPage', 40);
});

it('shows empty state when no notifications exist', function () {
    [$user] = createOnboardedUser();

    Livewire::actingAs($user)
        ->test(NotificationsPage::class)
        ->assertSee("You're all caught up!", false)
        ->assertSee('No notifications here');
});
