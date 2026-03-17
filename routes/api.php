<?php

use App\Http\Controllers\Api\SensorDataController;
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

