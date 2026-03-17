<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_activities', function (Blueprint $table): void {
            $table->id('alert_activity_id');
            $table->foreignId('alert_id')->constrained('alerts', 'alert_id')->cascadeOnDelete();
            $table->string('action', 32);
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_activities');
    }
};

