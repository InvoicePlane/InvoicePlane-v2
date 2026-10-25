<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotes\Http\Controllers\GuestQuoteController;
use Modules\Quotes\Http\Middleware\SetQuoteLocale;

Route::name('quotes.guest.')->prefix('quotes/{quote:url_key}')->middleware(SetQuoteLocale::class)->group(function (): void {
    Route::get('/', [GuestQuoteController::class, 'show'])->name('show');
    Route::post('/password', [GuestQuoteController::class, 'verifyPassword'])
        ->middleware('throttle:10,1')
        ->name('password');
    Route::post('/sign', [GuestQuoteController::class, 'sign'])->name('sign');
    Route::get('/pdf', [GuestQuoteController::class, 'pdf'])->name('pdf');
    Route::get('/logo', [GuestQuoteController::class, 'logo'])->name('logo');
    Route::get('/signatures/{signature}', [GuestQuoteController::class, 'signatureImage'])->name('signature');
});
