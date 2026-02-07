<?php

use App\Http\Controllers\WidgetController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\FormWidget;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Widget routes - PUBLIC (on company subdomains)
Route::domain('{company}.' . config('app.domain'))->prefix('widget')->name('widget.')->group(function () {
    Route::get('/{key}', [WidgetController::class, 'show'])->name('show');
    Route::post('/{key}/submit', [WidgetController::class, 'submit'])->name('submit');
    Route::get('/verify/{ticketNumber}/{token}', [WidgetController::class, 'verify'])->name('verify');
    Route::get('/track/{ticketNumber}/{token}', [WidgetController::class, 'track'])->name('track');
    Route::post('/track/{ticketNumber}/{token}/reply', [WidgetController::class, 'reply'])->name('reply');
});

// Settings routes - AUTHENTICATED (on company subdomains)
Route::domain('{company}.' . config('app.domain'))
    ->middleware(['auth', 'company.access', 'verified'])
    ->group(function () {
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
