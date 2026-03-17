<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table): void {
            $table->index(['sensor_id', 'timestamp'], 'measurements_sensor_id_timestamp_index');
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table): void {
            $table->dropIndex('measurements_sensor_id_timestamp_index');
        });
    }
};
