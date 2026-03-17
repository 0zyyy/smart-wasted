<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            $table->index('name', 'locations_name_idx');
        });

        Schema::table('bins', function (Blueprint $table): void {
            $table->index(['location_id', 'type'], 'bins_location_type_idx');
        });

        Schema::table('sensors', function (Blueprint $table): void {
            $table->index(['bin_id', 'type'], 'sensors_bin_type_idx');
            $table->index('last_maintenance', 'sensors_last_maintenance_idx');
        });

        Schema::table('measurements', function (Blueprint $table): void {
            $table->index('timestamp', 'measurements_timestamp_idx');
            $table->index(['sensor_id', 'timestamp'], 'measurements_sensor_timestamp_idx');
            $table->index(['sensor_id', 'unit', 'value', 'timestamp'], 'measurements_dedup_idx');
        });

        Schema::table('alerts', function (Blueprint $table): void {
            $table->index(['status', 'severity', 'timestamp'], 'alerts_status_severity_time_idx');
            $table->index(['is_resolved', 'timestamp'], 'alerts_resolved_time_idx');
            $table->index(['bin_id', 'type', 'is_resolved'], 'alerts_bin_type_resolved_idx');
            $table->index(['assigned_to', 'status'], 'alerts_assignee_status_idx');
            $table->index('last_seen_at', 'alerts_last_seen_idx');
        });

        Schema::table('data_transmissions', function (Blueprint $table): void {
            $table->index(['timestamp', 'successful'], 'transmissions_time_success_idx');
            $table->index(['sensor_id', 'timestamp'], 'transmissions_sensor_time_idx');
        });

        Schema::table('collection_schedules', function (Blueprint $table): void {
            $table->index(['planned_time', 'location_id'], 'schedules_time_location_idx');
        });

        Schema::table('maintenance_logs', function (Blueprint $table): void {
            $table->index('maintenance_date', 'maintenance_date_idx');
            $table->index(['sensor_id', 'maintenance_date'], 'maintenance_sensor_date_idx');
        });

        Schema::table('analysis_results', function (Blueprint $table): void {
            $table->index(['bin_id', 'timestamp'], 'analysis_bin_time_idx');
        });

        Schema::table('alert_activities', function (Blueprint $table): void {
            $table->index(['alert_id', 'created_at'], 'alert_activities_alert_time_idx');
            $table->index(['actor_id', 'created_at'], 'alert_activities_actor_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('alert_activities', function (Blueprint $table): void {
            $table->dropIndex('alert_activities_alert_time_idx');
            $table->dropIndex('alert_activities_actor_time_idx');
        });

        Schema::table('analysis_results', function (Blueprint $table): void {
            $table->dropIndex('analysis_bin_time_idx');
        });

        Schema::table('maintenance_logs', function (Blueprint $table): void {
            $table->dropIndex('maintenance_date_idx');
            $table->dropIndex('maintenance_sensor_date_idx');
        });

        Schema::table('collection_schedules', function (Blueprint $table): void {
            $table->dropIndex('schedules_time_location_idx');
        });

        Schema::table('data_transmissions', function (Blueprint $table): void {
            $table->dropIndex('transmissions_time_success_idx');
            $table->dropIndex('transmissions_sensor_time_idx');
        });

        Schema::table('alerts', function (Blueprint $table): void {
            $table->dropIndex('alerts_status_severity_time_idx');
            $table->dropIndex('alerts_resolved_time_idx');
            $table->dropIndex('alerts_bin_type_resolved_idx');
            $table->dropIndex('alerts_assignee_status_idx');
            $table->dropIndex('alerts_last_seen_idx');
        });

        Schema::table('measurements', function (Blueprint $table): void {
            $table->dropIndex('measurements_timestamp_idx');
            $table->dropIndex('measurements_sensor_timestamp_idx');
            $table->dropIndex('measurements_dedup_idx');
        });

        Schema::table('sensors', function (Blueprint $table): void {
            $table->dropIndex('sensors_bin_type_idx');
            $table->dropIndex('sensors_last_maintenance_idx');
        });

        Schema::table('bins', function (Blueprint $table): void {
            $table->dropIndex('bins_location_type_idx');
        });

        Schema::table('locations', function (Blueprint $table): void {
            $table->dropIndex('locations_name_idx');
        });
    }
};

