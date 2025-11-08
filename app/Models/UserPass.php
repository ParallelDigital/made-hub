<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPass extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pass_type',
        'credits',
        'expires_at',
        'source',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: only active passes
     * - Unlimited: expires_at >= today
     * - Credits: credits > 0 AND expires_at >= today
     */
    public function scopeActive($query)
    {
        return $query->whereDate('expires_at', '>=', now()->toDateString())
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('pass_type', 'credits')
                       ->where('credits', '>', 0);
                })->orWhere('pass_type', 'unlimited');
            });
    }
}
