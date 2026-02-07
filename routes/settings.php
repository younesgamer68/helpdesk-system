<?php

use App\Http\Controllers\WidgetController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\FormWidget;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Widget routes - PUBLIC, no auth required, but with company context
Route::prefix('{company:slug}/widget')->name('widget.')->group(function () {
    // Show widget form
    Route::get('/{key}', [WidgetController::class, 'show'])->name('show');

    // Submit ticket from widget
    Route::post('/{key}/submit', [WidgetController::class, 'submit'])->name('submit');

    // Verify ticket via email link
    Route::get('/verify/{ticketNumber}/{token}', [WidgetController::class, 'verify'])->name('verify');

    // Track ticket (view + reply)
    Route::get('/track/{ticketNumber}/{token}', [WidgetController::class, 'track'])->name('track');
    Route::post('/track/{ticketNumber}/{token}/reply', [WidgetController::class, 'reply'])->name('reply');
});

// Authenticated routes with company context
Route::middleware(['auth', 'company.access', 'verified'])->prefix('{company:slug}')->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::livewire('settings/profile', Profile::class)->name('profile.edit');
    Route::get('/settings/form-widget', FormWidget::class)->name('form-widget.edit');
    Route::livewire('settings/password', Password::class)->name('user-password.edit');
    Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::livewire('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
