<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'car_id', 'customer_name', 'customer_email',
        'start_date', 'end_date', 'total_price', 'status'
    ];

    // castsss
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // relationship with Car model
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
