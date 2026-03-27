<?php

use App\Livewire\Settings\Profile;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    $this->get("http://{$company->slug}.".config('app.domain').'/settings/profile')->assertOk();
});

test('profile page does not show delete account block', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->assertDontSee('Delete account');
});

test('profile information can be updated', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertSoftDeleted('users', ['id' => $user->id]);
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('user can upload avatar', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'))
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar);
});

test('user can reset avatar', function () {
    Storage::fake('public');

    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'avatar' => 'avatars/existing-avatar.jpg',
    ]);

    Storage::disk('public')->put('avatars/existing-avatar.jpg', 'fake-image');

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->call('resetAvatar')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->avatar)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/existing-avatar.jpg');
});
