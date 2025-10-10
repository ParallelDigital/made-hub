<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'fitness_class_id',
        'booking_date',
        'status',
        'booked_at',
        'booking_type',
        'amount_paid',
        'stripe_session_id',
        'attended',
        'checked_in_at',
        'checked_in_by',
        'cancelled_at'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booked_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'attended' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fitnessClass()
    {
        return $this->belongsTo(FitnessClass::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
