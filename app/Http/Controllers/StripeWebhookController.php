<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\MembershipStatusMail;
use App\Mail\ClassPassConfirmed;
use App\Mail\AdminClassPassNotification;
use Stripe\Stripe;
use Stripe\Webhook as StripeWebhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret') ?? env('STRIPE_WEBHOOK_SECRET');

        try {
            if ($secret) {
                $event = StripeWebhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $secret
                );
            } else {
                // Fallback: parse without signature verification (not recommended)
                $event = json_decode($payload, false); 
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'invalid'], 400);
        }

        $type = $event->type ?? ($event->type ?? '');
        $dataObject = $event->data->object ?? null;

        try {
            switch ($type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($dataObject);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($dataObject);
                    break;
                case 'customer.subscription.deleted':
                case 'customer.subscription.canceled':
                    $this->handleSubscriptionCanceled($dataObject);
                    break;
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($dataObject);
                    break;
                default:
                    // ignore others
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler error', ['type' => $type, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleCheckoutSessionCompleted($session): void
    {
        // Handle both subscription (membership) and payment (class passes) modes
        if (!isset($session->mode) || !in_array($session->mode, ['subscription', 'payment'])) {
            return;
        }

        if ($session->mode === 'subscription') {
            $this->handleMembershipPurchase($session);
        } elseif ($session->mode === 'payment') {
            $this->handleClassPassPurchase($session);
        }
    }

    protected function handleMembershipPurchase($session): void
    {

        $email = $session->customer_details->email ?? $session->customer_email ?? null;
        if (!$email) return;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $session->metadata->name ?? 'Member',
                'password' => bcrypt('temporary_password_' . time()),
            ]
        );

        // If user was created with temporary password, send password reset email
        if ($user->wasRecentlyCreated) {
            try {
                \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
            } catch (\Throwable $e) {
                Log::warning('Failed to send password reset for webhook-created user', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        // Ensure we have a default membership (5 classes per calendar month)
        $membership = Membership::firstOrCreate(
            ['name' => 'MEMBERSHIP'],
            [
                'description' => 'Monthly membership with 5 class credits per month',
                'price' => 30.00,
                'duration_days' => 30,
                'class_credits' => 5,
                'unlimited' => false,
                'active' => true,
            ]
        );

        // Mark user as member
        $user->membership_id = $membership->id;
        $user->membership_start_date = now()->toDateString();
        $user->membership_end_date = null; // open-ended until canceled

        // Link Stripe IDs for future updates
        if (!empty($session->customer)) {
            $user->stripe_customer_id = $session->customer;
        }
        if (!empty($session->subscription)) {
            $user->stripe_subscription_id = $session->subscription;
            $user->subscription_status = 'active';
        }

        // Grant immediate monthly credits (calendar month model)
        $user->monthly_credits = 5;
        $user->credits_last_refreshed = now()->startOfMonth()->toDateString();
        $user->save();

        Log::info('Membership activated via checkout.session.completed', ['user_id' => $user->id]);

        // Send activation email
        try {
            Mail::to($user->email)->send(new MembershipStatusMail($user, 'activation'));
        } catch (\Throwable $e) {
            Log::warning('Failed to send membership activation email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    protected function handleClassPassPurchase($session): void
    {
        $email = $session->customer_details->email ?? $session->customer_email ?? null;
        if (!$email) return;

        // Get package type from metadata
        $packageType = $session->metadata->package_type ?? null;
        if (!$packageType) {
            Log::warning('Class pass purchase without package_type', ['session_id' => $session->id]);
            return;
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $session->metadata->name ?? 'Guest',
                'password' => bcrypt('Made2025!'),
                'role' => 'subscriber',
                'email_verified_at' => now(),
            ]
        );

        // Track if this is a new account
        $isNewAccount = $user->wasRecentlyCreated;
        $password = $isNewAccount ? 'Made2025!' : null;

        // Check if user is a member
        $isMember = $user->hasActiveMembership();

        // Allocate the pass based on package type
        $expiresAt = now()->addMonth();
        try {
            switch ($packageType) {
                case 'package_5':
                    $user->allocateCreditsWithExpiry(5, $expiresAt, 'stripe_purchase');
                    Log::info('5 class pass allocated via webhook', ['user_id' => $user->id]);
                    // Send confirmation email to user
                    try { Mail::to($user->email)->send(new ClassPassConfirmed($user, 'credits', 5, $expiresAt, 'Stripe Purchase', $isNewAccount, $password, $isMember)); } catch (\Throwable $e) { Log::warning('Failed to send class pass email', ['user_id' => $user->id, 'error' => $e->getMessage()]); }
                    // Send notification to all admin users
                    $this->notifyAdminsOfClassPassPurchase($user, 'credits', 5, $expiresAt, 'Stripe Purchase');
                    break;
                case 'package_10':
                    $user->allocateCreditsWithExpiry(10, $expiresAt, 'stripe_purchase');
                    Log::info('10 class pass allocated via webhook', ['user_id' => $user->id]);
                    try { Mail::to($user->email)->send(new ClassPassConfirmed($user, 'credits', 10, $expiresAt, 'Stripe Purchase', $isNewAccount, $password, $isMember)); } catch (\Throwable $e) { Log::warning('Failed to send class pass email', ['user_id' => $user->id, 'error' => $e->getMessage()]); }
                    $this->notifyAdminsOfClassPassPurchase($user, 'credits', 10, $expiresAt, 'Stripe Purchase');
                    break;
                case 'unlimited':
                    $user->activateUnlimitedPass($expiresAt, 'stripe_purchase');
                    Log::info('Unlimited pass allocated via webhook', ['user_id' => $user->id]);
                    try { Mail::to($user->email)->send(new ClassPassConfirmed($user, 'unlimited', null, $expiresAt, 'Stripe Purchase', $isNewAccount, $password, $isMember)); } catch (\Throwable $e) { Log::warning('Failed to send class pass email', ['user_id' => $user->id, 'error' => $e->getMessage()]); }
                    $this->notifyAdminsOfClassPassPurchase($user, 'unlimited', null, $expiresAt, 'Stripe Purchase');
                    break;
                default:
                    Log::warning('Unknown package type in webhook', ['package_type' => $packageType, 'session_id' => $session->id]);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Failed to allocate class pass via webhook', [
                'user_id' => $user->id,
                'package_type' => $packageType,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function handleInvoicePaymentSucceeded($invoice): void
    {
        // Optional: we could realign credits to billing cycle. For now, rely on 1st-of-month command.
        // Ensure the user is marked active and grant monthly credits on each successful invoice.
        $customerId = $invoice->customer ?? null;
        if (!$customerId) return;

        $user = User::where('stripe_customer_id', $customerId)->first();
        if (!$user) {
            // Log this case - we have an invoice for a customer that doesn't exist in our system
            Log::warning('Invoice payment succeeded for unknown customer', ['customer_id' => $customerId]);
            return;
        }

        // Ensure the user has a membership if they have an active subscription
        $membership = null;
        if ($user->stripe_subscription_id && !$user->membership_id) {
            $membership = Membership::firstOrCreate(
                ['name' => 'MEMBERSHIP'],
                [
                    'description' => 'Monthly membership with 5 class credits per month',
                    'price' => 30.00,
                    'duration_days' => 30,
                    'class_credits' => 5,
                    'unlimited' => false,
                    'active' => true,
                ]
            );
            $user->membership_id = $membership->id;
            $user->membership_start_date = $user->membership_start_date ?: now()->toDateString();
            $user->membership_end_date = null;
        }

        // Get membership for credit calculation
        if (!$membership) {
            $membership = Membership::find($user->membership_id);
        }

        if (!$user->hasActiveMembership()) {
            if (!$membership) {
                $membership = Membership::firstOrCreate(
                    ['name' => 'MEMBERSHIP'],
                    [
                        'description' => 'Monthly membership with 5 class credits per month',
                        'price' => 30.00,
                        'duration_days' => 30,
                        'class_credits' => 5,
                        'unlimited' => false,
                        'active' => true,
                    ]
                );
            }
            $user->membership_id = $membership->id;
            $user->membership_start_date = $user->membership_start_date ?: now()->toDateString();
            $user->membership_end_date = null;
        }

        $user->subscription_status = 'active';
        // Top-up/Reset monthly credits to membership allowance (5)
        $allowance = $membership->class_credits ?? 5;
        $user->monthly_credits = $allowance;
        $user->credits_last_refreshed = now()->toDateString();
        $user->save();

        Log::info('Invoice payment succeeded processed; monthly credits set', ['user_id' => $user->id, 'monthly_credits' => $user->monthly_credits]);

        // Send renewal email
        try {
            Mail::to($user->email)->send(new MembershipStatusMail($user, 'renewal'));
        } catch (\Throwable $e) {
            Log::warning('Failed to send membership renewal email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    protected function handleSubscriptionCanceled($subscription): void
    {
        $customerId = $subscription->customer ?? null;
        if (!$customerId) return;
        $user = User::where('stripe_customer_id', $customerId)->first();
        if (!$user) return;

        $user->subscription_status = 'canceled';
        $user->membership_end_date = now()->toDateString();
        $user->save();

        Log::info('Membership canceled via Stripe', ['user_id' => $user->id]);
    }

    protected function handleSubscriptionUpdated($subscription): void
    {
        $customerId = $subscription->customer ?? null;
        if (!$customerId) return;
        $user = User::where('stripe_customer_id', $customerId)->first();
        if (!$user) return;

        $status = $subscription->status ?? null;
        if ($status === 'active') {
            $user->subscription_status = 'active';
            if (!$user->hasActiveMembership()) {
                $membership = Membership::firstOrCreate(
                    ['name' => 'MEMBERSHIP'],
                    [
                        'description' => 'Monthly membership with 5 class credits per month',
                        'price' => 30.00,
                        'duration_days' => 30,
                        'class_credits' => 5,
                        'unlimited' => false,
                        'active' => true,
                    ]
                );
                $user->membership_id = $membership->id;
                $user->membership_start_date = now()->toDateString();
                $user->membership_end_date = null;
            }
        } elseif (in_array($status, ['canceled', 'unpaid', 'past_due', 'incomplete_expired'], true)) {
            $user->subscription_status = $status;
            $user->membership_end_date = now()->toDateString();
        }

        $user->save();
        Log::info('Subscription updated processed', ['user_id' => $user->id, 'status' => $status]);
    }

    /**
     * Notify all admin users of a new class pass purchase
     */
    protected function notifyAdminsOfClassPassPurchase(User $user, string $passType, ?int $credits, ?\Carbon\CarbonInterface $expiresAt, string $source): void
    {
        try {
            // Get all admin users
            $adminUsers = User::whereIn('role', ['admin', 'administrator'])->get();

            if ($adminUsers->isEmpty()) {
                Log::warning('No admin users found to notify of class pass purchase');
                return;
            }

            // Send notification email to each admin
            foreach ($adminUsers as $admin) {
                try {
                    Mail::to($admin->email)->send(new AdminClassPassNotification($user, $passType, $credits, $expiresAt, $source));
                } catch (\Throwable $e) {
                    Log::warning('Failed to send admin notification for class pass purchase', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Admin notifications sent for class pass purchase', [
                'user_id' => $user->id,
                'pass_type' => $passType,
                'admin_count' => $adminUsers->count()
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send admin notifications for class pass purchase', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
