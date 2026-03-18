<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\WasteDetectionController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toISOString(),
    ]);
});

// ESP32 IoT endpoints (supports both GET and POST)
Route::match(['get', 'post'], '/sensor-data', [SensorDataController::class, 'store'])
    ->middleware('sensor.apikey');

// YOLOv8 camera detection endpoint (PC-side)
Route::post('/waste-detection', [WasteDetectionController::class, 'store'])
    ->middleware('sensor.apikey');

