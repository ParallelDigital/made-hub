<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
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

    /**
     * Get the URL to the instructor's photo.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return 'https://www.gravatar.com/avatar/?d=mp&s=200';
        }

        // Check if the photo is already a full URL
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }

        // Check if the photo exists in storage
        if (strpos($this->photo, 'http') !== 0 && file_exists(storage_path('app/public/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }

        // Fallback to the stored path (in case it's a full URL from another source)
        return $this->photo;
    }
}
