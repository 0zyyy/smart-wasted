<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_transmissions', function (Blueprint $table) {
            $table->id('transmission_id');
            $table->foreignId('sensor_id')->constrained('sensors', 'sensor_id');
            $table->dateTime('timestamp');
            $table->boolean('successful')->default(true);
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_transmissions');
    }
};
