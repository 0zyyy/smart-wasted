<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteDetection extends Model
{
    protected $primaryKey = 'detection_id';

    protected $fillable = [
        'location_id',
        'detected_class',
        'confidence',
        'timestamp',
        'device_id',
        'latency_ms',
    ];

    protected $casts = [
        'timestamp'  => 'datetime',
        'confidence' => 'float',
        'latency_ms' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
