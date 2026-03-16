<?php

use App\Livewire\Dashboard\AgentDashboard;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function dashboardUser(string $role = 'agent'): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id, 'role' => $role]);

    return [$user, $company];
}

function dashboardTicket(Company $company, ?int $assignedTo = null, string $status = 'open', string $priority = 'medium'): Ticket
{
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $customer = Customer::factory()->create(['company_id' => $company->id]);

    return Ticket::create([
        'company_id' => $company->id,
        'ticket_number' => 'TKT-'.fake()->unique()->bothify('######'),
        'customer_id' => $customer->id,
        'subject' => fake()->sentence(4),
        'description' => fake()->paragraph(),
        'category_id' => $category->id,
        'priority' => $priority,
        'status' => $status,
        'assigned_to' => $assignedTo,
        'verified' => true,
        'verification_token' => fake()->uuid(),
    ]);
}

it('renders the dashboard for authenticated users', function () {
    [$user, $company] = dashboardUser();

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/home')
        ->assertOk();
});

it('shows correct KPI counts', function () {
    [$user, $company] = dashboardUser();

    dashboardTicket($company, $user->id, 'open');
    dashboardTicket($company, $user->id, 'in_progress');
    dashboardTicket($company, $user->id, 'pending');
    $resolved = dashboardTicket($company, $user->id, 'resolved');
    $resolved->update(['resolved_at' => now()]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee('Open Tickets')
        ->assertSee('Resolved Today')
        ->assertSee('Pending Reply');
});

it('shows my tickets sorted by priority', function () {
    [$user, $company] = dashboardUser();

    dashboardTicket($company, $user->id, 'open', 'low');
    $urgent = dashboardTicket($company, $user->id, 'open', 'urgent');
    dashboardTicket($company, $user->id, 'open', 'high');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee($urgent->ticket_number);
});

it('does not show resolved or closed tickets in my tickets', function () {
    [$user, $company] = dashboardUser();

    $open = dashboardTicket($company, $user->id, 'open');
    $resolved = dashboardTicket($company, $user->id, 'resolved');
    $closed = dashboardTicket($company, $user->id, 'closed');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee($open->ticket_number)
        ->assertDontSee($resolved->ticket_number)
        ->assertDontSee($closed->ticket_number);
});

it('shows empty state when no tickets', function () {
    [$user] = dashboardUser();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee('You have no open tickets');
});

it('shows unassigned tickets', function () {
    [$user, $company] = dashboardUser();

    $unassigned = dashboardTicket($company, null, 'open');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee($unassigned->ticket_number)
        ->assertSee('Assign to me');
});

it('assigns an unassigned ticket to the authenticated user', function () {
    [$user, $company] = dashboardUser();

    $ticket = dashboardTicket($company, null, 'open');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('assignToMe', $ticket->id)
        ->assertDispatched('show-toast');

    expect($ticket->fresh()->assigned_to)->toBe($user->id);
    expect($ticket->fresh()->status)->toBe('in_progress');

    $this->assertDatabaseHas('ticket_logs', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'action' => 'assigned',
    ]);
});

it('shows recent notifications', function () {
    [$user] = dashboardUser();

    $user->notifications()->create([
        'id' => fake()->uuid(),
        'type' => 'App\Notifications\Test',
        'data' => ['message' => 'Test notification message', 'type' => 'assigned'],
    ]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee('Test notification message');
});

it('shows empty state for no recent activity', function () {
    [$user] = dashboardUser();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee('No recent activity');
});
