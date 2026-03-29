<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'ground_name' => $this->turf->name ?? 'N/A',
            'date' => \Carbon\Carbon::parse($this->start_time)->format('d-m-Y'),
            'time' => \Carbon\Carbon::parse($this->start_time)->format('H:i'),
            'price' => $this->total_price,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
        ];
    }
}
