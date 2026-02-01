<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataTransmission extends Model
{
    use HasFactory;

    protected $primaryKey = 'transmission_id';

    protected $fillable = [
        'sensor_id',
        'timestamp',
        'successful',
        'error_message',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'successful' => 'boolean',
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}
