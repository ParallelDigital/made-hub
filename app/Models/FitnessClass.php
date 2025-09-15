<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FitnessClass extends Model
{
    protected $fillable = [
        'name',
        'description',
        'class_type_id',
        'class_date',
        'instructor_id',
        'max_spots',
        'price',
        'start_time',
        'end_time',
        'active',
        'recurring',
        'recurring_weekly',
        'recurring_days',
        'recurring_frequency',
        'recurring_until',
        'parent_class_id',
        'location'
    ];

    protected $casts = [
        'active' => 'boolean',
        'recurring' => 'boolean',
        'price' => 'decimal:2',
        'recurring_weekly' => 'boolean',
        'class_date' => 'date',
        'recurring_until' => 'date',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
    
    public function classType()
    {
        return $this->belongsTo(ClassType::class, 'class_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get available spots for this class
     */
    public function getAvailableSpotsAttribute()
    {
        $currentBookings = $this->bookings()->count();
        return max(0, $this->max_spots - $currentBookings);
    }

    /**
     * Check if class is full
     */
    public function isFull()
    {
        return $this->available_spots <= 0;
    }

    public function parentClass()
    {
        return $this->belongsTo(FitnessClass::class, 'parent_class_id');
    }

    public function childClasses()
    {
        return $this->hasMany(FitnessClass::class, 'parent_class_id');
    }

    public function isRecurring()
    {
        return $this->recurring_frequency !== 'none';
    }

    public function isChildClass()
    {
        return !is_null($this->parent_class_id);
    }
}
