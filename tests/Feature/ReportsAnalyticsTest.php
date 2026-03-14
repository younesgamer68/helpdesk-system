<?php

use App\Livewire\Reports\ReportsAnalytics;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createOnboardedCompanyWithAdmin(): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    return [$company, $admin];
}

test('reports page renders for admin users', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    $this->actingAs($admin)
        ->get("http://{$company->slug}.".config('app.domain').'/reports')
        ->assertOk();
});

test('reports page shows key elements', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->assertSee('Reports')
        ->assertSee('Analytics')
        ->assertSee('Overview')
        ->assertSee('Agent Performance')
        ->assertSee('Tickets')
        ->assertSee('Categories');
});

test('non-admin cannot access reports page', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($operator)
        ->get("http://{$company->slug}.".config('app.domain').'/reports')
        ->assertForbidden();
});

test('reports page displays correct ticket counts', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Ticket::factory()->count(3)->create([
        'company_id' => $company->id,
        'status' => 'open',
        'created_at' => now(),
    ]);
    Ticket::factory()->count(2)->resolved()->create([
        'company_id' => $company->id,
        'created_at' => now(),
    ]);

    $component = Livewire::actingAs($admin)->test(ReportsAnalytics::class);

    expect($component->get('totalTickets'))->toBe(5);
    expect($component->get('resolvedCount'))->toBe(2);
    expect($component->get('openCount'))->toBe(3);
});

test('reports page switches tabs', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->assertSet('activeTab', 'overview')
        ->call('setTab', 'agents')
        ->assertSet('activeTab', 'agents')
        ->call('setTab', 'tickets')
        ->assertSet('activeTab', 'tickets')
        ->call('setTab', 'categories')
        ->assertSet('activeTab', 'categories');
});

test('date preset changes update start and end dates', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    $component = Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class);

    $component->call('applyPreset', 'today');
    expect($component->get('startDate'))->toBe(now()->startOfDay()->format('Y-m-d'));
    expect($component->get('endDate'))->toBe(now()->endOfDay()->format('Y-m-d'));

    $component->call('applyPreset', 'this_month');
    expect($component->get('startDate'))->toBe(now()->startOfMonth()->format('Y-m-d'));
});

test('chart filter navigation applies filter and switches to tickets tab', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('applyChartFilter', 'status', 'open')
        ->assertSet('activeTab', 'tickets')
        ->assertSet('filterStatus', 'open');
});

test('tickets tab filters tickets correctly', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    Ticket::factory()->count(2)->create([
        'company_id' => $company->id,
        'status' => 'open',
        'priority' => 'high',
        'category_id' => $category->id,
        'created_at' => now(),
    ]);
    Ticket::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'priority' => 'low',
        'created_at' => now(),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('setTab', 'tickets')
        ->set('filterStatus', 'open');

    expect($component->get('paginatedTickets'))->toHaveCount(2);
});

test('tickets csv export streams download', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Ticket::factory()->create([
        'company_id' => $company->id,
        'created_at' => now(),
    ]);

    $component = Livewire::actingAs($admin)->test(ReportsAnalytics::class);
    $response = $component->call('exportTicketsCsv');

    expect($response->effects['download'])->not->toBeEmpty();
});

test('agent csv export streams download', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    $component = Livewire::actingAs($admin)->test(ReportsAnalytics::class);
    $response = $component->call('exportAgentsCsv');

    expect($response->effects['download'])->not->toBeEmpty();
});

test('agent performance tab shows agent pills', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'name' => 'Test Agent',
    ]);

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('setTab', 'agents')
        ->assertSee('All Agents')
        ->assertSee('Test Agent');
});

test('selecting an agent shows their profile', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();
    $operator = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'name' => 'Profile Agent',
    ]);

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('setTab', 'agents')
        ->call('selectAgent', $operator->id)
        ->assertSee('Profile Agent');
});

test('categories tab shows category cards', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();
    TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Billing']);

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('setTab', 'categories')
        ->assertSee('Billing');
});

test('category card expands with details on toggle', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();
    $category = TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Support']);

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('setTab', 'categories')
        ->call('toggleCategory', $category->id)
        ->assertSet('expandedCategoryId', $category->id)
        ->assertSee('Top Agents')
        ->assertSee('Priority Breakdown');
});

test('resolution rate calculates correctly', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Ticket::factory()->count(3)->resolved()->create([
        'company_id' => $company->id,
        'created_at' => now(),
    ]);
    Ticket::factory()->count(7)->open()->create([
        'company_id' => $company->id,
        'created_at' => now(),
    ]);

    $component = Livewire::actingAs($admin)->test(ReportsAnalytics::class);
    expect($component->get('resolutionRate'))->toBe(30.0);
});

test('clear ticket filters resets all filters', function () {
    [$company, $admin] = createOnboardedCompanyWithAdmin();

    Livewire::actingAs($admin)
        ->test(ReportsAnalytics::class)
        ->call('setTab', 'tickets')
        ->set('filterStatus', 'open')
        ->set('filterPriority', 'high')
        ->set('ticketSearch', 'test')
        ->call('clearTicketFilters')
        ->assertSet('filterStatus', '')
        ->assertSet('filterPriority', '')
        ->assertSet('ticketSearch', '');
});
