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

        return view('admin.memberships.index', compact('users'));
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
}
