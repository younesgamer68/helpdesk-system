<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\URL;

it('aborts when the invitation link has an invalid signature', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => null,
    ]);

    // Generate a valid URL, then tamper with it
    $validUrl = URL::signedRoute('invitations.accept', ['user' => $user->id]);
    $invalidUrl = $validUrl.'invalid-hash-addition';

    $response = $this->get($invalidUrl);

    $response->assertStatus(403);
});

it('aborts when the invitation link points to an invalid user', function () {
    $invalidUrl = URL::signedRoute('invitations.accept', ['user' => 99999]);

    $response = $this->get($invalidUrl);

    // Will hit a 404 because of implicit model binding failure
    $response->assertStatus(404);
});

it('redirects accepted users back to login if they already have an active password', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password'), // already active
    ]);

    $validUrl = URL::signedRoute('invitations.accept', ['user' => $user->id]);

    $response = $this->get($validUrl);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'You have already accepted your invitation. Please log in.');
});

it('securely loads the pending agent session and redirects to set password', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => null, // pending
    ]);

    $validUrl = URL::signedRoute('invitations.accept', ['user' => $user->id]);

    $response = $this->get($validUrl);

    $response->assertRedirect(route('set-password'));

    // Assert the session was correctly bound
    $this->assertEquals($user->email, session('pending_user_email'));
});
