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
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'car'            => new CarResource($this->car), // ensure car relation is loaded
            'customer_name'  => $this->customer_name,
            'customer_email' => $this->customer_email,
            'start_date'     => $this->start_date->toDateString(),
            'end_date'       => $this->end_date->toDateString(),
            'total_price'    => number_format((float) $this->total_price, 2, '.', ''), // 2 decimals
            'status'         => $this->status,
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
