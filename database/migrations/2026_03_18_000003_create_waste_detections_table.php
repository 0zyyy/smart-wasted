<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_detections', function (Blueprint $table): void {
            $table->id('detection_id');
            $table->foreignId('location_id')->constrained('locations', 'location_id');
            $table->string('detected_class');     // Organic | Anorganic | B3
            $table->float('confidence');          // 0.0 – 1.0
            $table->dateTime('timestamp');
            $table->string('device_id', 50)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'timestamp']);
            $table->index('detected_class');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_detections');
    }
};
