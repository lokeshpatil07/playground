<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurfOverride extends Model
{
    protected $fillable = [
        'turf_id',
        'date',
        'start_time',
        'end_time',
        'price',
        'is_blocked',
        'sport_type',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'date' => 'date',
    ];

    public function turf()
    {
        return $this->belongsTo(Turf::class);
    }
}
