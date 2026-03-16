<?php

use App\Models\Company;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);

    $response = $this->get("http://{$company->slug}.".config('app.domain').'/dashboard');
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    $this->actingAs($user);

    $response = $this->get("http://{$company->slug}.".config('app.domain').'/dashboard');
    $response->assertRedirect();
});
