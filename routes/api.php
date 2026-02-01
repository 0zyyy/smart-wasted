<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\DataTransmissionController;
use App\Http\Controllers\Api\MeasurementController;
use App\Http\Controllers\Api\SensorDataController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toISOString(),
    ]);
});

// ESP32 IoT endpoints (supports both GET and POST)
Route::match(['get', 'post'], '/sensor-data', [SensorDataController::class, 'store']);

// Legacy endpoints
Route::post('/measurements', [MeasurementController::class, 'store']);
Route::post('/alerts', [AlertController::class, 'store']);
Route::post('/transmissions', [DataTransmissionController::class, 'store']);

