<?php

use App\Livewire\App\AgentDashboard;
use App\Livewire\Dashboard\TicketsTable;
use App\Livewire\Notifications\NotificationsPage;
use App\Livewire\Settings\MyTeam;
use App\Livewire\Settings\Profile;
use App\Livewire\Tickets\TicketDetails;
use App\Models\Company;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMention;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TeamAssigned;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function operatorSetup(): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    return [$operator, $admin, $company];
}

// ─── Phase 1: Ticket Detail Page ───

test('operator cannot assign tickets', function () {
    [$operator, $admin, $company] = operatorSetup();
    $ticket = Ticket::withoutEvents(function () use ($company) {
        return Ticket::factory()->create(['company_id' => $company->id, 'verified' => true, 'status' => 'open']);
    });

    Livewire::actingAs($operator)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id)
        ->assertDispatched('show-toast');

    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('operator cannot close tickets', function () {
    [$operator, $admin, $company] = operatorSetup();
    $ticket = Ticket::withoutEvents(function () use ($company, $operator) {
        return Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'status' => 'open',
            'assigned_to' => $operator->id,
        ]);
    });

    Livewire::actingAs($operator)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->call('closeTicket')
        ->assertDispatched('show-toast', type: 'error');

    expect($ticket->fresh()->status)->not->toBe('closed');
});

test('admin can assign tickets', function () {
    [$operator, $admin, $company] = operatorSetup();
    $ticket = Ticket::withoutEvents(function () use ($company) {
        return Ticket::factory()->create(['company_id' => $company->id, 'verified' => true, 'status' => 'open']);
    });

    Livewire::actingAs($admin)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->call('assign', $operator->id);

    expect($ticket->fresh()->assigned_to)->toBe($operator->id);
});

// ─── Phase 2: Tickets Table ───

test('operator sees ticket view tabs', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(TicketsTable::class)
        ->assertSee('My Tickets')
        ->assertSee('All Tickets');
});

test('operator my tickets view shows only assigned tickets', function () {
    [$operator, $admin, $company] = operatorSetup();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator->categories()->attach($category->id);

    $assignedTicket = Ticket::withoutEvents(function () use ($company, $operator) {
        return Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'assigned_to' => $operator->id,
            'status' => 'open',
        ]);
    });
    $unassignedTicket = Ticket::withoutEvents(function () use ($company, $category) {
        return Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'category_id' => $category->id,
            'assigned_to' => null,
            'status' => 'open',
        ]);
    });

    Livewire::actingAs($operator)
        ->test(TicketsTable::class)
        ->assertSet('ticketView', 'mine')
        ->assertSee($assignedTicket->ticket_number)
        ->assertDontSee($unassignedTicket->ticket_number);
});

test('operator all tickets view shows assigned and specialty unassigned', function () {
    [$operator, $admin, $company] = operatorSetup();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator->categories()->attach($category->id);

    $assignedTicket = Ticket::withoutEvents(function () use ($company, $operator) {
        return Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'assigned_to' => $operator->id,
            'status' => 'open',
        ]);
    });
    $specialtyUnassigned = Ticket::withoutEvents(function () use ($company, $category) {
        return Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'category_id' => $category->id,
            'assigned_to' => null,
            'status' => 'open',
        ]);
    });
    $otherTicket = Ticket::withoutEvents(function () use ($company, $admin) {
        return Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'assigned_to' => $admin->id,
            'status' => 'open',
        ]);
    });

    Livewire::actingAs($operator)
        ->test(TicketsTable::class)
        ->call('setTicketView', 'all')
        ->assertSee($assignedTicket->ticket_number)
        ->assertSee($specialtyUnassigned->ticket_number)
        ->assertDontSee($otherTicket->ticket_number);
});

test('admin does not see ticket view tabs', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($admin)
        ->test(TicketsTable::class)
        ->assertDontSee('My Tickets')
        ->assertDontSee('All Tickets');
});

// ─── Phase 3: KB Read-Only Access ───

test('operator cannot access article editor', function () {
    [$operator, $admin, $company] = operatorSetup();

    $this->actingAs($operator)
        ->get(route('kb.articles.create', $company->slug))
        ->assertRedirect();
});

test('operator can access articles list', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(\App\Livewire\Tickets\Kb\ArticlesList::class)
        ->assertStatus(200)
        ->assertDontSee('New Article');
});

test('admin can see new article button', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Tickets\Kb\ArticlesList::class)
        ->assertStatus(200)
        ->assertSee('New Article');
});

// ─── Phase 4: Settings Navigation ───

test('operator sees profile but not company settings', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(Profile::class)
        ->assertStatus(200)
        ->assertSee('Profile');
});

// ─── Phase 5: Operator Specialities & Availability ───

test('operator can toggle availability from profile', function () {
    [$operator, $admin, $company] = operatorSetup();
    $operator->update(['is_available' => true]);

    Livewire::actingAs($operator)
        ->test(Profile::class)
        ->assertSee('Availability')
        ->call('toggleAvailability');

    expect($operator->fresh()->is_available)->toBeFalse();
});

test('operator can update specialties from profile', function () {
    [$operator, $admin, $company] = operatorSetup();
    $category1 = TicketCategory::factory()->create(['company_id' => $company->id]);
    $category2 = TicketCategory::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($operator)
        ->test(Profile::class)
        ->assertSee('Specialities')
        ->set('selectedCategories', [(string) $category1->id, (string) $category2->id])
        ->call('updateSpecialties')
        ->assertDispatched('show-toast', message: 'Specialties updated successfully.', type: 'success');

    expect($operator->fresh()->specialty_id)->toBe($category1->id);
    expect($operator->fresh()->categories->pluck('id')->sort()->values()->toArray())
        ->toBe([$category1->id, $category2->id]);
});

test('admin does not see availability or specialities on profile', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($admin)
        ->test(Profile::class)
        ->assertDontSee('Availability')
        ->assertDontSee('Specialities');
});

// ─── Phase 6: KB Tabs Hidden for Operators ───

test('operator cannot access kb categories route', function () {
    [$operator, $admin, $company] = operatorSetup();

    $this->actingAs($operator)
        ->get(route('kb.categories', $company->slug))
        ->assertRedirect();
});

test('operator cannot access kb api route', function () {
    [$operator, $admin, $company] = operatorSetup();

    $this->actingAs($operator)
        ->get(route('kb.api', $company->slug))
        ->assertRedirect();
});

// ─── Phase 7: Team Assignment Notification ───

test('operator receives notification when added to team', function () {
    Notification::fake();

    [$operator, $admin, $company] = operatorSetup();
    $team = Team::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Operators\TeamsTable::class)
        ->set('managingTeamId', $team->id)
        ->set('addMemberId', $operator->id)
        ->set('addMemberRole', 'member')
        ->call('addMember');

    Notification::assertSentTo($operator, TeamAssigned::class, function ($notification) use ($team) {
        return $notification->team->id === $team->id && $notification->role === 'member';
    });
});

// ─── Phase 8: My Team Page ───

test('operator can view their teams', function () {
    [$operator, $admin, $company] = operatorSetup();
    $team = Team::factory()->create(['company_id' => $company->id, 'name' => 'Support Alpha']);
    $team->members()->attach($operator->id, ['role' => 'member']);

    Livewire::actingAs($operator)
        ->test(MyTeam::class)
        ->assertStatus(200)
        ->assertSee('Support Alpha')
        ->assertSee($operator->name);
});

test('operator sees empty state when not in any team', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(MyTeam::class)
        ->assertStatus(200)
        ->assertSee("You haven't been assigned to any teams yet.");
});

test('operator can access my team settings route', function () {
    [$operator, $admin, $company] = operatorSetup();

    $this->actingAs($operator)
        ->get(route('settings.my-team', $company->slug))
        ->assertOk();
});

test('my teams page highlights my teams without activating settings', function () {
    [$operator, $admin, $company] = operatorSetup();

    $response = $this->actingAs($operator)
        ->get(route('settings.my-team', $company->slug))
        ->assertOk();

    $content = $response->getContent();

    expect($content)->toContain(route('settings.my-team', $company->slug));
    expect($content)->toContain('My Teams');
    expect($content)->toContain('bg-zinc-800 text-white');
    expect($content)->toContain(route('settings.security', $company->slug));
    expect($content)->toContain('text-zinc-400 hover:bg-zinc-900 hover:text-white');
});

test('operator dashboard route renders even when a mentioned ticket is soft deleted', function () {
    [$operator, $admin, $company] = operatorSetup();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'category_id' => $category->id,
        'verified' => true,
        'assigned_to' => $operator->id,
        'status' => 'open',
    ]);

    $reply = TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'customer_name' => '',
        'message' => 'Ping @'.$operator->name,
        'is_internal' => false,
    ]);

    TicketMention::create([
        'company_id' => $company->id,
        'ticket_id' => $ticket->id,
        'ticket_reply_id' => $reply->id,
        'mentioned_user_id' => $operator->id,
        'mentioned_by_user_id' => $admin->id,
    ]);

    $ticket->delete();

    $this->actingAs($operator)
        ->get(route('agent.dashboard', $company->slug))
        ->assertOk();
});

// ─── Phase 9: SLA Breached KPI on Dashboard ───

test('operator dashboard shows sla breached count', function () {
    [$operator, $admin, $company] = operatorSetup();

    Ticket::withoutEvents(function () use ($company, $operator) {
        Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'assigned_to' => $operator->id,
            'status' => 'open',
            'sla_status' => 'breached',
        ]);
        Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'assigned_to' => $operator->id,
            'status' => 'in_progress',
            'sla_status' => 'breached',
        ]);
        // Resolved ticket should not count
        Ticket::factory()->create([
            'company_id' => $company->id,
            'verified' => true,
            'assigned_to' => $operator->id,
            'status' => 'resolved',
            'sla_status' => 'breached',
        ]);
    });

    Livewire::actingAs($operator)
        ->test(AgentDashboard::class)
        ->assertSee('SLA')
        ->assertSee('Breached')
        ->assertSet('slaBreachedCount', 2);
});

// ─── Phase 10: Teams Tab in Notifications ───

test('operator sees teams tab on notifications page', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(NotificationsPage::class)
        ->assertSee('Teams');
});

test('admin does not see teams tab on notifications page', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($admin)
        ->test(NotificationsPage::class)
        ->assertDontSee('Teams');
});

test('operator can switch to teams tab', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(NotificationsPage::class)
        ->call('setTab', 'teams')
        ->assertSet('activeTab', 'teams');
});

test('admin cannot switch to teams tab', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($admin)
        ->test(NotificationsPage::class)
        ->call('setTab', 'teams')
        ->assertSet('activeTab', 'all');
});

test('operator sees sla tab on notifications page', function () {
    [$operator, $admin, $company] = operatorSetup();

    Livewire::actingAs($operator)
        ->test(NotificationsPage::class)
        ->assertSee('SLA')
        ->call('setTab', 'sla')
        ->assertSet('activeTab', 'sla');
});
