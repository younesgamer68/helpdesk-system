<?php

use App\Livewire\App\AdminDashboard;
use App\Livewire\App\AgentDashboard;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketLog;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function tenantCompany(string $slug): Company
{
    return Company::factory()->create([
        'slug' => $slug,
        'onboarding_completed_at' => now(),
    ]);
}

function tenantAdmin(Company $company): User
{
    return User::factory()->admin()->create([
        'company_id' => $company->id,
    ]);
}

function tenantAgent(Company $company): User
{
    return User::factory()->create([
        'company_id' => $company->id,
        'role' => 'agent',
    ]);
}

function tenantCategory(Company $company, string $name): TicketCategory
{
    return TicketCategory::factory()->create([
        'company_id' => $company->id,
        'name' => $name,
    ]);
}

function tenantTicket(Company $company, array $attributes = []): Ticket
{
    return Ticket::factory()->create(array_merge([
        'company_id' => $company->id,
        'status' => 'open',
        'priority' => 'medium',
    ], $attributes));
}

it('scopes company-owned models to the authenticated users company', function () {
    $companyA = tenantCompany('scope-a');
    $companyB = tenantCompany('scope-b');
    $adminA = tenantAdmin($companyA);

    tenantAgent($companyA);
    tenantAgent($companyB);

    $customerA = Customer::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Alpha Customer',
        'email' => 'alpha@example.test',
        'phone' => '111111',
        'is_active' => true,
    ]);

    $customerB = Customer::query()->create([
        'company_id' => $companyB->id,
        'name' => 'Beta Customer',
        'email' => 'beta@example.test',
        'phone' => '222222',
        'is_active' => true,
    ]);

    tenantCategory($companyA, 'Alpha Category');
    tenantCategory($companyB, 'Beta Category');

    tenantTicket($companyA, ['subject' => 'Alpha ticket', 'customer_id' => $customerA->id]);
    tenantTicket($companyB, ['subject' => 'Beta ticket', 'customer_id' => $customerB->id]);

    actingAs($adminA);

    expect(Ticket::query()->pluck('subject')->all())->toBe(['Alpha ticket']);
    expect(Customer::query()->pluck('name')->all())->toBe(['Alpha Customer']);
    expect(TicketCategory::query()->pluck('name')->all())->toBe(['Alpha Category']);
    expect(User::query()->pluck('company_id')->unique()->all())->toBe([$companyA->id]);
});

it('allows explicit bypass of the company scope', function () {
    $companyA = tenantCompany('bypass-a');
    $companyB = tenantCompany('bypass-b');
    $adminA = tenantAdmin($companyA);

    actingAs($adminA);

    tenantTicket($companyA, ['subject' => 'Scoped ticket A']);
    tenantTicket($companyB, ['subject' => 'Scoped ticket B']);

    expect(Ticket::query()->count())->toBe(1);
    expect(Ticket::withoutGlobalScope(CompanyScope::class)->count())->toBe(2);
});

it('keeps the admin dashboard isolated to the authenticated company', function () {
    $companyA = tenantCompany('admin-a');
    $companyB = tenantCompany('admin-b');
    $adminA = tenantAdmin($companyA);
    $adminB = tenantAdmin($companyB);

    $ticketA = tenantTicket($companyA, [
        'subject' => 'Alpha admin ticket',
        'ticket_number' => 'TKT-ALPHA1',
    ]);
    $ticketB = tenantTicket($companyB, [
        'subject' => 'Beta admin ticket',
        'ticket_number' => 'TKT-BETA1',
    ]);

    TicketLog::create([
        'ticket_id' => $ticketA->id,
        'user_id' => $adminA->id,
        'company_id' => $companyA->id,
        'action' => 'created',
        'description' => 'Alpha activity log',
    ]);

    TicketLog::create([
        'ticket_id' => $ticketB->id,
        'user_id' => $adminB->id,
        'company_id' => $companyB->id,
        'action' => 'created',
        'description' => 'Beta activity log',
    ]);

    actingAs($adminA)
        ->get("http://{$companyA->slug}.".config('app.domain').'/admin/dashboard')
        ->assertSuccessful()
        ->assertSee('TKT-ALPHA1')
        ->assertDontSee('TKT-BETA1')
        ->assertSee('alpha activity log')
        ->assertDontSee('beta activity log');

    $component = Livewire::actingAs($adminA)->test(AdminDashboard::class);

    expect($component->instance()->openTicketsCount)->toBe(1);
    expect($component->instance()->recentTickets->pluck('ticket_number')->all())->toBe(['TKT-ALPHA1']);
});

it('keeps the agent dashboard unassigned tickets isolated to the authenticated company', function () {
    $companyA = tenantCompany('agent-a');
    $companyB = tenantCompany('agent-b');
    $agentA = tenantAgent($companyA);

    tenantTicket($companyA, [
        'subject' => 'Agent company ticket',
        'ticket_number' => 'TKT-AGENTA',
        'assigned_to' => null,
    ]);
    tenantTicket($companyB, [
        'subject' => 'Other company ticket',
        'ticket_number' => 'TKT-AGENTB',
        'assigned_to' => null,
    ]);

    actingAs($agentA)
        ->get("http://{$companyA->slug}.".config('app.domain').'/home')
        ->assertSuccessful()
        ->assertSee('TKT-AGENTA')
        ->assertDontSee('TKT-AGENTB');

    $component = Livewire::actingAs($agentA)->test(AgentDashboard::class);

    expect($component->instance()->unassignedTickets->pluck('ticket_number')->all())->toBe(['TKT-AGENTA']);
});
