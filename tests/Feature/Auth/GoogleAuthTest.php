<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;

it('redirects invited pending users to set password when logging in with google', function () {
    $company = Company::factory()->create();
    $invitedOperator = User::factory()->operator()->create([
        'company_id' => $company->id,
        'password' => null,
        'email_verified_at' => null,
        'google_id' => null,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn($invitedOperator->email);
    $socialiteUser->shouldReceive('getName')->andReturn('Invited Operator');
    $socialiteUser->shouldReceive('getNickname')->andReturn('invited');
    $socialiteUser->shouldReceive('getId')->andReturn('google-123');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.png');

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('set-password'));
    $response->assertSessionHas('pending_user_email', $invitedOperator->email);

    $this->assertGuest();
    expect($invitedOperator->fresh()->password)->toBeNull();
});

it('logs in an existing user by email without creating a duplicate account', function () {
    $companyB = Company::factory()->create();

    $existingGoogleUser = User::factory()->admin()->create([
        'company_id' => $companyB->id,
        'email' => 'existing-google@example.com',
        'google_id' => null,
        'email_verified_at' => now()->subDay(),
    ]);

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getEmail')->andReturn($existingGoogleUser->email);
    $socialiteUser->shouldReceive('getName')->andReturn($existingGoogleUser->name);
    $socialiteUser->shouldReceive('getNickname')->andReturn('existing-google');
    $socialiteUser->shouldReceive('getId')->andReturn('google-existing-456');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.png');

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('agent.dashboard', ['company' => $companyB->slug]));
    expect($existingGoogleUser->fresh()->google_id)->toBe('google-existing-456');
    expect(User::where('email', $existingGoogleUser->email)->count())->toBe(1);
});
