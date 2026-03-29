<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'city' => $this->city,
            'venue_name' => $this->venue_name,
            'venue_address' => $this->venue_address,
            'balance' => round($this->balance, 2),
            'bookings_count' => $this->role === 'owner' ? $this->ownerBookings()->count() : $this->bookings()->count(),
            'created_at' => $this->created_at ? $this->created_at->format('d M Y') : null,
        ];
    }
}
