<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        $stripeError = null;
        $stripeMembers = collect();
        $totalMembers = 0;
        $monthlyRevenue = 0;
        $stripeMode = config('services.stripe.mode') ?: 'default';
        $statusFilter = $request->input('status', 'all');

        try {
            $secret = config('services.stripe.secret');
            if ($secret) {
                $stripe = new \Stripe\StripeClient($secret);

                // Fetch all subscriptions, expanding only the customer.
                $allSubs = collect();
                $params = ['limit' => 100, 'expand' => ['data.customer']];
                do {
                    $resp = $stripe->subscriptions->all($params);
                    $data = collect($resp->data ?? []);
                    $allSubs = $allSubs->merge($data);
                    if (($resp->has_more ?? false) && $data->isNotEmpty()) {
                        $params['starting_after'] = $data->last()->id;
                    } else {
                        break;
                    }
                } while (true);

                // Map and filter subscriptions
                $stripeMembers = $allSubs->map(function($sub) {
                    $status = $sub->status;
                    if ($status === 'past_due') {
                        $status = 'inactive';
                    }

                    $startTs = $sub->start_date ?? $sub->current_period_start ?? null;
                    $monthsActive = 0;
                    if ($startTs) {
                        $startDate = \Carbon\Carbon::createFromTimestamp($startTs);
                        $monthsActive = (int) $startDate->diffInMonths(now());
                        if ($monthsActive === 0) {
                            $monthsActive = 1;
                        }
                    }
                    
                    $customer = $sub->customer;
                    return [
                        'name' => is_object($customer) ? ($customer->name ?? '—') : '—',
                        'email' => is_object($customer) ? ($customer->email ?? '—') : '—',
                        'months_active' => $monthsActive,
                        'status' => $status,
                        'subscription_id' => $sub->id,
                    ];
                });

                // Calculate metrics on the complete, unfiltered list of members
                $stripeMembersBeforeFilter = $stripeMembers;
                $activeMembers = $stripeMembers->filter(function ($member) {
                    return $member['status'] === 'active' || $member['status'] === 'trialing';
                });
                $activeMembersCount = $activeMembers->count();
                $totalMembers = $stripeMembersBeforeFilter->count();
                $monthlyRevenue = $activeMembersCount * 30;

                // Now, filter the list for display based on the user's selection
                if ($statusFilter !== 'all') {
                    $stripeMembers = $stripeMembers->where('status', $statusFilter);
                }
            }
        } catch (\Throwable $e) {
            $stripeError = $e->getMessage();
            \Log::warning('Stripe membership fetch failed: '.$e->getMessage());
        }

        $statusOptions = [
            'all' => 'All Statuses',
            'active' => 'Active',
            'trialing' => 'Trialing',
            'inactive' => 'Inactive',
            'canceled' => 'Canceled',
        ];

        // Paginate the results
        $perPage = $request->input('per_page', 20);
        $currentPage = $request->input('page', 1);
        $paginatedItems = $stripeMembers->slice(($currentPage - 1) * $perPage, $perPage);
        $stripeMembers = new LengthAwarePaginator(
            $paginatedItems,
            $stripeMembers->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.memberships.index', compact(
            'users', 'stripeMembers', 'totalMembers', 'monthlyRevenue', 'activeMembersCount',
            'stripeMode', 'stripeError', 'statusFilter', 'statusOptions', 'perPage'
        ));
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

    /**
     * Export Stripe members as CSV applying the same status filter as the index.
     */
    public function export(Request $request)
    {
        $statusFilter = $request->input('status', 'all');
        $filename = 'memberships_export_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ];

        $callback = function () use ($statusFilter) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, ['Name', 'Email', 'Months Active', 'Status', 'Subscription ID']);

            try {
                $secret = config('services.stripe.secret');
                if ($secret) {
                    $stripe = new \Stripe\StripeClient($secret);
                    $params = ['limit' => 100, 'expand' => ['data.customer']];
                    do {
                        $resp = $stripe->subscriptions->all($params);
                        $data = collect($resp->data ?? []);

                        foreach ($data as $sub) {
                            $status = $sub->status;
                            if ($status === 'past_due') {
                                $status = 'inactive';
                            }
                            if ($statusFilter !== 'all' && $status !== $statusFilter) {
                                continue;
                            }

                            $startTs = $sub->start_date ?? $sub->current_period_start ?? null;
                            $monthsActive = 0;
                            if ($startTs) {
                                $startDate = \Carbon\Carbon::createFromTimestamp($startTs);
                                $monthsActive = (int) $startDate->diffInMonths(now());
                                if ($monthsActive === 0) {
                                    $monthsActive = 1;
                                }
                            }

                            $customer = $sub->customer;
                            $name = is_object($customer) ? ($customer->name ?? '—') : '—';
                            $email = is_object($customer) ? ($customer->email ?? '—') : '—';

                            fputcsv($handle, [
                                $name,
                                $email,
                                $monthsActive,
                                $status,
                                $sub->id,
                            ]);
                        }

                        if (($resp->has_more ?? false) && $data->isNotEmpty()) {
                            $params['starting_after'] = $data->last()->id;
                        } else {
                            break;
                        }
                    } while (true);
                }
            } catch (\Throwable $e) {
                // Write an error row for visibility
                fputcsv($handle, ['Error', $e->getMessage(), '', '', '']);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
