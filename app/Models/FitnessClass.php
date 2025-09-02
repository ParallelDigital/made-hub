<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FitnessClass extends Model
{
    protected $fillable = [
        'name',
        'description',
        'class_date',
        'instructor_id',
        'max_spots',
        'price',
        'start_time',
        'end_time',
        'active',
        'recurring',
        'recurring_weekly',
        'recurring_days'
    ];

    protected $casts = [
        'active' => 'boolean',
        'recurring' => 'boolean',
        'price' => 'decimal:2',
        'recurring_weekly' => 'boolean',
        'class_date' => 'date',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
