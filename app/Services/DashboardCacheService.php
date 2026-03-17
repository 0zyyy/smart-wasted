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

    /**
     * Fill percentages per location for the bar chart.
     */
    public static function getLocationFill(): array
    {
        return Cache::remember(self::KEY_LOCATION_FILL, self::TTL, function () {
            $locations = Location::withCount('bins')->get();

            $labels = [];
            $fillPercentages = [];
            $colors = [];

            foreach ($locations as $location) {
                $labels[] = $location->name;

                $avgFill = Measurement::whereHas('sensor.bin', function ($query) use ($location) {
                    $query->where('location_id', $location->location_id);
                })
                    ->where('unit', 'LIKE', '%')
                    ->latest('timestamp')
                    ->limit(50)
                    ->avg('value');

                $fillPercentages[] = round($avgFill ?? 0, 1);

                if ($avgFill >= 80) {
                    $colors[] = 'rgb(239, 68, 68)';
                } elseif ($avgFill >= 60) {
                    $colors[] = 'rgb(245, 158, 11)';
                } else {
                    $colors[] = 'rgb(16, 185, 129)';
                }
            }

            return compact('labels', 'fillPercentages', 'colors');
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
