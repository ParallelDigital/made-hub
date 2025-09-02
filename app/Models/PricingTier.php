<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PricingTier extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'base_price',
        'discount_percentage',
        'final_price',
        'valid_from',
        'valid_until',
        'min_quantity',
        'max_quantity',
        'conditions',
        'active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'final_price' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'conditions' => 'array',
        'active' => 'boolean',
    ];

    public function getDiscountAmountAttribute()
    {
        return $this->base_price * ($this->discount_percentage / 100);
    }

    public function getSavingsAttribute()
    {
        return $this->base_price - $this->final_price;
    }

    public function getIsValidAttribute()
    {
        $now = Carbon::now()->startOfDay();
        
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }
        
        return $this->active;
    }

    public function getStatusTextAttribute()
    {
        if (!$this->active) {
            return 'Inactive';
        }
        
        $now = Carbon::now()->startOfDay();
        
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return 'Scheduled';
        }
        
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return 'Expired';
        }
        
        return 'Active';
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now()->startOfDay();
        
        return $query->where('active', true)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_from')
                          ->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_until')
                          ->orWhere('valid_until', '>=', $now);
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
