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
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'value' => 'float',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}
