<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\QuickRegisterController;
use App\Http\Controllers\TicketsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ====== HOME ======
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ====== AUTH ======
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::get('/set-password', App\Livewire\Auth\SetPassword::class)
        ->name('set-password')
        ->middleware('user.pending');

    // Invitation link acceptance
    Route::get('/invitation/{user}', App\Http\Controllers\Auth\InvitationController::class)
        ->name('invitations.accept')
        ->middleware('signed');

    Route::post('/register/quick', [QuickRegisterController::class, 'store'])
        ->name('register.quick');

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', function () {
    Auth::logout();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

// Setup Company (after Google OAuth + email verified)
Route::get('/setup-company', App\Livewire\Auth\SetupCompany::class)
    ->middleware(['auth', 'verified'])
    ->name('setup-company');

// ====== EMAIL VERIFICATION WITH CODE ======
Route::get('/email/verify', App\Livewire\Auth\VerifyEmailCode::class)
    ->middleware('auth')
    ->name('verification.notice');

// Keep link-based verification as backup
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = Auth::user();

    // Si l'utilisateur a déjà une company → dashboard
    if ($user->company_id && $user->company) {
        return redirect()->to('https://'.$user->company->slug.'.'.config('app.domain').'/tickets');
    }

    // Sinon → formulaire setup company
    return redirect()->route('setup-company');
})->middleware(['auth', 'signed'])->name('verification.verify');

// ====== SUBDOMAIN (company) ======
Route::domain('{company}.'.config('app.domain'))->group(function () {
    Route::middleware(['auth', 'company.access', 'verified'])->group(function () {

        // Onboarding form for the company
        Route::get('/onboarding', \App\Livewire\Onboarding\Wizard::class)->name('onboarding.wizard');

        // Dashboard routes (require onboarding)
        Route::middleware(['company.is_onboarded'])->group(function () {
            Route::view('tickets', 'dashboard.tickets.index')->name('tickets');
            Route::get('tickets/{ticket}', [TicketsController::class, 'show'])->name('details');
            Route::get('/operators', fn () => view('dashboard.operators'))
                ->middleware('can:view-operators,App\Models\User')
                ->name('operators');
        });
    });
});

require __DIR__.'/settings.php';
