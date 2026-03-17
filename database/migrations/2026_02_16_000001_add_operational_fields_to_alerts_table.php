<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table): void {
            $table->string('status', 32)->default('open')->after('description');
            $table->string('severity', 32)->default('warning')->after('status');
            $table->foreignId('assigned_to')->nullable()->after('severity')->constrained('users')->nullOnDelete();
            $table->foreignId('acknowledged_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable()->after('acknowledged_by');
            $table->timestamp('resolved_at')->nullable()->after('acknowledged_at');
            $table->text('resolution_note')->nullable()->after('resolved_at');
            $table->timestamp('last_seen_at')->nullable()->after('resolution_note');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('acknowledged_by');
            $table->dropColumn([
                'status',
                'severity',
                'acknowledged_at',
                'resolved_at',
                'resolution_note',
                'last_seen_at',
            ]);
        });
    }
};

