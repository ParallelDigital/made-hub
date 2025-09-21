<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
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
        // Only act for subscriptions (membership purchases)
        if (!isset($session->mode) || $session->mode !== 'subscription') {
            return;
        }

        $email = $session->customer_details->email ?? $session->customer_email ?? null;
        if (!$email) return;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $session->metadata->name ?? 'Member',
                'password' => bcrypt('temporary_password_' . time()),
            ]
        );

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
    }

    protected function handleInvoicePaymentSucceeded($invoice): void
    {
        // Optional: we could realign credits to billing cycle. For now, rely on 1st-of-month command.
        // Ensure the user is marked active and grant monthly credits on each successful invoice.
        $customerId = $invoice->customer ?? null;
        if (!$customerId) return;

        $user = User::where('stripe_customer_id', $customerId)->first();
        if (!$user) return;

        // Keep membership active if recurring invoice succeeded
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

        if (!$user->hasActiveMembership()) {
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
}
