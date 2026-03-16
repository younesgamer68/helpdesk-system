<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

it('aborts when the invitation link has an invalid signature', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => null,
    ]);

    // Generate a valid URL, then tamper with it
    $validUrl = URL::temporarySignedRoute('invitations.accept', now()->addHours(72), ['user' => $user->id]);
    $invalidUrl = $validUrl.'invalid-hash-addition';

    $response = get($invalidUrl);

    $response->assertStatus(403);
});

it('aborts when the invitation link points to an invalid user', function () {
    $invalidUrl = URL::temporarySignedRoute('invitations.accept', now()->addHours(72), ['user' => 99999]);

    $response = get($invalidUrl);

    // Will hit a 404 because of implicit model binding failure
    $response->assertStatus(404);
});

it('redirects accepted users back to login if they already have an active password', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password'), // already active
    ]);

    $validUrl = URL::temporarySignedRoute('invitations.accept', now()->addHours(72), ['user' => $user->id]);

    $response = get($validUrl);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'You have already accepted your invitation. Please log in.');
});

it('securely loads the pending agent session and redirects to set password', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => null, // pending
    ]);

    $validUrl = URL::temporarySignedRoute('invitations.accept', now()->addHours(72), ['user' => $user->id]);

    $response = get($validUrl);

    $response->assertRedirect(route('set-password'));

    // Assert the session was correctly bound
    $this->assertEquals($user->email, session('pending_user_email'));
});

it('queues an expired-invite notification email when expired invitation link is opened', function () {
    Mail::fake();

    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => null,
        'invite_sent_at' => now()->subDays(3),
        'invite_expires_at' => now()->subHour(),
        'invite_expired_notified_at' => null,
    ]);

    $expiredUrl = URL::temporarySignedRoute('invitations.accept', now()->subHour(), ['user' => $user->id]);

    $response = get($expiredUrl);

    $response->assertStatus(403);
    Mail::assertQueued(\App\Mail\InviteExpiredMail::class, 1);

    expect($user->fresh()->invite_expired_notified_at)->not->toBeNull();
});
