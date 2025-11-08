<?php

namespace App\Models;
use App\Models\UserPass;
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
        'phone',
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
            // Automatically verify email for all new users (email verification disabled)
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
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

    public function passes(): HasMany
    {
        return $this->hasMany(UserPass::class);
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
        try {
            return $this->passes()
                ->where('pass_type', 'credits')
                ->where('expires_at', '>=', now()->toDateString())
                ->sum('credits');
        } catch (\Exception $e) {
            // Fallback if user_passes table doesn't exist
            return (int) ($this->credits ?? 0);
        }
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
        // Prioritize membership credits
        if ($this->hasActiveMembership()) {
            $this->refreshMonthlyCreditsIfNeeded();
            if ($this->monthly_credits > 0) {
                $this->decrement('monthly_credits');
                return true;
            }
        }

        // If no membership credits, try to use credits from passes
        $creditPass = $this->passes()
            ->where('pass_type', 'credits')
            ->where('expires_at', '>=', now()->toDateString())
            ->where('credits', '>', 0)
            ->orderBy('expires_at', 'asc') // Use the one that expires soonest
            ->first();

        if ($creditPass) {
            $creditPass->decrement('credits');
            return true;
        }

        return false;
    }

    /**
     * Determine if user has an active unlimited pass
     */
    public function hasActiveUnlimitedPass(): bool
    {
        try {
            return $this->passes()
                ->where('pass_type', 'unlimited')
                ->where('expires_at', '>=', now()->toDateString())
                ->exists();
        } catch (\Exception $e) {
            // Fallback to old system if user_passes table doesn't exist
            return false;
        }
    }

    /**
     * Allocate N credits to a non-member with a given expiry date (1 month passes, etc.)
     * If the user has an existing later expiry, keep the later one.
     */
    public function allocateCreditsWithExpiry(int $amount, \Carbon\CarbonInterface $expiresAt, string $source = 'admin_grant'): void
    {
        try {
            $this->passes()->create([
                'pass_type' => 'credits',
                'credits' => $amount,
                'expires_at' => $expiresAt,
                'source' => $source,
            ]);
        } catch (\Exception $e) {
            // Fallback to old system if user_passes table doesn't exist
            $this->credits = ($this->credits ?? 0) + $amount;
            $this->credits_expires_at = $expiresAt;
            $this->save();
        }
    }

    /**
     * Activate an unlimited pass until the given expiry date
     */
    public function activateUnlimitedPass(\Carbon\CarbonInterface $expiresAt, string $source = 'admin_grant'): void
    {
        try {
            $this->passes()->create([
                'pass_type' => 'unlimited',
                'expires_at' => $expiresAt,
                'source' => $source,
            ]);
        } catch (\Exception $e) {
            // Fallback to old system if user_passes table doesn't exist
            $this->unlimited_pass_expires_at = $expiresAt;
            $this->save();
        }
    }

    /**
     * Check if user has an active Stripe subscription
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing'], true);
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
