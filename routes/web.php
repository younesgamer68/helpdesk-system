<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\QuickRegisterController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ====== HOME ======
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ====== AUTH ======
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');

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

// Setup Company (after Google OAuth)
Route::get('/setup-company', App\Livewire\Auth\SetupCompany::class)
    ->middleware('auth')
    ->name('setup-company');

// ====== EMAIL VERIFICATION ======
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $company = Auth::user()->company;
    return redirect()->to('https://' . $company->slug . '.' . config('app.domain') . '/tickets');
})->middleware(['auth', 'signed'])->name('verification.verify');

// ====== SUBDOMAIN (company) ======
Route::domain('{company}.' . config('app.domain'))->group(function () {
    Route::middleware(['auth', 'company.access', 'verified'])->group(function () {

        Route::view('tickets', 'dashboard.tickets.index')->name('tickets');
        Route::get('tickets/{ticket}', [TicketsController::class, 'show'])->name('details');
        Route::view('technicians', 'dashboard.technicians')
            ->can('view-technicians')
            ->name('technicians');
    });
});

require __DIR__ . '/settings.php';