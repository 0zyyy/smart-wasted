<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SensorApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('app.sensor_api_key');

        // If no key is configured, allow through (dev/unconfigured environments)
        if (!$configuredKey) {
            return $next($request);
        }

        // Accept key from header OR query param (for GET-based ESP32 firmware)
        $providedKey = $request->header('X-Sensor-Key') ?? $request->query('api_key');

        if ($providedKey !== $configuredKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
