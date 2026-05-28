<?php

use App\Http\Controllers\ManagerOverrideController;
use App\Http\Controllers\QrWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Stateless routes (no session, no CSRF). The QR webhook is unauthenticated
| so table scans work without a session cookie. Manager override requires
| the cashier's web session (auth middleware via sanctum or web guard).
*/

// Public — called by customer's phone after scanning a table QR code
Route::post('/qr/scan', [QrWebhookController::class, 'handle'])
    ->name('api.qr.scan');

// Protected — cashier triggers this from an AJAX modal on the POS screen
Route::middleware('auth')->group(function () {
    Route::post('/manager/verify', [ManagerOverrideController::class, 'verify'])
        ->name('api.manager.verify');
});
