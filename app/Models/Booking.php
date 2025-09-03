<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'fitness_class_id',
        'status',
        'booked_at',
        'booking_type',
        'amount_paid'
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fitnessClass()
    {
        return $this->belongsTo(FitnessClass::class);
    }
}
