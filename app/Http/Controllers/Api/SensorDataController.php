<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Bin;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorDataController extends Controller
{
    /**
     * Receive data from ESP32 IoT device.
     * 
     * Supports both GET (ESP32 format) and POST:
     * GET: ?lokasi=organik&berat=1250&volume=75&device=BB102
     * POST: { "bin_type": "organik", "weight": 1250, "volume": 75, "location": "BB102" }
     */
    public function store(Request $request): JsonResponse
    {
        // Support both GET (ESP32) and POST formats
        if ($request->isMethod('get')) {
            // ESP32 format: ?lokasi=organik&berat=123&volume=45&device=BB102
            $binType = $request->query('lokasi', $request->query('bin_type'));
            $weight = $request->query('berat', $request->query('weight'));
            $volume = $request->query('volume');
            $location = $request->query('device', $request->query('location', 'BB102'));
        } else {
            // POST JSON format
            $binType = $request->input('lokasi', $request->input('bin_type'));
            $weight = $request->input('berat', $request->input('weight'));
            $volume = $request->input('volume');
            $location = $request->input('device', $request->input('location', 'BB102'));
        }

        // Validate required fields
        if (!$binType || $weight === null || $volume === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required fields: lokasi/bin_type, berat/weight, volume',
            ], 400);
        }

        // Normalize bin type to match database values
        $binTypeMap = [
            'organik' => 'Organic',
            'anorganik' => 'Anorganic',
            'b3' => 'B3',
            'organic' => 'Organic',
            'anorganic' => 'Anorganic',
        ];
        
        $normalizedBinType = $binTypeMap[strtolower($binType)] ?? null;
        
        if (!$normalizedBinType) {
            return response()->json([
                'status' => 'error',
                'message' => "Invalid bin type: {$binType}. Use: organik, anorganik, b3",
            ], 400);
        }

        // Validate location
        $validLocations = ['BB102', 'BB202', 'AA107', 'AA108'];
        if (!in_array($location, $validLocations)) {
            return response()->json([
                'status' => 'error',
                'message' => "Invalid location: {$location}. Use: " . implode(', ', $validLocations),
            ], 400);
        }

        // Find the bin at this location
        $bin = Bin::whereHas('location', function ($query) use ($location) {
            $query->where('name', $location);
        })->where('type', $normalizedBinType)->first();

        if (!$bin) {
            return response()->json([
                'status' => 'error',
                'message' => "Bin '{$normalizedBinType}' not found at location '{$location}'",
            ], 404);
        }

        $now = now();
        $createdMeasurements = [];

        // Get both sensors for this bin
        $sensors = Sensor::where('bin_id', $bin->bin_id)->get();

        foreach ($sensors as $sensor) {
            if ($sensor->type === 'Loadcell') {
                // Store weight measurement (grams)
                $measurement = Measurement::create([
                    'sensor_id' => $sensor->sensor_id,
                    'timestamp' => $now,
                    'value' => (float) $weight,
                    'unit' => 'g',
                ]);
                $createdMeasurements[] = [
                    'type' => 'weight',
                    'measurement_id' => $measurement->measurement_id,
                ];
            } elseif ($sensor->type === 'Ultrasonic') {
                // Store volume percentage
                $measurement = Measurement::create([
                    'sensor_id' => $sensor->sensor_id,
                    'timestamp' => $now,
                    'value' => (float) $volume,
                    'unit' => '%',
                ]);
                $createdMeasurements[] = [
                    'type' => 'volume',
                    'measurement_id' => $measurement->measurement_id,
                ];

                // Auto-create alert if bin is >= 80% full
                if ((float) $volume >= 80) {
                    $existingAlert = Alert::where('bin_id', $bin->bin_id)
                        ->where('type', 'Overflow')
                        ->where('is_resolved', false)
                        ->first();

                    if (!$existingAlert) {
                        Alert::create([
                            'bin_id' => $bin->bin_id,
                            'timestamp' => $now,
                            'type' => 'Overflow',
                            'description' => "Bin {$normalizedBinType} at {$location} is {$volume}% full (PENUH)",
                            'is_resolved' => false,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'OK',
            'bin_id' => $bin->bin_id,
            'location' => $location,
            'bin_type' => $normalizedBinType,
            'measurements' => $createdMeasurements,
        ], 201);
    }
}
