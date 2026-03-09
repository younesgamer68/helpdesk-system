<?php

use App\Livewire\Dashboard\AutomationRulesTable;
use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\TicketCategory;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('automation rules table renders successfully', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->assertStatus(200);
});

test('automation rules table displays rules', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    AutomationRule::factory()->create([
        'company_id' => $company->id,
        'name' => 'Auto Assign Technical',
    ]);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->assertSee('Auto Assign Technical');
});

test('automation rules table filters by name', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    AutomationRule::factory()->create(['company_id' => $company->id, 'name' => 'Priority Filter Test']);
    AutomationRule::factory()->create(['company_id' => $company->id, 'name' => 'Unique Reply Name']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('search', 'Priority Filter')
        ->assertSee('Priority Filter Test')
        ->assertDontSee('Unique Reply Name');
});

test('automation rules table filters by type', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    AutomationRule::factory()->create(['company_id' => $company->id, 'name' => 'XYZ Assignment Rule', 'type' => 'assignment']);
    AutomationRule::factory()->create(['company_id' => $company->id, 'name' => 'XYZ Escalation Rule', 'type' => 'escalation']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('filterType', 'assignment')
        ->assertSee('XYZ Assignment Rule')
        ->assertDontSee('XYZ Escalation Rule');
});

test('admin can create an assignment rule', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', 'Auto Assign Network Issues')
        ->set('description', 'Assign network tickets to specialists')
        ->set('type', 'assignment')
        ->set('is_active', true)
        ->set('priority', 10)
        ->set('category_id', $category->id)
        ->set('assign_to_specialist', true)
        ->set('fallback_to_generalist', true)
        ->call('createRule')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('automation_rules', [
        'company_id' => $company->id,
        'name' => 'Auto Assign Network Issues',
        'type' => 'assignment',
    ]);
});

test('admin can create a priority rule with keywords', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', 'Urgent Keywords')
        ->set('type', 'priority')
        ->set('newKeyword', 'urgent')
        ->call('addKeyword')
        ->set('newKeyword', 'critical')
        ->call('addKeyword')
        ->set('set_priority', 'urgent')
        ->call('createRule')
        ->assertDispatched('show-toast');

    $rule = AutomationRule::where('name', 'Urgent Keywords')->first();
    expect($rule->conditions['keywords'])->toContain('urgent', 'critical');
    expect($rule->actions['set_priority'])->toBe('urgent');
});

test('admin can create an auto reply rule', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', 'Welcome Auto Reply')
        ->set('type', 'auto_reply')
        ->set('on_create', true)
        ->set('send_email', true)
        ->set('email_message', 'Thank you for contacting us!')
        ->call('createRule')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('automation_rules', [
        'company_id' => $company->id,
        'name' => 'Welcome Auto Reply',
        'type' => 'auto_reply',
    ]);
});

test('admin can create an escalation rule', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->set('name', '24h Escalation')
        ->set('type', 'escalation')
        ->set('idle_hours', 24)
        ->set('conditionStatuses', ['pending', 'open'])
        ->set('escalate_priority', true)
        ->set('notify_admin', true)
        ->call('createRule')
        ->assertDispatched('show-toast');

    $rule = AutomationRule::where('name', '24h Escalation')->first();
    expect($rule->conditions['idle_hours'])->toBe(24);
    expect($rule->actions['notify_admin'])->toBeTrue();
});

test('admin can toggle rule status', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $rule = AutomationRule::factory()->create([
        'company_id' => $company->id,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->call('toggleRuleStatus', $rule->id);

    $rule->refresh();
    expect($rule->is_active)->toBeFalse();
});

test('admin can delete a rule', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $rule = AutomationRule::factory()->create(['company_id' => $company->id]);

    $this->actingAs($admin);

    Livewire::test(AutomationRulesTable::class)
        ->call('confirmDelete', $rule->id)
        ->call('deleteRule');

    $this->assertDatabaseMissing('automation_rules', ['id' => $rule->id]);
});
