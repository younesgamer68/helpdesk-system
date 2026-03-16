<?php

use App\Livewire\Auth\SetPassword;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('redirects pending users to the set password screen during login', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'password' => null,
        'email_verified_at' => null,
        'company_id' => $company->id,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'anything', // It shouldn't matter since password is null
    ]);

    $response->assertRedirect(route('set-password', ['company' => $company->slug]));

    // Check that the email was stored in the session
    $this->assertEquals($user->email, session('pending_user_email'));
});

it('blocks access to set password screen if no pending email in session', function () {
    $this->get(route('set-password'))
        ->assertRedirect(route('login'));
});

it('allows a pending user to set their password and active their account', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'password' => null,
        'email_verified_at' => null,
        'company_id' => $company->id,
    ]);

    // Simulate the login interceptor putting the email in the session
    session()->put('pending_user_email', $user->email);

    Livewire::test(SetPassword::class)
        ->set('password', 'Secretpass123!')
        ->set('password_confirmation', 'Secretpass123!')
        ->call('save')
        ->assertRedirect(route('tickets', ['company' => $company->slug]));

    $user->refresh();

    // Verify the password was hashed and updated
    expect($user->password)->not->toBeNull();
    $this->assertTrue(Hash::check('Secretpass123!', $user->password));

    // Verify they are now marked as email_verified_at
    expect($user->email_verified_at)->not->toBeNull();

    // Verify session was cleared
    $this->assertNull(session('pending_user_email'));

    // Verify user was immediately logged in
    $this->assertAuthenticatedAs($user);
});
