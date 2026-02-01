<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sensor extends Model
{
    use HasFactory;

    protected $primaryKey = 'sensor_id';

    protected $fillable = [
        'bin_id',
        'type',
        'model',
        'last_maintenance',
    ];

    protected $casts = [
        'last_maintenance' => 'datetime',
    ];

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'bin_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class, 'sensor_id');
    }

    public function latestMeasurement(): HasOne
    {
        return $this->hasOne(Measurement::class, 'sensor_id')->latest('timestamp');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'sensor_id');
    }

    public function dataTransmissions(): HasMany
    {
        return $this->hasMany(DataTransmission::class, 'sensor_id');
    }
}
