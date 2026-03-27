<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\KbArticle;
use App\Models\KbArticleVersion;
use App\Models\TicketCategory;
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

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Product Docs',
        'description' => 'Category for product docs',
    ]);

    $article = KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
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

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Product Docs',
        'description' => 'Category for product docs',
    ]);

    $article = KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
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

it('kb article editor does not show deprecated meta and schedule fields', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Product Docs',
        'description' => 'Category for product docs',
    ]);

    $article = KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
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
        ->assertDontSee('Meta Description')
        ->assertDontSee('Schedule Publish')
        ->assertDontSee('Save Draft')
        ->assertDontSee('wire:confirm="Are you sure you want to revert to this version? Unsaved changes will be lost."', false);
});

it('kb search matches article tags and renders tag chips', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Billing',
        'description' => 'Billing category',
    ]);

    KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
        'title' => 'Invoice lifecycle',
        'slug' => 'invoice-lifecycle',
        'body' => '<p>Details about invoices</p>',
        'status' => 'published',
        'tags' => json_encode(['billing', 'invoices']),
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/search?q=billing')
        ->assertSuccessful()
        ->assertSee('Invoice lifecycle')
        ->assertSee('#billing');
});

it('renders the kb categories page showing company categories', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'General Support',
        'description' => 'General support category',
    ]);

    TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Technical',
        'description' => 'Technical support category',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/categories')
        ->assertSuccessful()
        ->assertSee('Knowledge Base')
        ->assertSee('General Support')
        ->assertSee('Technical');
});

it('renders the public kb article page with related articles from ticket category', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Guides',
        'description' => 'Public guides',
    ]);

    $article = KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
        'title' => 'Primary Article',
        'slug' => 'primary-article',
        'body' => '<p>Main content</p>',
        'status' => 'published',
    ]);

    KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
        'title' => 'Related Article',
        'slug' => 'related-article',
        'body' => '<p>Related content</p>',
        'status' => 'published',
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain')."/kb/article/{$article->slug}")
        ->assertSuccessful()
        ->assertSee('Primary Article')
        ->assertSee('Related Article');
});

it('renders public kb search results without lazy loading exceptions', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'Billing',
        'description' => 'Billing category',
    ]);

    KbArticle::create([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
        'title' => 'Invoice lifecycle',
        'slug' => 'invoice-lifecycle',
        'body' => '<p>Details about invoices</p>',
        'status' => 'published',
        'tags' => json_encode(['billing', 'invoices']),
    ]);

    actingAs($user)
        ->get("http://{$company->slug}.".config('app.domain').'/kb/search?q=billing')
        ->assertSuccessful()
        ->assertSee('Invoice lifecycle')
        ->assertSee('Billing');
});

it('renders widget demo with saved custom link attributes', function () {
    $company = Company::factory()->create([
        'slug' => 'test-a',
        'kb_widget_link_mode' => 'custom',
        'kb_widget_article_base_url' => 'https://www.youtube.com',
    ]);

    $this->get("http://{$company->slug}.".config('app.domain').'/kb/widget-demo')
        ->assertSuccessful()
        ->assertSee('data-default-link-mode="custom"', false)
        ->assertSee('data-article-base-url="https://www.youtube.com"', false);
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
