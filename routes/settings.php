<?php

use App\Http\Controllers\WidgetController;
use App\Livewire\Settings\AiCopilot;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\CompanyProfile;
use App\Livewire\Settings\DangerZone;
use App\Livewire\Settings\EmailConfiguration;
use App\Livewire\Settings\FormWidget;
use App\Livewire\Settings\NotificationPreferences;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Widget routes - PUBLIC (on company subdomains)
Route::domain('{company}.'.config('app.domain'))->prefix('widget')->name('widget.')->group(function () {
    Route::get('/{key}', [WidgetController::class, 'show'])->name('show');
    Route::post('/{key}/submit', [WidgetController::class, 'submit'])->name('submit');
    Route::get('/verify/{ticketNumber}/{token}', [WidgetController::class, 'verify'])->name('verify');
    Route::get('/track/{ticketNumber}/{token}', [WidgetController::class, 'track'])->name('track');
    Route::post('/track/{ticketNumber}/{token}/reply', [WidgetController::class, 'reply'])->name('reply');
});

Route::domain('{company}.'.config('app.domain'))
    ->prefix('chatbot-widget')
    ->name('chatbot.widget.')
    ->group(function () {
        Route::get('/{key}/embed.js', [\App\Http\Controllers\ChatbotWidgetController::class, 'snippet'])
            ->name('embed');
        Route::get('/{key}', [\App\Http\Controllers\ChatbotWidgetController::class, 'show'])
            ->name('show');
        Route::post('/{key}/message', [\App\Http\Controllers\ChatbotWidgetController::class, 'message'])
            ->middleware('throttle:30,1')
            ->name('message');
    });

// Settings routes - AUTHENTICATED (on company subdomains)
Route::domain('{company}.'.config('app.domain'))
    ->middleware(['auth', 'company.access', 'verified'])
    ->group(function () {
        // Default redirect to Company Profile (admins) or Security (operators)
        Route::redirect('settings', 'settings/company');

        // Kept for back-compat (profile page still exists)
        Route::livewire('settings/profile', Profile::class)->name('profile.edit');
        Route::get('/widget-settings', FormWidget::class)->name('form-widget.edit');

        // Legacy password/2FA routes kept as redirects for any existing links
        Route::redirect('settings/password', 'settings/security')->name('user-password.edit');
        Route::redirect('settings/two-factor', 'settings/security')->name('two-factor.show');

        Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');
        Route::livewire('settings/notifications', NotificationPreferences::class)->name('notifications.preferences');
        Route::livewire('settings/security', Security::class)->name('settings.security');
        Route::livewire('settings/danger', DangerZone::class)->name('settings.danger');

        Route::livewire('settings/company', CompanyProfile::class)
            ->middleware(\App\Http\Middleware\AdminOnly::class)
            ->name('company.profile');

        Route::livewire('settings/ai-copilot', AiCopilot::class)
            ->middleware(\App\Http\Middleware\AdminOnly::class)
            ->name('settings.ai-copilot');

        Route::livewire('settings/email', EmailConfiguration::class)
            ->middleware(\App\Http\Middleware\AdminOnly::class)
            ->name('settings.email');
    });

// Widget routes - PUBLIC (on company subdomains)
Route::domain('{company}.'.config('app.domain'))->prefix('widget')->name('widget.')->group(function () {
    Route::get('/{key}', [WidgetController::class, 'show'])->name('show');
    Route::post('/{key}/submit', [WidgetController::class, 'submit'])->name('submit');
    Route::get('/verify/{ticketNumber}/{token}', [WidgetController::class, 'verify'])->name('verify');
    Route::get('/track/{ticketNumber}/{token}', [WidgetController::class, 'track'])->name('track');
    Route::post('/track/{ticketNumber}/{token}/reply', [WidgetController::class, 'reply'])->name('reply');
});

Route::domain('{company}.'.config('app.domain'))
    ->prefix('chatbot-widget')
    ->name('chatbot.widget.')
    ->group(function () {
        Route::get('/{key}/embed.js', [\App\Http\Controllers\ChatbotWidgetController::class, 'snippet'])
            ->name('embed');
        Route::get('/{key}', [\App\Http\Controllers\ChatbotWidgetController::class, 'show'])
            ->name('show');
        Route::post('/{key}/message', [\App\Http\Controllers\ChatbotWidgetController::class, 'message'])
            ->middleware('throttle:30,1')
            ->name('message');
    });

// Settings routes - AUTHENTICATED (on company subdomains)
Route::domain('{company}.'.config('app.domain'))
    ->middleware(['auth', 'company.access', 'verified'])
    ->group(function () {
        Route::redirect('settings', 'settings/profile');
        Route::livewire('settings/profile', Profile::class)->name('profile.edit');
        Route::get('/widget-settings', FormWidget::class)->name('form-widget.edit');
        Route::livewire('settings/password', Password::class)->name('user-password.edit');
        Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');
        Route::livewire('settings/notifications', NotificationPreferences::class)->name('notifications.preferences');

        Route::livewire('settings/company', CompanyProfile::class)
            ->middleware(\App\Http\Middleware\AdminOnly::class)
            ->name('company.profile');

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
