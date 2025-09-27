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
        $sortBy = $request->input('sort_by', 'unlimited_pass_expires_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $filter = $request->input('filter', 'all'); // all, active, expired, credits

        // Build the query
        $query = User::query();

        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Apply pass type filter
        switch ($filter) {
            case 'active_unlimited':
                $query->whereNotNull('unlimited_pass_expires_at')
                      ->where('unlimited_pass_expires_at', '>=', now()->toDateString());
                break;
            case 'expired_unlimited':
                $query->whereNotNull('unlimited_pass_expires_at')
                      ->where('unlimited_pass_expires_at', '<', now()->toDateString());
                break;
            case 'active_credits':
                $query->where('credits', '>', 0)
                      ->where(function($q) {
                          $q->whereNull('credits_expires_at')
                            ->orWhere('credits_expires_at', '>=', now()->toDateString());
                      });
                break;
            case 'expired_credits':
                $query->where('credits', '>', 0)
                      ->whereNotNull('credits_expires_at')
                      ->where('credits_expires_at', '<', now()->toDateString());
                break;
            case 'all':
            default:
                $query->where(function($q) {
                    $q->whereNotNull('unlimited_pass_expires_at')
                      ->orWhere('credits', '>', 0);
                });
                break;
        }

        // Apply sorting
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'email':
                $query->orderBy('email', $sortOrder);
                break;
            case 'credits':
                $query->orderBy('credits', $sortOrder);
                break;
            case 'credits_expires_at':
                $query->orderBy('credits_expires_at', $sortOrder);
                break;
            case 'unlimited_pass_expires_at':
            default:
                $query->orderBy('unlimited_pass_expires_at', $sortOrder);
                break;
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
            $user->activateUnlimitedPass(Carbon::parse($request->expires_at));
            $message = "Unlimited pass activated for {$user->name} until " . Carbon::parse($request->expires_at)->format('M j, Y');
        } else {
            $user->allocateCreditsWithExpiry(
                (int) $request->credits_amount,
                Carbon::parse($request->expires_at)
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
                $user->activateUnlimitedPass(Carbon::parse($request->expires_at));
                $message = "Unlimited pass extended until " . Carbon::parse($request->expires_at)->format('M j, Y');
                break;

            case 'add_credits':
                $user->allocateCreditsWithExpiry(
                    (int) $request->credits_amount,
                    Carbon::parse($request->expires_at)
                );
                $message = "{$request->credits_amount} credits added, expiring " . Carbon::parse($request->expires_at)->format('M j, Y');
                break;

            case 'expire_unlimited':
                $user->unlimited_pass_expires_at = now()->subDay();
                $user->save();
                $message = "Unlimited pass expired";
                break;

            case 'expire_credits':
                $user->credits_expires_at = now()->subDay();
                $user->save();
                $message = "Credits expired";
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
        // Reset both unlimited pass and credits
        $user->unlimited_pass_expires_at = null;
        $user->credits = 0;
        $user->credits_expires_at = null;
        $user->save();

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
