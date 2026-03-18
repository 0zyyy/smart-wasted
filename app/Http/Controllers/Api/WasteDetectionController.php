<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWasteDetectionRequest;
use App\Models\Location;
use App\Models\WasteDetection;
use Illuminate\Http\JsonResponse;

class WasteDetectionController extends Controller
{
    public function store(StoreWasteDetectionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $locationId = Location::where('name', $validated['location'])->value('location_id');

        $deviceTimestamp = isset($validated['device_timestamp'])
            ? (float) $validated['device_timestamp']
            : null;

        $latencyMs = $deviceTimestamp
            ? max(0, (int) round((microtime(true) - $deviceTimestamp) * 1000))
            : null;

        $detection = WasteDetection::create([
            'location_id'    => $locationId,
            'detected_class' => $validated['waste_type'],
            'confidence'     => (float) $validated['confidence'],
            'timestamp'      => now(),
            'device_id'      => $validated['device_id'] ?? null,
            'latency_ms'     => $latencyMs,
        ]);

        return response()->json([
            'status'         => 'OK',
            'detection_id'   => $detection->detection_id,
            'location'       => $validated['location'],
            'detected_class' => $detection->detected_class,
            'confidence'     => $detection->confidence,
            'latency_ms'     => $detection->latency_ms,
        ], 201);
    }
}
