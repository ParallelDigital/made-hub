<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'class_credits',
        'unlimited',
        'active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'unlimited' => 'boolean',
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_memberships')
                    ->withPivot('start_date', 'end_date', 'status')
                    ->withTimestamps();
    }

    public function getDurationTextAttribute()
    {
        if ($this->duration_days >= 365) {
            $years = floor($this->duration_days / 365);
            return $years . ' year' . ($years > 1 ? 's' : '');
        } elseif ($this->duration_days >= 30) {
            $months = floor($this->duration_days / 30);
            return $months . ' month' . ($months > 1 ? 's' : '');
        } else {
            return $this->duration_days . ' day' . ($this->duration_days > 1 ? 's' : '');
        }
    }
}
