<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\AnalysisResult;
use App\Models\Bin;
use App\Models\CollectionSchedule;
use App\Models\DataTransmission;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Database\Seeder;

class SmartWasteSeeder extends Seeder
{
    public function run(): void
    {
        // Create Locations (only allowed: BB102, BB202, AA107, AA108)
        $locations = [
            ['name' => 'BB102', 'address' => 'Building B, Room 102', 'latitude' => -6.2088, 'longitude' => 106.8456],
            ['name' => 'BB202', 'address' => 'Building B, Room 202', 'latitude' => -6.2150, 'longitude' => 106.8200],
            ['name' => 'AA107', 'address' => 'Building A, Room 107', 'latitude' => -6.2250, 'longitude' => 106.8100],
            ['name' => 'AA108', 'address' => 'Building A, Room 108', 'latitude' => -6.1900, 'longitude' => 106.8350],
        ];

        foreach ($locations as $data) {
            Location::create($data);
        }

        $this->command->info('Created 4 locations.');

        // Create Bins (exactly 3 per location: Organic, Anorganic, B3)
        $binTypes = ['Organic', 'Anorganic', 'B3'];
        $bins = [];

        foreach (Location::all() as $location) {
            foreach ($binTypes as $type) {
                $bins[] = Bin::create([
                    'location_id' => $location->location_id,
                    'type' => $type,
                    'capacity' => rand(50, 200),
                    'last_emptied' => now()->subDays(rand(0, 7)),
                ]);
            }
        }

        $this->command->info('Created ' . count($bins) . ' bins (3 per location).');

        // Create Sensors (2 types only: Loadcell and Ultrasonic per bin)
        $sensorTypes = [
            ['type' => 'Loadcell', 'model' => 'HX711'],
            ['type' => 'Ultrasonic', 'model' => 'HC-SR04'],
        ];
        $sensors = [];

        foreach ($bins as $bin) {
            foreach ($sensorTypes as $sensorData) {
                $sensors[] = Sensor::create([
                    'bin_id' => $bin->bin_id,
                    'type' => $sensorData['type'],
                    'model' => $sensorData['model'],
                    'last_maintenance' => rand(0, 1) ? now()->subDays(rand(30, 180)) : null,
                ]);
            }
        }

        $this->command->info('Created ' . count($sensors) . ' sensors (2 per bin).');

        // Create Measurements (10-30 per sensor)
        $measurementCount = 0;
        foreach ($sensors as $sensor) {
            $numMeasurements = rand(10, 30);
            for ($i = 0; $i < $numMeasurements; $i++) {
                // Loadcell measures weight in kg, Ultrasonic measures fill level in %
                $unit = match($sensor->type) {
                    'Ultrasonic' => '%',
                    'Loadcell' => 'g',
                    default => 'units',
                };

                $value = match($sensor->type) {
                    'Ultrasonic' => rand(10, 95),  // Fill percentage 10-95%
                    'Loadcell' => rand(1, 50),     // Weight 1-50 g
                    default => rand(1, 100),
                };

                Measurement::create([
                    'sensor_id' => $sensor->sensor_id,
                    'timestamp' => now()->subMinutes(rand(1, 1440)),
                    'value' => $value + (rand(0, 100) / 100),
                    'unit' => $unit,
                ]);
                $measurementCount++;
            }
        }

        $this->command->info("Created {$measurementCount} measurements.");

        // Create Alerts (5-10 per location)
        $alertTypes = ['Overflow', 'Sensor Failure', 'Low Battery', 'Tampering', 'High Temperature'];
        $alertCount = 0;

        foreach ($bins as $bin) {
            if (rand(0, 2) === 0) { // ~33% chance of having alerts
                $numAlerts = rand(1, 3);
                for ($i = 0; $i < $numAlerts; $i++) {
                    Alert::create([
                        'bin_id' => $bin->bin_id,
                        'timestamp' => now()->subHours(rand(1, 72)),
                        'type' => $alertTypes[array_rand($alertTypes)],
                        'description' => 'Auto-generated alert for testing purposes.',
                        'is_resolved' => rand(0, 1),
                    ]);
                    $alertCount++;
                }
            }
        }

        $this->command->info("Created {$alertCount} alerts.");

        // Create Collection Schedules
        foreach (Location::all() as $location) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                CollectionSchedule::create([
                    'location_id' => $location->location_id,
                    'planned_time' => now()->addDays(rand(1, 14))->setTime(rand(6, 18), 0),
                    'collector_name' => 'Crew ' . chr(65 + rand(0, 5)),
                ]);
            }
        }

        $this->command->info('Created collection schedules.');

        // Create Maintenance Logs
        foreach (array_slice($sensors, 0, 10) as $sensor) {
            MaintenanceLog::create([
                'sensor_id' => $sensor->sensor_id,
                'maintenance_date' => now()->subDays(rand(1, 90)),
                'technician_name' => 'Tech ' . rand(1, 5),
                'action_taken' => collect(['Replaced battery', 'Cleaned sensor', 'Calibrated device', 'Firmware update', 'Full replacement'])->random(),
            ]);
        }

        $this->command->info('Created maintenance logs.');

        // Create Data Transmissions
        foreach (array_slice($sensors, 0, 20) as $sensor) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $successful = rand(0, 9) > 0; // 90% success rate
                DataTransmission::create([
                    'sensor_id' => $sensor->sensor_id,
                    'timestamp' => now()->subMinutes(rand(1, 120)),
                    'successful' => $successful,
                    'error_message' => $successful ? null : 'Connection timeout',
                ]);
            }
        }

        $this->command->info('Created data transmissions.');

        // Create Analysis Results
        $analysisTypes = ['Fill Prediction', 'Anomaly Detection', 'Usage Pattern', 'Trend Analysis'];
        foreach (array_slice($bins, 0, 10) as $bin) {
            AnalysisResult::create([
                'bin_id' => $bin->bin_id,
                'timestamp' => now()->subHours(rand(1, 48)),
                'analysis_type' => $analysisTypes[array_rand($analysisTypes)],
                'result_data' => json_encode([
                    'confidence' => rand(70, 99) . '%',
                    'next_collection' => now()->addDays(rand(1, 5))->format('Y-m-d'),
                    'fill_level' => rand(30, 95) . '%',
                ]),
            ]);
        }

        $this->command->info('Created analysis results.');
        $this->command->info('Smart Waste seeding complete!');
    }
}
