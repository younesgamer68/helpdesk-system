<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])
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
