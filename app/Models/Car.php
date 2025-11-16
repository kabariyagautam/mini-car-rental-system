<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ['supplier_id','name','type','location','price_per_day','image','approved'];

    // relationship with User model (supplier)
    public function supplier()
    {
        return $this->belongsTo(User::class,'supplier_id');
    }

    // relationship with Booking model
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // relationship with CarAvailability model
    public function availabilities()
    {
        return $this->hasMany(CarAvailability::class);
    }

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        return $this->image ? asset("storage/{$this->image}") : null;
    }
}
