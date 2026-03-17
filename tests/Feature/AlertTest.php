<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\AlertActivity;
use App\Models\Bin;
use App\Models\Location;
use App\Services\DashboardCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AlertTest extends TestCase
{
    use RefreshDatabase;

    private function makeAlert(array $attrs = []): Alert
    {
        $location = Location::create(['name' => 'BB102', 'address' => 'Test']);
        $bin = Bin::create(['location_id' => $location->location_id, 'type' => 'Organic', 'capacity' => 100]);

        return Alert::create(array_merge([
            'bin_id' => $bin->bin_id,
            'timestamp' => now(),
            'type' => 'Overflow',
            'description' => 'Test alert',
            'status' => Alert::STATUS_OPEN,
            'severity' => Alert::SEVERITY_CRITICAL,
            'is_resolved' => false,
        ], $attrs));
    }

    // ── State transitions ────────────────────────────────────────────────────

    public function test_alert_starts_as_open(): void
    {
        $alert = $this->makeAlert();

        $this->assertEquals(Alert::STATUS_OPEN, $alert->status);
        $this->assertFalse($alert->is_resolved);
        $this->assertNull($alert->resolved_at);
    }

    public function test_alert_transitions_to_acknowledged(): void
    {
        $alert = $this->makeAlert();

        $alert->update(['status' => Alert::STATUS_ACKNOWLEDGED]);

        $this->assertEquals(Alert::STATUS_ACKNOWLEDGED, $alert->fresh()->status);
        $this->assertFalse($alert->fresh()->is_resolved);
    }

    public function test_alert_transitions_to_resolved(): void
    {
        $alert = $this->makeAlert();

        $alert->update(['status' => Alert::STATUS_RESOLVED]);
        $alert->refresh();

        $this->assertEquals(Alert::STATUS_RESOLVED, $alert->status);
        $this->assertTrue($alert->is_resolved);
        $this->assertNotNull($alert->resolved_at);
    }

    public function test_resolving_sets_resolved_at_timestamp(): void
    {
        $alert = $this->makeAlert();

        $before = now();
        $alert->update(['status' => Alert::STATUS_RESOLVED]);

        $this->assertGreaterThanOrEqual($before, $alert->fresh()->resolved_at);
    }

    public function test_reopening_resolved_alert_clears_resolved_fields(): void
    {
        $alert = $this->makeAlert();
        $alert->update(['status' => Alert::STATUS_RESOLVED]);

        $alert->update(['status' => Alert::STATUS_OPEN]);
        $alert->refresh();

        $this->assertEquals(Alert::STATUS_OPEN, $alert->status);
        $this->assertFalse($alert->is_resolved);
        $this->assertNull($alert->resolved_at);
    }

    public function test_acknowledging_clears_resolved_fields(): void
    {
        $alert = $this->makeAlert();
        $alert->update(['status' => Alert::STATUS_RESOLVED]);

        $alert->update(['status' => Alert::STATUS_ACKNOWLEDGED]);
        $alert->refresh();

        $this->assertFalse($alert->is_resolved);
        $this->assertNull($alert->resolved_at);
    }

    // ── AlertActivity logging ────────────────────────────────────────────────

    public function test_log_activity_creates_record(): void
    {
        $alert = $this->makeAlert();

        $alert->logActivity('opened', 'Alert opened by test.');

        $this->assertDatabaseHas('alert_activities', [
            'alert_id' => $alert->alert_id,
            'action' => 'opened',
            'note' => 'Alert opened by test.',
        ]);
    }

    public function test_log_activity_stores_meta(): void
    {
        $alert = $this->makeAlert();

        $alert->logActivity('acknowledged', 'Checked on-site.', null, ['channel' => 'dashboard']);

        $activity = AlertActivity::where('alert_id', $alert->alert_id)->first();

        $this->assertNotNull($activity);
        $this->assertEquals(['channel' => 'dashboard'], $activity->meta);
    }

    public function test_alert_has_activities_relationship(): void
    {
        $alert = $this->makeAlert();
        $alert->logActivity('opened', 'First.');
        $alert->logActivity('acknowledged', 'Second.');

        $this->assertCount(2, $alert->activities);
    }

    // ── Dashboard cache busting ──────────────────────────────────────────────

    public function test_bust_clears_all_dashboard_cache_keys(): void
    {
        Cache::put('sw:dash:stats', ['test' => 1], 60);
        Cache::put('sw:dash:sensor_health', ['test' => 1], 60);
        Cache::put('sw:dash:location_fill', ['test' => 1], 60);
        Cache::put('sw:dash:transmission_rate', 100, 60);

        DashboardCacheService::bust();

        $this->assertNull(Cache::get('sw:dash:stats'));
        $this->assertNull(Cache::get('sw:dash:sensor_health'));
        $this->assertNull(Cache::get('sw:dash:location_fill'));
        $this->assertNull(Cache::get('sw:dash:transmission_rate'));
    }

    public function test_get_stats_populates_cache(): void
    {
        DashboardCacheService::bust();
        $this->assertNull(Cache::get('sw:dash:stats'));

        DashboardCacheService::getStats();

        $this->assertNotNull(Cache::get('sw:dash:stats'));
    }
}
