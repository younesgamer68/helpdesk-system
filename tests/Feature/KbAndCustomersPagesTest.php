<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\KbArticle;
use App\Models\KbArticleVersion;
use App\Models\KbCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders the customers page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/customers')
        ->assertSuccessful()
        ->assertSee('Customers');
});

it('renders the customer details page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);
    $customer = Customer::query()->create([
        'company_id' => $company->id,
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'phone' => '123456789',
        'is_active' => true,
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain')."/customers/{$customer->id}")
        ->assertSuccessful();
});

it('renders the kb articles page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/articles')
        ->assertSuccessful()
        ->assertSee('Knowledge Base');
});

it('renders the kb article edit page for admins when accessed by id', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $category = KbCategory::create([
        'company_id' => $company->id,
        'name' => 'Product Docs',
        'description' => 'Category for product docs',
        'parent_id' => null,
        'icon' => null,
        'order' => 0,
    ]);

    $article = KbArticle::create([
        'company_id' => $company->id,
        'kb_category_id' => $category->id,
        'title' => 'How to reset password',
        'slug' => 'how-to-reset-password',
        'body' => '<p>Step-by-step guide</p>',
        'status' => 'draft',
    ]);

    KbArticleVersion::create([
        'kb_article_id' => $article->id,
        'title' => $article->title,
        'body' => $article->body,
        'created_by' => $user->id,
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain')."/kb/articles/{$article->id}/edit")
        ->assertSuccessful()
        ->assertSee('Edit Article')
        ->assertSee($user->name);
});

it('does not lazy load article version creator during livewire updates', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $category = KbCategory::create([
        'company_id' => $company->id,
        'name' => 'Product Docs',
        'description' => 'Category for product docs',
        'parent_id' => null,
        'icon' => null,
        'order' => 0,
    ]);

    $article = KbArticle::create([
        'company_id' => $company->id,
        'kb_category_id' => $category->id,
        'title' => 'How to reset password',
        'slug' => 'how-to-reset-password',
        'body' => '<p>Step-by-step guide</p>',
        'status' => 'draft',
    ]);

    KbArticleVersion::create([
        'kb_article_id' => $article->id,
        'title' => $article->title,
        'body' => $article->body,
        'created_by' => $user->id,
    ]);

    actingAs($user);

    Livewire::test(\App\Livewire\Tickets\Kb\ArticleEditor::class, ['article' => $article])
        ->set('title', 'Updated title')
        ->assertSee($user->name);
});

it('renders the kb categories page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $parent = KbCategory::create([
        'company_id' => $company->id,
        'name' => 'Parent Category',
        'description' => 'Parent category description',
        'parent_id' => null,
        'icon' => null,
        'order' => 0,
    ]);

    KbCategory::create([
        'company_id' => $company->id,
        'name' => 'Child Category',
        'description' => 'Child category description',
        'parent_id' => $parent->id,
        'icon' => null,
        'order' => 0,
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/categories')
        ->assertSuccessful()
        ->assertSee('Knowledge Base');
});

it('renders the automation page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/automation')
        ->assertSuccessful()
        ->assertSee('Automation');
});

it('renders the sla policy page for admins', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/automation/sla-policy')
        ->assertSuccessful()
        ->assertSee('Automation')
        ->assertSee('SLA Policy');
});
