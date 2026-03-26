<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Create your account');
    $response->assertDontSee('Remember me');
});

test('admins are redirected to the home dashboard route after login', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->admin()->create(['company_id' => $company->id]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('agent.dashboard', ['company' => $company->slug]));

    $this->assertAuthenticated();
});

test('operators are redirected to the operator dashboard after login', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->operator()->create(['company_id' => $company->id]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('agent.dashboard', ['company' => $company->slug]));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->withTwoFactor()->create(['company_id' => $company->id]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)->post(route('logout'));

    $protocol = app()->environment('local') ? 'http' : 'https';

    $response->assertRedirect($protocol.'://'.config('app.domain').'/');
    $this->assertGuest();
});
