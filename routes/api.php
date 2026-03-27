<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\EmiController;

// ── Auth ──────────────────────────────────────────────────────────
Route::post('/auth/customer/login', [AuthController::class, 'customerLogin']);

// ── Device registration (no auth — uses api_key from QR) ──────────
Route::post('/devices/register', [DeviceController::class, 'register']);

// ── Device API (authenticated via X-Device-Key header) ────────────
Route::prefix('devices')->middleware('device.auth')->group(function () {
    Route::post('/{imei}/heartbeat',         [DeviceController::class, 'heartbeat']);
    Route::post('/{imei}/events',            [DeviceController::class, 'reportEvent']);
    Route::get('/{imei}/status',             [DeviceController::class, 'status']);
    Route::post('/commands/{commandId}/ack', [DeviceController::class, 'ackCommand']);
});

// ── OTA App Updates ───────────────────────────────────────────────
Route::get('/app/update-check', [App\Http\Controllers\Api\AppUpdateController::class, 'check']);

// ── Customer API (Sanctum token) ──────────────────────────────────
Route::middleware('auth:sanctum')->prefix('customer')->group(function () {
    Route::get('/devices/{deviceId}/emi',      [EmiController::class, 'schedule']);
    Route::post('/devices/{deviceId}/payment', [EmiController::class, 'recordPayment']);
    Route::post('/auth/logout',                [AuthController::class, 'logout']);
});
