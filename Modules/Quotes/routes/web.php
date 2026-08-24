<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotes\Http\Controllers\GuestQuoteController;

Route::name('quotes.guest.')->prefix('quotes/{quote:url_key}')->group(function (): void {
    Route::get('/', [GuestQuoteController::class, 'show'])->name('show');
    Route::post('/password', [GuestQuoteController::class, 'verifyPassword'])
        ->middleware('throttle:10,1')
        ->name('password');
    Route::post('/sign', [GuestQuoteController::class, 'sign'])->name('sign');
    Route::get('/pdf', [GuestQuoteController::class, 'pdf'])->name('pdf');
});
