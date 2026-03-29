<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurfSlot extends Model
{
    protected $fillable = [
        'turf_id',
        'start_time',
        'end_time',
        'price',
        'sport_type',
    ];

    public function turf()
    {
        return $this->belongsTo(Turf::class);
    }
}
