<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table): void {
            $table->unsignedInteger('latency_ms')->nullable()->after('unit')
                ->comment('Pipeline latency: ms between device_timestamp and server receipt');
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table): void {
            $table->dropColumn('latency_ms');
        });
    }
};
