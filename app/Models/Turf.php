<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Turf extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'sport_type',
        'description',
        'amenities',
        'images',
        'status',
        'city',
        'address',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'sport_type' => 'array',
    ];

    protected $appends = ['average_rating', 'review_count'];
    
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function slots()
    {
        return $this->hasMany(TurfSlot::class);
    }

    public function overrides()
    {
        return $this->hasMany(TurfOverride::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Automatically prepend storage URL to image paths.
     */
    protected function images(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $images = json_decode($value, true) ?: [];
                return array_map(function($img) {
                    if (str_starts_with($img, 'http')) return $img;
                    return asset('storage/' . $img);
                }, $images);
            }
        );
    }
}
