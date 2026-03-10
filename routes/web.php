<?php

use App\Http\Controllers\ChatbotFaqController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/chatbot/faqs', [ChatbotFaqController::class, 'random'])->name('chatbot.faqs');
Route::post('/chatbot/chat', [ChatbotFaqController::class, 'chat'])->name('chatbot.chat');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
