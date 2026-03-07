<?php

use App\Livewire\Settings\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('password settings page renders successfully', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->assertStatus(200);
});

test('current password is required if user has a password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->set('current_password', '')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);
});

test('current password is NOT required if user does NOT have a password', function () {
    $user = User::factory()->create([
        'password' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->set('current_password', '')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('current password field is hidden if user does NOT have a password', function () {
    $user = User::factory()->create([
        'password' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->assertDontSee('Current password');
});

test('current password field is visible if user has a password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->assertSee('Current password');
});
