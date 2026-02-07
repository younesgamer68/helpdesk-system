<?php

use App\Http\Controllers\TicketsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Main domain routes (no subdomain)
Route::get('/', function () {
    return view('welcome');
})->name('home');


// Email Verification Routes (work on any subdomain)
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    $company = Auth::user()->company;
    return redirect()->to('https://' . $company->slug . '.' . config('app.domain') . '/tickets');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Subdomain routes - all company-specific routes
Route::domain('{company}.' . config('app.domain'))->group(function () {

    Route::middleware(['auth', 'company.access', 'verified'])->group(function () {

        Route::view('tickets', 'dashboard.tickets.index')
            ->name('tickets');

        Route::get('tickets/{ticket}', [TicketsController::class, 'show'])
            ->name('details');

        Route::view('technicians', 'dashboard.technicians')
            ->can('view-technicians')
            ->name('technicians');
    });
});

require __DIR__ . '/settings.php';
