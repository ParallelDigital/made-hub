<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FitnessClass extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'duration',
        'max_spots',
        'price',
        'instructor_id',
        'start_time',
        'end_time',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
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
