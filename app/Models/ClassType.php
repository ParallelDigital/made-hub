<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration',
        'color',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration' => 'integer'
    ];

    public function fitnessClasses(): HasMany
    {
        return $this->hasMany(FitnessClass::class);
    }
}
