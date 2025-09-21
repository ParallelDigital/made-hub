<?php

namespace App\Models;
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
        'pin_code',
        'membership_id',
        'monthly_credits',
        'credits_last_refreshed',
        'membership_start_date',
        'membership_end_date',
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
            'credits_last_refreshed' => 'date',
            'membership_start_date' => 'date',
            'membership_end_date' => 'date',
            'credits_expires_at' => 'date',
            'unlimited_pass_expires_at' => 'date',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->qr_code)) {
                $user->qr_code = self::generateUniqueQrCode();
            }
            if (empty($user->pin_code)) {
                $user->pin_code = self::generateUniquePinCode();
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
     * Generate a unique 4-digit PIN code for the user
     */
    public static function generateUniquePinCode(): string
    {
        do {
            $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('pin_code', $pin)->exists());

        return $pin;
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

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Check if user has an active membership
     */
    public function hasActiveMembership(): bool
    {
        return $this->membership_id && 
               $this->membership_start_date && 
               $this->membership_start_date <= now() &&
               (!$this->membership_end_date || $this->membership_end_date >= now());
    }

    /**
     * Get available credits for the user
     */
    public function getAvailableCredits(): int
    {
        if (!$this->hasActiveMembership()) {
            // Display 0 on dashboard when no active membership
            // (legacy credits may still be used during booking logic if applicable)
            return 0;
        }

        // Check if credits need to be refreshed
        $this->refreshMonthlyCreditsIfNeeded();
        
        return $this->monthly_credits;
    }

    /**
     * Get non-member available credits considering expiry
     */
    public function getNonMemberAvailableCredits(): int
    {
        if (($this->credits_expires_at && $this->credits_expires_at->isFuture()) || ($this->credits_expires_at && $this->credits_expires_at->isToday())) {
            return (int) ($this->credits ?? 0);
        }
        return 0;
    }

    /**
     * Refresh monthly credits if needed (on 1st of month)
     */
    public function refreshMonthlyCreditsIfNeeded(): void
    {
        if (!$this->hasActiveMembership() || !$this->membership) {
            return;
        }

        // If user is on a Stripe subscription, rely on Stripe webhooks to top up credits
        if (!empty($this->stripe_subscription_id)) {
            return;
        }

        $firstOfMonth = now()->startOfMonth()->toDateString();
        
        // If credits haven't been refreshed this month, refresh them
        if (!$this->credits_last_refreshed || $this->credits_last_refreshed < $firstOfMonth) {
            // Only assign credits if membership explicitly defines class_credits; otherwise 0
            $this->monthly_credits = $this->membership->class_credits ?? 0;
            $this->credits_last_refreshed = $firstOfMonth;
            $this->save();
        }
    }

    /**
     * Use a credit for booking
     */
    public function useCredit(): bool
    {
        if (!$this->hasActiveMembership()) {
            // Fallback to old credits system
            // Respect credit expiry window
            $hasValidWindow = $this->credits_expires_at && ($this->credits_expires_at->isFuture() || $this->credits_expires_at->isToday());
            if ($hasValidWindow && ($this->credits > 0)) {
                $this->decrement('credits');
                return true;
            }
            return false;
        }

        $this->refreshMonthlyCreditsIfNeeded();
        
        if ($this->monthly_credits > 0) {
            $this->decrement('monthly_credits');
            return true;
        }
        
        return false;
    }

    /**
     * Determine if user has an active unlimited pass
     */
    public function hasActiveUnlimitedPass(): bool
    {
        return (bool) ($this->unlimited_pass_expires_at && ($this->unlimited_pass_expires_at->isFuture() || $this->unlimited_pass_expires_at->isToday()));
    }

    /**
     * Allocate N credits to a non-member with a given expiry date (1 month passes, etc.)
     * If the user has an existing later expiry, keep the later one.
     */
    public function allocateCreditsWithExpiry(int $amount, \Carbon\CarbonInterface $expiresAt): void
    {
        $this->credits = (int) ($this->credits ?? 0) + max(0, $amount);
        if ($this->credits_expires_at) {
            $this->credits_expires_at = $this->credits_expires_at->greaterThan($expiresAt) ? $this->credits_expires_at : $expiresAt->toDateString();
        } else {
            $this->credits_expires_at = $expiresAt->toDateString();
        }
        $this->save();
    }

    /**
     * Activate an unlimited pass until the given expiry date
     */
    public function activateUnlimitedPass(\Carbon\CarbonInterface $expiresAt): void
    {
        $this->unlimited_pass_expires_at = $expiresAt->toDateString();
        $this->save();
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
