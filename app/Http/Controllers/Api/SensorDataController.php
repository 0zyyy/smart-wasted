<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSensorDataRequest;
use App\Events\AlertCreated;
use App\Models\Alert;
use App\Models\Bin;
use App\Models\Measurement;
use App\Models\Sensor;
use App\Models\User;
use App\Services\DashboardCacheService;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class SensorDataController extends Controller
{
    private const DUPLICATE_WINDOW_SECONDS = 15;

    public function store(StoreSensorDataRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $normalizedBinType = $validated['bin_type'];
        $location = $validated['location'];
        $weight = (float) $validated['weight'];
        $volume = (float) $validated['volume'];

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
        $anyCreated = false;

        // Get both sensors for this bin
        $sensors = Sensor::where('bin_id', $bin->bin_id)->get();

        foreach ($sensors as $sensor) {
            if ($sensor->type === 'Loadcell') {
                $measurement = $this->findRecentDuplicateMeasurement($sensor->sensor_id, $weight, 'g', $now);
                $duplicate = $measurement !== null;

                if (!$measurement) {
                    $measurement = Measurement::create([
                        'sensor_id' => $sensor->sensor_id,
                        'timestamp' => $now,
                        'value' => $weight,
                        'unit' => 'g',
                    ]);
                    $anyCreated = true;
                }

                $createdMeasurements[] = [
                    'type' => 'weight',
                    'measurement_id' => $measurement->measurement_id,
                    'duplicate' => $duplicate,
                ];
            } elseif ($sensor->type === 'Ultrasonic') {
                $measurement = $this->findRecentDuplicateMeasurement($sensor->sensor_id, $volume, '%', $now);
                $duplicate = $measurement !== null;

                if (!$measurement) {
                    $measurement = Measurement::create([
                        'sensor_id' => $sensor->sensor_id,
                        'timestamp' => $now,
                        'value' => $volume,
                        'unit' => '%',
                    ]);
                    $anyCreated = true;
                }

                $createdMeasurements[] = [
                    'type' => 'volume',
                    'measurement_id' => $measurement->measurement_id,
                    'duplicate' => $duplicate,
                ];

                // Auto-create alert if bin is >= 80% full
                if ($volume >= 80) {
                    $existingAlert = Alert::where('bin_id', $bin->bin_id)
                        ->where('type', 'Overflow')
                        ->where('is_resolved', false)
                        ->first();

                    if (!$existingAlert) {
                        $alert = Alert::create([
                            'bin_id' => $bin->bin_id,
                            'timestamp' => $now,
                            'type' => 'Overflow',
                            'description' => "Bin {$normalizedBinType} at {$location} is {$volume}% full (PENUH)",
                            'status' => Alert::STATUS_OPEN,
                            'severity' => Alert::SEVERITY_CRITICAL,
                            'last_seen_at' => $now,
                            'is_resolved' => false,
                        ]);

                        $alert->logActivity('opened', 'Alert generated from sensor overflow rule.');

                        event(new AlertCreated($alert));

                        $admins = User::all();
                        FilamentNotification::make()
                            ->title('Bin Overflow Alert')
                            ->body("Bin {$normalizedBinType} at {$location} is {$volume}% full.")
                            ->danger()
                            ->sendToDatabase($admins);
                    } else {
                        $existingAlert->update(['last_seen_at' => $now]);
                    }
                }
            }
        }

        if ($anyCreated) {
            DashboardCacheService::bust();
        }

        return response()->json([
            'status' => 'OK',
            'bin_id' => $bin->bin_id,
            'location' => $location,
            'bin_type' => $normalizedBinType,
            'deduplicated' => !$anyCreated,
            'measurements' => $createdMeasurements,
        ], $anyCreated ? 201 : 200);
    }

    private function findRecentDuplicateMeasurement(int $sensorId, float $value, string $unit, Carbon $now): ?Measurement
    {
        return Measurement::query()
            ->where('sensor_id', $sensorId)
            ->where('unit', $unit)
            ->where('value', $value)
            ->where('timestamp', '>=', $now->copy()->subSeconds(self::DUPLICATE_WINDOW_SECONDS))
            ->orderByDesc('timestamp')
            ->first();
    }
}
