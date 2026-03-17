<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Bin;
use App\Models\DataTransmission;
use App\Models\Location;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    private const TTL = 30; // seconds

    private const KEY_STATS = 'sw:dash:stats';
    private const KEY_SENSOR_HEALTH = 'sw:dash:sensor_health';
    private const KEY_LOCATION_FILL = 'sw:dash:location_fill';
    private const KEY_TRANSMISSION_RATE = 'sw:dash:transmission_rate';

    /**
     * Core dashboard stats: counts, active/silent sensors, maintenance due.
     */
    public static function getStats(): array
    {
        return Cache::remember(self::KEY_STATS, self::TTL, function () {
            $now = now();
            $sensorFreshCutoff = $now->copy()->subMinutes(10);
            $maintenanceCutoff = $now->copy()->subMonths(6);

            $activeSensors = Sensor::whereHas('measurements', function ($query) use ($sensorFreshCutoff): void {
                $query->where('timestamp', '>=', $sensorFreshCutoff);
            })->count();

            $silentSensors = Sensor::whereDoesntHave('measurements', function ($query) use ($sensorFreshCutoff): void {
                $query->where('timestamp', '>=', $sensorFreshCutoff);
            })->count();

            $dueMaintenance = Sensor::whereNull('last_maintenance')
                ->orWhere('last_maintenance', '<=', $maintenanceCutoff)
                ->count();

            return [
                'locations' => Location::count(),
                'locations_list' => Location::withCount('bins')->orderBy('name')->limit(6)->get(),
                'bins' => Bin::count(),
                'sensors' => Sensor::count(),
                'measurements' => Measurement::count(),
                'alerts_open' => Alert::where('is_resolved', false)->count(),
                'active_sensors' => $activeSensors,
                'silent_sensors' => $silentSensors,
                'maintenance_due' => $dueMaintenance,
            ];
        });
    }

    /**
     * Active vs silent sensor breakdown for the doughnut chart.
     */
    public static function getSensorHealth(): array
    {
        return Cache::remember(self::KEY_SENSOR_HEALTH, self::TTL, function () {
            $cutoff = now()->subMinutes(10);

            $active = Sensor::whereHas('measurements', function ($query) use ($cutoff) {
                $query->where('timestamp', '>=', $cutoff);
            })->count();

            $silent = Sensor::whereDoesntHave('measurements', function ($query) use ($cutoff) {
                $query->where('timestamp', '>=', $cutoff);
            })->count();

            return ['active' => $active, 'silent' => $silent];
        });
    }

    public static function getLocationFill(): array
    {
        return Cache::remember(self::KEY_LOCATION_FILL, self::TTL, function () {
            $locations = Location::orderBy('name')->get();
            $binTypes  = ['Organic', 'Anorganic', 'B3'];
            $colors    = [
                'Organic'   => 'rgba(16, 185, 129, 0.8)',
                'Anorganic' => 'rgba(59, 130, 246, 0.8)',
                'B3'        => 'rgba(245, 158, 11, 0.8)',
            ];

            $datasets = [];
            foreach ($binTypes as $type) {
                $data = [];
                foreach ($locations as $location) {
                    $fill = Measurement::whereHas('sensor', function ($q) use ($location, $type) {
                        $q->where('type', 'Ultrasonic')
                          ->whereHas('bin', fn ($q2) => $q2
                              ->where('location_id', $location->location_id)
                              ->where('type', $type));
                    })
                        ->where('unit', '%')
                        ->latest('timestamp')
                        ->value('value');

                    $data[] = round($fill ?? 0, 1);
                }

                $datasets[] = [
                    'label'           => $type,
                    'data'            => $data,
                    'backgroundColor' => $colors[$type],
                    'borderColor'     => $colors[$type],
                    'borderWidth'     => 1,
                ];
            }

            return [
                'labels'   => $locations->pluck('name')->toArray(),
                'datasets' => $datasets,
            ];
        });
    }

    /**
     * Transmission success rate (last hour).
     */
    public static function getTransmissionRate(): ?int
    {
        return Cache::remember(self::KEY_TRANSMISSION_RATE, self::TTL, function () {
            $transmissionWindow = now()->subHour();

            $total = DataTransmission::where('timestamp', '>=', $transmissionWindow)->count();
            $successful = DataTransmission::where('timestamp', '>=', $transmissionWindow)
                ->where('successful', true)
                ->count();

            return $total > 0 ? (int) round(($successful / $total) * 100) : null;
        });
    }

    /**
     * Flush all dashboard caches. Call after new data arrives.
     */
    public static function bust(): void
    {
        Cache::forget(self::KEY_STATS);
        Cache::forget(self::KEY_SENSOR_HEALTH);
        Cache::forget(self::KEY_LOCATION_FILL);
        Cache::forget(self::KEY_TRANSMISSION_RATE);
    }
}
