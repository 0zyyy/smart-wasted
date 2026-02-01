<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bin extends Model
{
    use HasFactory;

    protected $primaryKey = 'bin_id';

    protected $fillable = [
        'location_id',
        'type',
        'capacity',
        'last_emptied',
    ];

    protected $casts = [
        'last_emptied' => 'datetime',
        'capacity' => 'float',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'bin_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'bin_id');
    }

    public function analysisResults(): HasMany
    {
        return $this->hasMany(AnalysisResult::class, 'bin_id');
    }
}
