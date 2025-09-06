<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_status',
        'subscription_expires_at',
        'user_login',
        'first_name',
        'last_name',
        'nickname',
        'display_name',
        'role',
        'qr_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
            'user_registered' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->qr_code)) {
                $user->qr_code = self::generateUniqueQrCode();
            }
        });
    }

    /**
     * Generate a unique QR code for the user
     */
    public static function generateUniqueQrCode(): string
    {
        do {
            $qrCode = 'QR' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Get subscription status from Stripe
     */
    public function instructor()
    {
        return $this->hasOne(Instructor::class, 'email', 'email');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get subscription status from Stripe
     */
    public function getStripeSubscriptionStatus()
    {
        if (!$this->stripe_subscription_id) {
            return ['active' => false, 'status' => 'no_subscription'];
        }

        // This would integrate with Stripe API
        // Example: $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        // $subscription = $stripe->subscriptions->retrieve($this->stripe_subscription_id);
        
        return [
            'active' => $this->hasActiveSubscription(),
            'status' => $this->subscription_status,
            'expires_at' => $this->subscription_expires_at
        ];
    }
}
