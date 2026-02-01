<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'location_id';

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
    ];

    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class, 'location_id');
    }

    public function collectionSchedules(): HasMany
    {
        return $this->hasMany(CollectionSchedule::class, 'location_id');
    }
}
