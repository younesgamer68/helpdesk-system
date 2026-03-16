<?php

use App\Livewire\Categories\CategoriesTable;
use App\Models\Company;
use App\Models\TicketCategory;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('categories table renders successfully', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->assertStatus(200);
});

test('categories table displays categories', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Network Issues']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->assertSee('Network Issues');
});

test('categories table filters by name', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Networking']);
    TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Printers']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->set('search', 'Networking')
        ->assertSee('Networking')
        ->assertDontSee('Printers');
});

test('admin can create a new category', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->set('name', 'Hardware')
        ->set('description', 'Hardware related issues')
        ->set('default_priority', 'high')
        ->call('createCategory')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('ticket_categories', [
        'company_id' => $company->id,
        'name' => 'Hardware',
        'description' => 'Hardware related issues',
        'default_priority' => 'high',
    ]);
});

test('admin can edit a category', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Old Name']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->call('editCategory', $category->id)
        ->set('name', 'New Name')
        ->set('description', 'Updated description')
        ->call('updateCategory')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('ticket_categories', [
        'id' => $category->id,
        'name' => 'New Name',
        'description' => 'Updated description',
    ]);
});

test('admin can delete a category', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $category = TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'To Delete']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->call('confirmDelete', $category->id)
        ->call('deleteCategory')
        ->assertDispatched('show-toast');

    $this->assertDatabaseMissing('ticket_categories', ['id' => $category->id]);
});

test('category name must be unique per company', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    TicketCategory::factory()->create(['company_id' => $company->id, 'name' => 'Existing']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->set('name', 'Existing')
        ->set('default_priority', 'medium')
        ->call('createCategory')
        ->assertHasErrors(['name']);
});

test('non-admins cannot access categories route', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $operator = User::factory()->create(['company_id' => $company->id, 'role' => 'operator']);

    $this->actingAs($operator);

    $url = "http://{$company->slug}.".config('app.domain').'/categories';

    $this->get($url)->assertStatus(403);
});

test('categories only show for current company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company1->id, 'role' => 'admin']);
    TicketCategory::factory()->create(['company_id' => $company1->id, 'name' => 'My Category']);
    TicketCategory::factory()->create(['company_id' => $company2->id, 'name' => 'Other Category']);

    $this->actingAs($admin);

    Livewire::test(CategoriesTable::class)
        ->assertSee('My Category')
        ->assertDontSee('Other Category');
});
