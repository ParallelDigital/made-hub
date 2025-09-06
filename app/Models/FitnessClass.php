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
        'recurring_days',
        'recurring_frequency',
        'recurring_until',
        'parent_class_id'
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

    public function bookings()
    {
        return $this->hasMany(Booking::class);
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
