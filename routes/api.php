<?php

use App\Http\Controllers\Managers\Mailer\MailerEndpointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ─── Mailer Endpoints API (públicos con throttle) ─────────────────────────────
Route::middleware(['api', 'throttle:60,1'])
    ->prefix('email-endpoints')
    ->name('api.mailer.')
    ->group(function () {
        Route::post('/{slug}/send', [MailerEndpointController::class, 'send'])->name('send')->middleware('throttle:30,1');
        Route::get('/{slug}/info', [MailerEndpointController::class, 'info'])->name('info');
        Route::get('/{slug}/status', [MailerEndpointController::class, 'status'])->name('status');
    });
