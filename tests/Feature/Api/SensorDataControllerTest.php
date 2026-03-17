<?php

namespace Tests\Feature\Api;

use App\Models\Alert;
use App\Models\Bin;
use App\Models\Location;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorDataControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accepts_get_payload_and_creates_measurements(): void
    {
        $this->createBinWithSensors('BB102', 'Organic');

        $response = $this->getJson('/api/sensor-data?lokasi=organik&berat=1250&volume=75&device=BB102');

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'OK',
                'location' => 'BB102',
                'bin_type' => 'Organic',
                'deduplicated' => false,
            ]);

        $this->assertSame(2, Measurement::count());
        $this->assertDatabaseHas('measurements', ['unit' => 'g', 'value' => 1250.0]);
        $this->assertDatabaseHas('measurements', ['unit' => '%', 'value' => 75.0]);
    }

    public function test_it_accepts_post_payload_and_creates_measurements(): void
    {
        $this->createBinWithSensors('AA107', 'B3');

        $response = $this->postJson('/api/sensor-data', [
            'bin_type' => 'b3',
            'weight' => 300,
            'volume' => 40,
            'location' => 'aa107',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'OK',
                'location' => 'AA107',
                'bin_type' => 'B3',
                'deduplicated' => false,
            ]);

        $this->assertSame(2, Measurement::count());
    }

    public function test_it_deduplicates_retried_payload_within_window(): void
    {
        $this->createBinWithSensors('BB202', 'Anorganic');

        $first = $this->postJson('/api/sensor-data', [
            'bin_type' => 'anorganik',
            'weight' => 200,
            'volume' => 60,
            'location' => 'BB202',
        ]);

        $second = $this->postJson('/api/sensor-data', [
            'bin_type' => 'anorganik',
            'weight' => 200,
            'volume' => 60,
            'location' => 'BB202',
        ]);

        $first->assertStatus(201)->assertJson(['deduplicated' => false]);
        $second->assertStatus(200)->assertJson(['deduplicated' => true]);
        $this->assertSame(2, Measurement::count());

        $firstIds = collect($first->json('measurements'))->pluck('measurement_id')->values()->all();
        $secondIds = collect($second->json('measurements'))->pluck('measurement_id')->values()->all();

        $this->assertSame($firstIds, $secondIds);
    }

    public function test_it_creates_only_one_open_overflow_alert_for_repeated_high_volume(): void
    {
        $this->createBinWithSensors('AA108', 'Organic');

        $this->postJson('/api/sensor-data', [
            'bin_type' => 'organik',
            'weight' => 150,
            'volume' => 85,
            'location' => 'AA108',
        ])->assertStatus(201);

        $this->postJson('/api/sensor-data', [
            'bin_type' => 'organik',
            'weight' => 150,
            'volume' => 85,
            'location' => 'AA108',
        ])->assertStatus(200);

        $this->assertSame(1, Alert::where('type', 'Overflow')->where('is_resolved', false)->count());
    }

    public function test_it_returns_validation_error_for_invalid_location(): void
    {
        $this->createBinWithSensors('BB102', 'Organic');

        $response = $this->postJson('/api/sensor-data', [
            'bin_type' => 'organik',
            'weight' => 100,
            'volume' => 20,
            'location' => 'ZZ999',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ])
            ->assertJsonStructure(['message', 'errors']);
    }

    private function createBinWithSensors(string $locationName, string $binType): void
    {
        $location = Location::create([
            'name' => $locationName,
            'address' => 'Test location',
        ]);

        $bin = Bin::create([
            'location_id' => $location->location_id,
            'type' => $binType,
            'capacity' => 100,
        ]);

        Sensor::create([
            'bin_id' => $bin->bin_id,
            'type' => 'Loadcell',
            'model' => 'HX711',
        ]);

        Sensor::create([
            'bin_id' => $bin->bin_id,
            'type' => 'Ultrasonic',
            'model' => 'HC-SR04',
        ]);
    }
}

