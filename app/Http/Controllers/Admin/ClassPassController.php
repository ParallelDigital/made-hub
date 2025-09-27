<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClassPassController extends Controller
{
    /**
     * Display a listing of users with class passes.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'expires_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $filter = $request->input('filter', 'all');

        $query = User::query()->whereHas('passes');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $query->with(['passes' => function ($q) {
            $q->orderBy('expires_at', 'desc');
        }]);

        $query->whereHas('passes', function ($q) use ($filter) {
            switch ($filter) {
                case 'active_unlimited':
                    $q->where('pass_type', 'unlimited')->where('expires_at', '>=', now()->toDateString());
                    break;
                case 'expired_unlimited':
                    $q->where('pass_type', 'unlimited')->where('expires_at', '<', now()->toDateString());
                    break;
                case 'active_credits':
                    $q->where('pass_type', 'credits')->where('credits', '>', 0)->where('expires_at', '>=', now()->toDateString());
                    break;
                case 'expired_credits':
                    $q->where('pass_type', 'credits')->where('expires_at', '<', now()->toDateString());
                    break;
            }
        });

        if ($sortBy === 'name' || $sortBy === 'email') {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Sorting by pass attributes is more complex and may require subqueries or joins.
            // For now, we sort by user name as a fallback.
            $query->orderBy('name', 'asc');
        }

        $users = $query->paginate(20)->appends($request->query());

        return view('admin.class-passes.index', compact('users', 'search', 'sortBy', 'sortOrder', 'filter'));
    }

    /**
     * Show the form for creating a new class pass.
     */
    public function create()
    {
        return view('admin.class-passes.create');
    }

    /**
     * Store a newly created class pass.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'pass_type' => 'required|in:unlimited,credits',
            'expires_at' => 'required|date|after:today',
            'credits_amount' => 'required_if:pass_type,credits|integer|min:1|max:100',
        ]);

        $user = User::where('email', $request->user_email)->firstOrFail();

        if ($request->pass_type === 'unlimited') {
            $user->activateUnlimitedPass(Carbon::parse($request->expires_at), 'admin_grant');
            $message = "Unlimited pass activated for {$user->name} until " . Carbon::parse($request->expires_at)->format('M j, Y');
        } else {
            $user->allocateCreditsWithExpiry(
                (int) $request->credits_amount,
                Carbon::parse($request->expires_at),
                'admin_grant'
            );
            $message = "{$request->credits_amount} credits allocated to {$user->name} until " . Carbon::parse($request->expires_at)->format('M j, Y');
        }

        return redirect()->route('admin.class-passes.index')
            ->with('success', $message);
    }

    /**
     * Display the specified user's class pass details.
     */
    public function show(User $user)
    {
        return view('admin.class-passes.show', compact('user'));
    }

    /**
     * Show the form for editing the specified class pass.
     */
    public function edit(User $user)
    {
        return view('admin.class-passes.edit', compact('user'));
    }

    /**
     * Update the specified class pass.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'action' => 'required|in:extend_unlimited,add_credits,expire_unlimited,expire_credits',
            'expires_at' => 'required_if:action,extend_unlimited,add_credits|date|after:today',
            'credits_amount' => 'required_if:action,add_credits|integer|min:1|max:100',
        ]);

        switch ($request->action) {
            case 'extend_unlimited':
                $user->activateUnlimitedPass(Carbon::parse($request->expires_at), 'admin_grant');
                $message = "Unlimited pass extended until " . Carbon::parse($request->expires_at)->format('M j, Y');
                break;

            case 'add_credits':
                $user->allocateCreditsWithExpiry(
                    (int) $request->credits_amount,
                    Carbon::parse($request->expires_at),
                    'admin_grant'
                );
                $message = "{$request->credits_amount} credits added, expiring " . Carbon::parse($request->expires_at)->format('M j, Y');
                break;

            case 'expire_unlimited':
                $user->passes()->where('pass_type', 'unlimited')->update(['expires_at' => now()->subDay()]);
                $message = "Unlimited passes expired";
                break;

            case 'expire_credits':
                $user->passes()->where('pass_type', 'credits')->update(['expires_at' => now()->subDay()]);
                $message = "All credit passes expired";
                break;
        }

        return redirect()->route('admin.class-passes.show', $user)
            ->with('success', $message);
    }

    /**
     * Remove the specified class pass.
     */
    public function destroy(User $user)
    {
        $user->passes()->delete();

        return redirect()->route('admin.class-passes.index')
            ->with('success', "All class passes removed for {$user->name}");
    }

    /**
     * Get user suggestions for autocomplete
     */
    public function getUserSuggestions(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->limit(10)
                    ->get(['id', 'name', 'email']);

        return response()->json($users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'display' => $user->name . ' (' . $user->email . ')'
            ];
        }));
    }
}
