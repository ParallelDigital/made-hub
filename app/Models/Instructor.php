<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'bio',
        'specialties',
        'photo',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function fitnessClasses()
    {
        return $this->hasMany(FitnessClass::class);
    }
}
