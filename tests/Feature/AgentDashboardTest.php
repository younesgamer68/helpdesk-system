<?php

use App\Livewire\Dashboard\AgentDashboard;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMention;
use App\Models\TicketReply;
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

it('shows greeting and subtitle', function () {
    [$user, $company] = dashboardUser();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee($user->name)
        ->assertSee('You are all caught up');
});

it('shows urgent pill with count', function () {
    [$user, $company] = dashboardUser();

    dashboardTicket($company, $user->id, 'open', 'urgent');
    dashboardTicket($company, $user->id, 'open', 'medium');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee('Urgent')
        ->assertSet('activePill', 'urgent');
});

it('defaults to needs-reply pill when no urgent tickets', function () {
    [$user, $company] = dashboardUser();

    dashboardTicket($company, $user->id, 'pending', 'medium');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSet('activePill', 'needs-reply');
});

it('defaults to all pill when no urgent or pending tickets', function () {
    [$user, $company] = dashboardUser();

    dashboardTicket($company, $user->id, 'open', 'low');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSet('activePill', 'all');
});

it('switches pills correctly', function () {
    [$user, $company] = dashboardUser();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('setPill', 'urgent')
        ->assertSet('activePill', 'urgent')
        ->call('setPill', 'needs-reply')
        ->assertSet('activePill', 'needs-reply')
        ->call('setPill', 'all')
        ->assertSet('activePill', 'all');
});

it('shows my tickets sorted by priority', function () {
    [$user, $company] = dashboardUser();

    dashboardTicket($company, $user->id, 'open', 'low');
    $urgent = dashboardTicket($company, $user->id, 'open', 'urgent');
    dashboardTicket($company, $user->id, 'open', 'high');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSee($urgent->subject);
});

it('does not show resolved or closed tickets', function () {
    [$user, $company] = dashboardUser();

    $open = dashboardTicket($company, $user->id, 'open');
    $resolved = dashboardTicket($company, $user->id, 'resolved');
    $closed = dashboardTicket($company, $user->id, 'closed');

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('setPill', 'all')
        ->assertSee($open->subject)
        ->assertDontSee($resolved->subject)
        ->assertDontSee($closed->subject);
});

it('shows empty state when no tickets', function () {
    [$user] = dashboardUser();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('setPill', 'all')
        ->assertSee('No tickets found');
});

it('shows unassigned team tickets in pill', function () {
    [$user, $company] = dashboardUser();

    $team = Team::factory()->create(['company_id' => $company->id]);
    $team->members()->attach($user->id, ['role' => 'member']);

    dashboardTicket($company, null, 'open');
    $ticket = Ticket::latest('id')->first();
    $ticket->update(['team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('setPill', 'unassigned')
        ->assertSee('Unassigned')
        ->assertSee($ticket->subject)
        ->assertSee('Take this');
});

it('takes unassigned ticket from pill', function () {
    [$user, $company] = dashboardUser();

    $team = Team::factory()->create(['company_id' => $company->id]);
    $team->members()->attach($user->id, ['role' => 'member']);

    $ticket = dashboardTicket($company, null, 'open');
    $ticket->update(['team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('takeTicket', $ticket->id)
        ->assertDispatched('show-toast');

    expect($ticket->fresh()->assigned_to)->toBe($user->id);
});

it('toggles availability status', function () {
    [$user, $company] = dashboardUser();

    expect($user->is_available)->toBeTrue();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('toggleAvailability');

    expect($user->fresh()->is_available)->toBeFalse();

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('toggleAvailability');

    expect($user->fresh()->is_available)->toBeTrue();
});

it('includes sla breached tickets in urgent pill', function () {
    [$user, $company] = dashboardUser();

    $slaTicket = dashboardTicket($company, $user->id, 'open', 'medium');
    $slaTicket->update(['sla_status' => 'breached']);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSet('activePill', 'urgent')
        ->assertSee($slaTicket->subject);
});

it('defaults to mentions pill when only mentions exist', function () {
    [$user, $company] = dashboardUser();

    $ticket = dashboardTicket($company, null, 'open');
    $reply = TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'message' => 'test note',
        'is_internal' => true,
        'is_technician' => false,
    ]);

    $mentioner = User::factory()->create(['company_id' => $company->id, 'role' => 'agent']);
    TicketMention::create([
        'ticket_id' => $ticket->id,
        'ticket_reply_id' => $reply->id,
        'mentioned_user_id' => $user->id,
        'mentioned_by_user_id' => $mentioner->id,
        'company_id' => $company->id,
    ]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSet('activePill', 'mentions');
});

it('marks mention as read', function () {
    [$user, $company] = dashboardUser();

    $ticket = dashboardTicket($company, null, 'open');
    $reply = TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'message' => 'test note',
        'is_internal' => true,
        'is_technician' => false,
    ]);

    $mentioner = User::factory()->create(['company_id' => $company->id, 'role' => 'agent']);
    $mention = TicketMention::create([
        'ticket_id' => $ticket->id,
        'ticket_reply_id' => $reply->id,
        'mentioned_user_id' => $user->id,
        'mentioned_by_user_id' => $mentioner->id,
        'company_id' => $company->id,
    ]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->call('markMentionRead', $mention->id);

    expect($mention->fresh()->read_at)->not->toBeNull();
});

it('shows unassigned pill in smart default when unassigned tickets exist', function () {
    [$user, $company] = dashboardUser();

    $team = Team::factory()->create(['company_id' => $company->id]);
    $team->members()->attach($user->id, ['role' => 'member']);

    $ticket = dashboardTicket($company, null, 'open');
    $ticket->update(['team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(AgentDashboard::class)
        ->assertSet('activePill', 'unassigned');
});
