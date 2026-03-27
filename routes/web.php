<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Customer\CustomerPortalController;

// ── Public Landing Pages ──────────────────────────────────────────
Route::get('/',         [LandingController::class, 'home'])->name('home');
Route::get('/features', [LandingController::class, 'features'])->name('features');
Route::get('/pricing',  [LandingController::class, 'pricing'])->name('pricing');
Route::get('/contact',  [LandingController::class, 'contact'])->name('contact');
Route::post('/contact', [LandingController::class, 'submitContact'])->name('contact.submit');

// ── GitHub Auto-Deploy Webhook ────────────────────────────────────
Route::post('/webhook/deploy', [App\Http\Controllers\Admin\DeployWebhookController::class, 'handle'])
    ->name('webhook.deploy');

// ── Admin: Device QR ─────────────────────────────────────────────
Route::get('/admin/devices/{id}/qr', [App\Http\Controllers\Admin\DeviceQrController::class, 'show'])
    ->name('admin.device.qr')
    ->middleware('auth');

// ── Admin: App Release Upload ────────────────────────────────────
Route::post('/admin/app-release/upload', [App\Http\Controllers\Admin\AppReleaseController::class, 'upload'])
    ->name('admin.app-release.upload')
    ->middleware('auth');

Route::post('/admin/app-release/delete-latest', [App\Http\Controllers\Admin\AppReleaseController::class, 'deleteLatest'])
    ->name('admin.app-release.delete-latest')
    ->middleware('auth');

// ── Customer Portal ───────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/login',   [App\Http\Controllers\Customer\CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [App\Http\Controllers\Customer\CustomerAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [App\Http\Controllers\Customer\CustomerAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [CustomerPortalController::class, 'index'])->name('dashboard');
        Route::get('/payments',  [CustomerPortalController::class, 'payments'])->name('payments');
        Route::get('/pay/{planId}',  [CustomerPortalController::class, 'payNow'])->name('pay');
        Route::post('/pay/{planId}', [CustomerPortalController::class, 'processPayment'])->name('pay.post');
    });
});

// ── Device API (workaround for /api/ routing issues) ─────────────
Route::prefix('device-api')->middleware('api')->group(function () {
    Route::post('/devices/register', [App\Http\Controllers\Api\DeviceController::class, 'register']);
    
    Route::prefix('devices')->middleware('device.auth')->group(function () {
        Route::post('/{imei}/heartbeat', [App\Http\Controllers\Api\DeviceController::class, 'heartbeat']);
        Route::post('/{imei}/events', [App\Http\Controllers\Api\DeviceController::class, 'reportEvent']);
        Route::get('/{imei}/status', [App\Http\Controllers\Api\DeviceController::class, 'status']);
        Route::post('/commands/{commandId}/ack', [App\Http\Controllers\Api\DeviceController::class, 'ackCommand']);
    });
    
    Route::get('/app/update-check', [App\Http\Controllers\Api\AppUpdateController::class, 'check']);
});
