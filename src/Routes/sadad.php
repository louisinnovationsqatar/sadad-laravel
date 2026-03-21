<?php
// Built by Louis Innovations (www.louis-innovations.com)

use Illuminate\Support\Facades\Route;
use LouisInnovations\SadadLaravel\Http\Controllers\SadadCallbackController;
use LouisInnovations\SadadLaravel\Http\Controllers\SadadWebhookController;

/*
|--------------------------------------------------------------------------
| SADAD Payment Routes
|--------------------------------------------------------------------------
|
| These routes are automatically loaded by SadadServiceProvider. They handle
| the two server-side endpoints that SADAD calls:
|
|   POST /sadad/callback  – Return URL after customer completes/abandons checkout.
|   POST /sadad/webhook   – Asynchronous payment notification from SADAD.
|
| CSRF verification is disabled for both routes because SADAD posts to them
| directly without a Laravel session/CSRF token.
|
| The webhook route is wrapped with the VerifySadadWebhook middleware which
| validates the SHA-256 checksumhash before the request reaches the controller.
|
*/

Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->prefix('sadad')
    ->name('sadad.')
    ->group(function () {

        Route::post('/callback', SadadCallbackController::class)
            ->name('callback');

        Route::post('/webhook', SadadWebhookController::class)
            ->middleware(\LouisInnovations\SadadLaravel\Http\Middleware\VerifySadadWebhook::class)
            ->name('webhook');
    });
