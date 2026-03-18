<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    use HasFactory;

    protected $primaryKey = 'measurement_id';

    protected $fillable = [
        'sensor_id',
        'timestamp',
        'value',
        'unit',
        'latency_ms',
    ];

    protected $casts = [
        'timestamp'  => 'datetime',
        'value'      => 'float',
        'latency_ms' => 'integer',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}
