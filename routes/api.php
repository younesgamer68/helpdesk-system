<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('kb/{company_slug}')->name('api.kb.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\KbController::class, 'index'])->name('index');
    Route::get('/articles', [\App\Http\Controllers\Api\KbController::class, 'articles'])->name('articles');
    Route::get('/articles/{slug}', [\App\Http\Controllers\Api\KbController::class, 'article'])->name('article');
    Route::get('/search', [\App\Http\Controllers\Api\KbController::class, 'search'])->name('search');
});
