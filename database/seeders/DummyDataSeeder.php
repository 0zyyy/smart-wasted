<?php

namespace Database\Seeders;

use App\Models\Bin;
use App\Models\DataTransmission;
use App\Models\WasteDetection;
use App\Models\Location;
use App\Models\Measurement;
use App\Models\Sensor;
use App\Models\Alert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generates 7 days of realistic sensor measurements with:
 * - Gradual fill level increases (bin-type-specific rates)
 * - Automatic "collection" resets when bins hit ~85-90%
 * - Correlated weight measurements
 * - Realistic pipeline latency_ms values
 * - Overflow alerts for un-collected bins
 */
class DummyDataSeeder extends Seeder
{
    // Fill rate per hour per bin type (% per hour during active hours)
    private const FILL_RATES = [
        'Organic'   => ['min' => 3.0, 'max' => 7.0],
        'Anorganic' => ['min' => 1.5, 'max' => 3.5],
        'B3'        => ['min' => 0.5, 'max' => 1.5],
    ];

    // Weight per fill % per bin type (grams per %)
    private const WEIGHT_PER_FILL = [
        'Organic'   => 18.0,  // heaviest
        'Anorganic' => 8.0,
        'B3'        => 5.0,   // lightest
    ];

    // Latency profile: mostly fast, occasional spikes
    private const LATENCY_PROFILE = [
        ['weight' => 60, 'min' => 50,   'max' => 150],   // fast (50-150ms)
        ['weight' => 25, 'min' => 150,  'max' => 400],   // normal (150-400ms)
        ['weight' => 10, 'min' => 400,  'max' => 1200],  // slow (400ms-1.2s)
        ['weight' => 5,  'min' => 1200, 'max' => 3500],  // spike (1.2-3.5s)
    ];

    public function run(): void
    {
        $this->command->info('Generating 7 days of dummy sensor data...');

        // Clear existing data (keep locations/bins/sensors)
        DB::table('measurements')->delete();
        DB::table('alerts')->delete();
        DB::table('data_transmissions')->delete();
        DB::table('waste_detections')->delete();

        $interval   = 30;   // minutes between readings
        $days       = 7;
        $start      = now()->subDays($days)->startOfHour();
        $end        = now();
        $totalSteps = (int) $start->diffInMinutes($end) / $interval;

        $bins = Bin::with(['sensors', 'location'])->get();

        $totalMeasurements   = 0;
        $totalCollections    = 0;
        $totalAlerts         = 0;
        $totalTransmissions  = 0;
        $transmissionsBatch  = [];

        foreach ($bins as $bin) {
            $ultrasonic = $bin->sensors->firstWhere('type', 'Ultrasonic');
            $loadcell   = $bin->sensors->firstWhere('type', 'Loadcell');

            if (!$ultrasonic || !$loadcell) {
                continue;
            }

            $fillRate  = $this->randomBetween(
                self::FILL_RATES[$bin->type]['min'],
                self::FILL_RATES[$bin->type]['max']
            );
            $fillLevel = $this->randomBetween(5, 25); // starting fill
            $alertFired = false;

            $measurementsBatch = [];

            for ($step = 0; $step <= $totalSteps; $step++) {
                $time = $start->copy()->addMinutes($step * $interval);

                // Active hours boost (7am–10pm = full rate, night = 20%)
                $hour        = (int) $time->format('H');
                $isActive    = $hour >= 7 && $hour <= 22;
                $rateMultiplier = $isActive ? 1.0 : 0.15;

                // Increment fill level
                $increment = ($fillRate / 60) * $interval * $rateMultiplier;
                $increment += $this->randomBetween(-0.3, 0.3); // sensor noise
                $fillLevel  = max(0, min(100, $fillLevel + $increment));

                // Collection: reset when bin reaches 85-92%
                if ($fillLevel >= $this->randomBetween(85, 92)) {
                    $fillLevel      = $this->randomBetween(5, 15);
                    $alertFired     = false;
                    $totalCollections++;
                    // Re-randomize fill rate slightly after collection
                    $fillRate = $this->randomBetween(
                        self::FILL_RATES[$bin->type]['min'],
                        self::FILL_RATES[$bin->type]['max']
                    );
                }

                // Weight correlates with fill level + noise
                $weight = ($fillLevel * self::WEIGHT_PER_FILL[$bin->type])
                    + $this->randomBetween(-30, 30);
                $weight = max(0, round($weight, 1));

                $latencyMs = $this->generateLatency();

                // Fill measurement
                $measurementsBatch[] = [
                    'sensor_id'  => $ultrasonic->sensor_id,
                    'timestamp'  => $time,
                    'value'      => round($fillLevel, 1),
                    'unit'       => '%',
                    'latency_ms' => $latencyMs,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Weight measurement (slightly different latency)
                $measurementsBatch[] = [
                    'sensor_id'  => $loadcell->sensor_id,
                    'timestamp'  => $time,
                    'value'      => $weight,
                    'unit'       => 'g',
                    'latency_ms' => $this->generateLatency(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Overflow alert if fill ≥ 80% and no active alert yet
                if (!$alertFired && $fillLevel >= 80 && $time->lte($end)) {
                    Alert::create([
                        'bin_id'      => $bin->bin_id,
                        'timestamp'   => $time,
                        'type'        => 'Overflow',
                        'description' => "Bin {$bin->type} at {$bin->location->name} is " . round($fillLevel, 1) . "% full (PENUH)",
                        'status'      => $time->gt(now()->subHours(6)) ? Alert::STATUS_OPEN : Alert::STATUS_RESOLVED,
                        'severity'    => Alert::SEVERITY_CRITICAL,
                        'is_resolved' => $time->lte(now()->subHours(6)),
                        'last_seen_at' => $time,
                    ]);
                    $alertFired = true;
                    $totalAlerts++;
                }

                // Log transmission per sensor (96% success, 4% failure)
                foreach ([$ultrasonic->sensor_id, $loadcell->sensor_id] as $sensorId) {
                    $successful = mt_rand(1, 100) <= 96;
                    $transmissionsBatch[] = [
                        'sensor_id'     => $sensorId,
                        'timestamp'     => $time,
                        'successful'    => $successful,
                        'error_message' => $successful ? null : collect(['Connection timeout', 'Network unreachable', 'HTTP 503'])->random(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }

                // Flush in batches of 500
                if (count($measurementsBatch) >= 500) {
                    DB::table('measurements')->insert($measurementsBatch);
                    $totalMeasurements += count($measurementsBatch);
                    $measurementsBatch  = [];
                }

                if (count($transmissionsBatch) >= 500) {
                    DB::table('data_transmissions')->insert($transmissionsBatch);
                    $totalTransmissions += count($transmissionsBatch);
                    $transmissionsBatch  = [];
                }
            }

            // Flush remainder
            if (!empty($measurementsBatch)) {
                DB::table('measurements')->insert($measurementsBatch);
                $totalMeasurements += count($measurementsBatch);
                $measurementsBatch  = [];
            }

            if (!empty($transmissionsBatch)) {
                DB::table('data_transmissions')->insert($transmissionsBatch);
                $totalTransmissions += count($transmissionsBatch);
                $transmissionsBatch  = [];
            }
        }

        // Generate YOLOv8 detection records
        // ~8-20 detections per hour per location during active hours
        $locations        = \App\Models\Location::all();
        $detectionClasses = ['Organic', 'Anorganic', 'B3'];
        // Organic is most common (~60%), Anorganic ~30%, B3 ~10%
        $classWeights     = ['Organic' => 60, 'Anorganic' => 30, 'B3' => 10];
        $detectionsBatch  = [];
        $totalDetections  = 0;

        foreach ($locations as $location) {
            $current = $start->copy();
            while ($current->lte($end)) {
                $hour      = (int) $current->format('H');
                $isActive  = $hour >= 7 && $hour <= 22;
                $perHour   = $isActive ? mt_rand(8, 20) : mt_rand(0, 2);

                for ($i = 0; $i < $perHour; $i++) {
                    // Pick class by weight
                    $rand  = mt_rand(1, 100);
                    $cumul = 0;
                    $class = 'Organic';
                    foreach ($classWeights as $c => $w) {
                        $cumul += $w;
                        if ($rand <= $cumul) { $class = $c; break; }
                    }

                    $detectionsBatch[] = [
                        'location_id'    => $location->location_id,
                        'detected_class' => $class,
                        'confidence'     => round($this->randomBetween(0.72, 0.99), 3),
                        'timestamp'      => $current->copy()->addMinutes(mt_rand(0, 59)),
                        'device_id'      => 'CAM-' . $location->name,
                        'latency_ms'     => $this->generateLatency(),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                if (count($detectionsBatch) >= 500) {
                    DB::table('waste_detections')->insert($detectionsBatch);
                    $totalDetections += count($detectionsBatch);
                    $detectionsBatch  = [];
                }

                $current->addHour();
            }
        }

        if (!empty($detectionsBatch)) {
            DB::table('waste_detections')->insert($detectionsBatch);
            $totalDetections += count($detectionsBatch);
        }

        $this->command->info("✓ {$totalDetections} YOLOv8 detection records created");

        $this->command->info("✓ {$totalMeasurements} measurements created");
        $this->command->info("✓ {$totalTransmissions} transmission records logged (~96% success rate)");
        $this->command->info("✓ {$totalCollections} simulated collections");
        $this->command->info("✓ {$totalAlerts} overflow alerts generated");
        $this->command->info('Dummy data seeding complete!');
    }

    private function generateLatency(): int
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;

        foreach (self::LATENCY_PROFILE as $bucket) {
            $cumulative += $bucket['weight'];
            if ($rand <= $cumulative) {
                return (int) $this->randomBetween($bucket['min'], $bucket['max']);
            }
        }

        return 200;
    }

    private function randomBetween(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}
