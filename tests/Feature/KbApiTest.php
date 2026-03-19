<?php

use App\Models\Company;
use App\Models\KbArticle;
use App\Models\TicketCategory;

function seedKbArticle(Company $company, array $overrides = []): KbArticle
{
    // Check if a "General" category already exists for this company
    $category = TicketCategory::firstOrCreate(
        [
            'company_id' => $company->id,
            'name' => 'General',
        ],
        [
            'description' => 'General category',
        ]
    );

    return KbArticle::create(array_merge([
        'company_id' => $company->id,
        'ticket_category_id' => $category->id,
        'title' => 'Getting Started',
        'slug' => 'getting-started',
        'body' => 'Welcome content',
        'status' => 'published',
        'meta_description' => 'Welcome article',
        'tags' => json_encode(['welcome']),
    ], $overrides));
}

test('kb api base endpoint responds on company subdomain host', function () {
    $company = Company::factory()->create([
        'name' => 'Test A',
        'slug' => 'test-a',
    ]);

    $response = $this->get('http://test-a.'.config('app.domain').'/api/kb/test-a');

    $response->assertOk()->assertJsonPath('company.slug', $company->slug);
    $response->assertJsonPath('endpoints.articles', route('api.kb.articles', ['company_slug' => 'test-a']));
});

test('kb api articles endpoint returns only published articles for requested company', function () {
    $company = Company::factory()->create(['slug' => 'test-a']);
    $otherCompany = Company::factory()->create(['slug' => 'other-company']);

    seedKbArticle($company, ['title' => 'Public A', 'slug' => 'public-a', 'status' => 'published']);
    seedKbArticle($company, ['title' => 'Draft A', 'slug' => 'draft-a', 'status' => 'draft']);
    seedKbArticle($otherCompany, ['title' => 'Other Public', 'slug' => 'other-public', 'status' => 'published']);

    $response = $this->getJson('/api/kb/test-a/articles');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.slug', 'public-a');
});

test('kb api article endpoint returns a published article by slug', function () {
    $company = Company::factory()->create(['slug' => 'test-a']);

    seedKbArticle($company, ['title' => 'Install Guide', 'slug' => 'install-guide', 'status' => 'published']);

    $response = $this->getJson('/api/kb/test-a/articles/install-guide');

    $response->assertOk();
    $response->assertJsonPath('slug', 'install-guide');
    $response->assertJsonPath('title', 'Install Guide');
});

test('kb api article endpoint returns 404 for draft article', function () {
    $company = Company::factory()->create(['slug' => 'test-a']);

    seedKbArticle($company, ['slug' => 'draft-guide', 'status' => 'draft']);

    $this->getJson('/api/kb/test-a/articles/draft-guide')->assertNotFound();
});

test('kb api search returns matched published articles only', function () {
    $company = Company::factory()->create(['slug' => 'test-a']);

    seedKbArticle($company, ['title' => 'Reset Password', 'slug' => 'reset-password', 'status' => 'published']);
    seedKbArticle($company, ['title' => 'Draft Password', 'slug' => 'draft-password', 'status' => 'draft']);

    $response = $this->getJson('/api/kb/test-a/search?q=Password');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.slug', 'reset-password');
});

test('kb api search returns empty array when query is missing', function () {
    $company = Company::factory()->create(['slug' => 'test-a']);

    seedKbArticle($company, ['slug' => 'anything']);

    $response = $this->getJson('/api/kb/test-a/search');

    $response->assertOk();
    $response->assertExactJson(['data' => []]);
});
