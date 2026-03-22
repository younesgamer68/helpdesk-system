<?php

use App\Livewire\App\MePage;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function mePageSetup(): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    return [$operator, $company];
}

// ─── Rendering ───

test('me page renders for operator', function () {
    [$operator, $company] = mePageSetup();

    $this->actingAs($operator)
        ->get("http://{$company->slug}.".config('app.domain').'/me')
        ->assertOk();
});

test('me page shows performance section', function () {
    [$operator, $company] = mePageSetup();

    Livewire::actingAs($operator)
        ->test(MePage::class)
        ->assertSee('My Performance')
        ->assertSee($operator->name);
});

// ─── Date Presets ───

test('date preset defaults to this_week', function () {
    [$operator] = mePageSetup();

    Livewire::actingAs($operator)
        ->test(MePage::class)
        ->assertSet('datePreset', 'this_week');
});

test('changing date preset updates date range', function () {
    [$operator] = mePageSetup();

    $component = Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('applyPreset', 'today');

    expect($component->get('datePreset'))->toBe('today');
    expect($component->get('startDate'))->toBe(now()->startOfDay()->format('Y-m-d'));
    expect($component->get('endDate'))->toBe(now()->endOfDay()->format('Y-m-d'));
});

// ─── KPI: Ticket Counts Scoped to Agent ───

test('ticket counts are scoped to the authenticated agent', function () {
    [$operator, $company] = mePageSetup();
    $otherOperator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    Ticket::withoutEvents(function () use ($company, $operator) {
        Ticket::factory()->count(3)->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
        ]);
        Ticket::factory()->count(2)->resolved()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
            'resolved_at' => now(),
        ]);
    });

    // Tickets assigned to another operator should not be counted
    Ticket::withoutEvents(function () use ($company, $otherOperator) {
        Ticket::factory()->count(4)->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $otherOperator->id,
            'created_at' => now(),
        ]);
    });

    $component = Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('applyPreset', 'this_month');

    expect($component->get('totalAssigned'))->toBe(5);
    expect($component->get('resolvedCount'))->toBe(2);
    expect($component->get('openCount'))->toBe(3);
});

// ─── KPI: Resolution Rate ───

test('resolution rate is calculated correctly', function () {
    [$operator, $company] = mePageSetup();

    Ticket::withoutEvents(function () use ($company, $operator) {
        Ticket::factory()->count(3)->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
        ]);
        Ticket::factory()->count(2)->resolved()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
            'resolved_at' => now(),
        ]);
    });

    $component = Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('applyPreset', 'this_month');

    expect($component->get('resolutionRate'))->toBe(40.0);
});

test('resolution rate is null when no tickets', function () {
    [$operator] = mePageSetup();

    $component = Livewire::actingAs($operator)
        ->test(MePage::class);

    expect($component->get('resolutionRate'))->toBeNull();
});

// ─── Chart Data ───

test('status breakdown returns correct structure', function () {
    [$operator, $company] = mePageSetup();

    Ticket::withoutEvents(function () use ($company, $operator) {
        Ticket::factory()->count(2)->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
        ]);
        Ticket::factory()->resolved()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
            'resolved_at' => now(),
        ]);
    });

    $component = Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('applyPreset', 'this_month');

    $breakdown = $component->get('statusBreakdown');

    expect($breakdown)->toHaveKeys(['labels', 'keys', 'values', 'colors']);
    expect($breakdown['labels'])->toBe(['Open', 'In Progress', 'Pending', 'Resolved', 'Closed']);
    // Open = 2, Resolved = 1
    expect($breakdown['values'][0])->toBe(2);
    expect($breakdown['values'][3])->toBe(1);
});

test('priority breakdown returns correct structure', function () {
    [$operator, $company] = mePageSetup();

    Ticket::withoutEvents(function () use ($company, $operator) {
        Ticket::factory()->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'priority' => 'urgent',
            'created_at' => now(),
        ]);
        Ticket::factory()->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'priority' => 'low',
            'created_at' => now(),
        ]);
    });

    $component = Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('applyPreset', 'this_month');

    $breakdown = $component->get('priorityBreakdown');

    expect($breakdown)->toHaveKeys(['labels', 'keys', 'values', 'colors']);
    expect($breakdown['labels'])->toBe(['Urgent', 'High', 'Medium', 'Low']);
    expect($breakdown['values'][0])->toBe(1); // urgent
    expect($breakdown['values'][3])->toBe(1); // low
});

test('ticket volume chart returns labels and data arrays', function () {
    [$operator, $company] = mePageSetup();

    Ticket::withoutEvents(function () use ($company, $operator) {
        Ticket::factory()->open()->create([
            'company_id' => $company->id,
            'assigned_to' => $operator->id,
            'created_at' => now(),
        ]);
    });

    $component = Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('applyPreset', 'this_week');

    $chart = $component->get('ticketVolumeChart');

    expect($chart)->toHaveKeys(['labels', 'created', 'resolved']);
    expect($chart['labels'])->toBeArray()->not->toBeEmpty();
    expect($chart['created'])->toBeArray();
    expect($chart['resolved'])->toBeArray();
});

// ─── Profile Actions ───

test('operator can update name from me page', function () {
    [$operator] = mePageSetup();

    Livewire::actingAs($operator)
        ->test(MePage::class)
        ->set('editingName', true)
        ->set('name', 'Updated Name')
        ->call('saveName')
        ->assertDispatched('show-toast');

    expect($operator->fresh()->name)->toBe('Updated Name');
});

test('operator can toggle availability from me page', function () {
    [$operator] = mePageSetup();
    $operator->update(['is_available' => true]);

    Livewire::actingAs($operator)
        ->test(MePage::class)
        ->call('toggleAvailability');

    expect($operator->fresh()->is_available)->toBeFalse();
});

test('operator can save specialties from me page', function () {
    [$operator, $company] = mePageSetup();
    $cat1 = TicketCategory::factory()->create(['company_id' => $company->id]);
    $cat2 = TicketCategory::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($operator)
        ->test(MePage::class)
        ->set('selectedCategories', [(string) $cat1->id, (string) $cat2->id])
        ->call('saveSpecialties')
        ->assertDispatched('show-toast');

    expect($operator->fresh()->categories)->toHaveCount(2);
});
