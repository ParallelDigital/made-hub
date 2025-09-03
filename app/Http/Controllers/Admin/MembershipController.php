<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get users with subscription data
        $users = \App\Models\User::select('id', 'name', 'email', 'stripe_customer_id', 'stripe_subscription_id', 'subscription_status', 'subscription_expires_at', 'created_at')
            ->get()
            ->map(function($user) {
                // Calculate months active (dummy data for now)
                $monthsActive = now()->diffInMonths($user->created_at);
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'months_active' => $monthsActive,
                    'subscription_status' => $user->subscription_status ?? 'inactive',
                    'subscription_expires_at' => $user->subscription_expires_at,
                    'stripe_customer_id' => $user->stripe_customer_id,
                    'stripe_subscription_id' => $user->stripe_subscription_id,
                ];
            });

        // Stripe subscriptions (all)
        $stripeMembers = collect();
        $stripeTotalFetched = 0;

        try {
            $secret = $this->stripeSecret();
            if ($secret) {
                $stripe = new \Stripe\StripeClient($secret);

                // List all subscriptions (paginated)
                $allSubs = collect();
                $params = [
                    'limit' => 100,
                    'expand' => ['data.customer', 'data.items.data.price.product'],
                ];
                do {
                    $resp = $stripe->subscriptions->all($params);
                    $data = collect($resp->data ?? []);
                    $allSubs = $allSubs->merge($data);
                    if (($resp->has_more ?? false) && $data->isNotEmpty()) {
                        $params['starting_after'] = $data->last()->id;
                    } else {
                        unset($params['starting_after']);
                        break;
                    }
                } while (true);

                $stripeTotalFetched = $allSubs->count();

                // Map to simplified rows
                $stripeMembers = $allSubs->map(function($sub) {
                    $customer = $sub->customer; // expanded
                    $name = is_object($customer) ? ($customer->name ?? null) : null;
                    $email = is_object($customer) ? ($customer->email ?? null) : null;
                    $startTs = $sub->start_date ?? $sub->current_period_start ?? null;
                    $monthsActive = $startTs ? \Carbon\Carbon::createFromTimestamp($startTs)->diffInMonths(now()) : null;
                    return [
                        'name' => $name ?: '—',
                        'email' => $email ?: '—',
                        'months_active' => $monthsActive ?? 0,
                        'status' => $sub->status ?? 'unknown',
                        'subscription_id' => $sub->id,
                        'customer_id' => is_object($customer) ? ($customer->id ?? null) : (is_string($customer) ? $customer : null),
                    ];
                });
            }
        } catch (\Throwable $e) {
            // Leave $stripeMembers empty on error; optionally log for debugging
            \Log::warning('Stripe membership fetch failed: '.$e->getMessage());
        }

        return view('admin.memberships.index', compact('users', 'stripeMembers', 'stripeTotalFetched'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.memberships.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'class_credits' => 'nullable|integer|min:0',
            'unlimited' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['unlimited'] = $request->has('unlimited');
        $validated['active'] = $request->has('active');

        Membership::create($validated);

        return redirect()->route('admin.memberships.index')
                        ->with('success', 'Membership created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Membership $membership)
    {
        return view('admin.memberships.show', compact('membership'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Membership $membership)
    {
        return view('admin.memberships.edit', compact('membership'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'class_credits' => 'nullable|integer|min:0',
            'unlimited' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['unlimited'] = $request->has('unlimited');
        $validated['active'] = $request->has('active');

        $membership->update($validated);

        return redirect()->route('admin.memberships.index')
                        ->with('success', 'Membership updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Membership $membership)
    {
        $membership->delete();

        return redirect()->route('admin.memberships.index')
                        ->with('success', 'Membership deleted successfully.');
    }

    // Helpers
    private function stripeSecret(): ?string
    {
        $secret = config('services.stripe.secret') ?? env('STRIPE_SECRET');
        $secret = is_string($secret) ? trim($secret) : null;
        return $secret ?: null;
    }
}
