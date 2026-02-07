<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\WidgetController;
use App\Livewire\Settings\FormWidget;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;




Route::get('/', function () {
    return view('welcome');
})->name('home');

// Email Verification Notice (shown after registration)
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Email Verification Handler (when user clicks link in email)
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('tickets', ['company' => Auth::user()->company->slug]);
})->middleware(['auth', 'signed'])->name('verification.verify');



Route::middleware(['auth', 'company.access', 'verified'])
    ->prefix('{company:slug}')
    ->group(function () {

        Route::view('tickets', 'dashboard.tickets.index')
            ->name('tickets');

        Route::get('tickets/{ticket}', [TicketsController::class, 'show'])->name('details');


        Route::view('technicians', 'dashboard.technicians')
            ->can('view-technicians')
            ->name('technicians');

});

require __DIR__ . '/settings.php';
