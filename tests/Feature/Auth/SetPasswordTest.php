<?php

use App\Livewire\Auth\SetPassword;
use App\Models\Company;
use App\Models\TicketCategory;
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
        ->assertRedirect(route('agent.dashboard', ['company' => $company->slug]));

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

it('keeps operator specialties empty when no category is selected', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'password' => null,
        'email_verified_at' => null,
        'company_id' => $company->id,
        'role' => 'operator',
        'specialty_id' => null,
    ]);

    session()->put('pending_user_email', $operator->email);

    Livewire::test(SetPassword::class)
        ->set('password', 'Secretpass123!')
        ->set('password_confirmation', 'Secretpass123!')
        ->set('selectedSpecialties', [])
        ->call('save')
        ->assertRedirect(route('agent.dashboard', ['company' => $company->slug]));

    $operator->refresh();

    expect($operator->specialty_id)->toBeNull();
    expect($operator->categories()->count())->toBe(0);
});

it('saves multiple operator specialties and sets primary specialty to the first selected', function () {
    $company = Company::factory()->create();
    $operator = User::factory()->create([
        'password' => null,
        'email_verified_at' => null,
        'company_id' => $company->id,
        'role' => 'operator',
        'specialty_id' => null,
    ]);

    $categoryA = TicketCategory::factory()->create(['company_id' => $company->id]);
    $categoryB = TicketCategory::factory()->create(['company_id' => $company->id]);

    session()->put('pending_user_email', $operator->email);

    Livewire::test(SetPassword::class)
        ->set('password', 'Secretpass123!')
        ->set('password_confirmation', 'Secretpass123!')
        ->set('selectedSpecialties', [$categoryB->id, $categoryA->id])
        ->call('save')
        ->assertRedirect(route('agent.dashboard', ['company' => $company->slug]));

    $operator->refresh();
    $attachedCategoryIds = $operator->categories()->pluck('ticket_categories.id')->all();

    expect($operator->specialty_id)->toBe($categoryB->id);
    expect($attachedCategoryIds)->toContain($categoryA->id, $categoryB->id);
    expect($attachedCategoryIds)->toHaveCount(2);
});
