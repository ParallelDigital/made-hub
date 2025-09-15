<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'package_type',
        'classes',
        'email',
        'expires_at',
        'redeemed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];
}
