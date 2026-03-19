<?php

use App\Models\Company;
use App\Models\TicketCategory;
use App\Models\User;

beforeEach(function () {
    $this->company = Company::factory()->create();
});

test('category can have a parent category', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software',
        'parent_id' => null,
    ]);

    $child = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software Bugs',
        'parent_id' => $parent->id,
    ]);

    expect($child->parent->id)->toBe($parent->id);
    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->id)->toBe($child->id);
});

test('parent category can have multiple children', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Hardware',
        'parent_id' => null,
    ]);

    $childA = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Printers',
        'parent_id' => $parent->id,
    ]);

    $childB = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Monitors',
        'parent_id' => $parent->id,
    ]);

    expect($parent->children)->toHaveCount(2);
    expect($parent->children->pluck('id')->all())->toContain($childA->id, $childB->id);
});

test('scopeParents returns only top-level categories', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);

    TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network Config',
        'parent_id' => $parent->id,
    ]);

    $parents = TicketCategory::query()
        ->where('company_id', $this->company->id)
        ->parents()
        ->get();

    expect($parents)->toHaveCount(1);
    expect($parents->first()->id)->toBe($parent->id);
});

test('ancestor_ids returns parent and self for subcategory', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Database',
        'parent_id' => null,
    ]);

    $child = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Database Backups',
        'parent_id' => $parent->id,
    ]);

    expect($child->ancestor_ids)->toBe([$parent->id, $child->id]);
});

test('ancestor_ids returns only self for parent category', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Security',
        'parent_id' => null,
    ]);

    expect($parent->ancestor_ids)->toBe([$parent->id]);
});

test('subcategory belongs to same company as parent', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General',
        'parent_id' => null,
    ]);

    $child = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General Inquiries',
        'parent_id' => $parent->id,
    ]);

    expect($child->company_id)->toBe($parent->company_id);
    expect($child->company_id)->toBe($this->company->id);
});

test('deleting parent does not cascade to children by default', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);

    $child = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network Config',
        'parent_id' => $parent->id,
    ]);

    $parent->delete();

    // Child still exists but with orphaned parent_id
    $this->assertDatabaseHas('ticket_categories', ['id' => $child->id]);
});

test('users can be associated with subcategories', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software',
        'parent_id' => null,
    ]);

    $child = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Software Bugs',
        'parent_id' => $parent->id,
    ]);

    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $child->users()->attach($operator->id);

    expect($child->users)->toHaveCount(1);
    expect($child->users->first()->id)->toBe($operator->id);
});

test('user can specialize in a subcategory via categories pivot', function () {
    $parent = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Hardware',
        'parent_id' => null,
    ]);

    $child = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Printers',
        'parent_id' => $parent->id,
    ]);

    $operator = User::factory()->operator()->create(['company_id' => $this->company->id]);
    $operator->categories()->attach($child->id);

    expect($operator->categories)->toHaveCount(1);
    expect($operator->categories->first()->id)->toBe($child->id);
    expect($operator->categories->first()->parent_id)->toBe($parent->id);
});

test('category company scope isolates categories per company', function () {
    $otherCompany = Company::factory()->create();

    TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Network',
        'parent_id' => null,
    ]);

    TicketCategory::factory()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Software',
        'parent_id' => null,
    ]);

    $admin = User::factory()->admin()->create(['company_id' => $this->company->id]);
    $this->actingAs($admin);

    $categories = TicketCategory::all();
    expect($categories)->toHaveCount(1);
    expect($categories->first()->name)->toBe('Network');
});

test('subcategory with parent_id null is a top-level category', function () {
    $category = TicketCategory::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'General',
        'parent_id' => null,
    ]);

    expect($category->parent)->toBeNull();
    expect($category->parent_id)->toBeNull();
    expect($category->ancestor_ids)->toBe([$category->id]);
});
