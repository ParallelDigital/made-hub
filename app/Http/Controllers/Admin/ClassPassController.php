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
        $filter = $request->input('filter', 'active'); // Changed default from 'all' to 'active'

        try {
            $query = User::query();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $query->with(['passes' => function ($q) {
                $q->orderBy('expires_at', 'desc');
            }]);
            
            // Detect if we can rely on a status column
            $hasStatus = \Illuminate\Support\Facades\Schema::hasTable('user_passes') && \Illuminate\Support\Facades\Schema::hasColumn('user_passes', 'status');
            if ($filter === 'active') {
                // For active filter, prefer status-based filtering if available
                if ($hasStatus) {
                    $query->whereHas('passes', function ($subQ) {
                        $subQ->where('status', 'active');
                    });
                } else {
                    // Fallback: Align EXACTLY with the "Active Pass / Credits" display rule
                    // Active if: Unlimited (unexpired) OR Credits > 0 (unexpired)
                    $query->whereHas('passes', function ($subQ) {
                        $subQ->where(function ($passQ) {
                            $passQ->where('pass_type', 'unlimited')
                                  ->where('expires_at', '>=', now()->toDateString());
                        })->orWhere(function ($creditQ) {
                            $creditQ->where('pass_type', 'credits')
                                    ->where('expires_at', '>=', now()->toDateString())
                                    ->where('credits', '>', 0);
                        });
                    });
                }
            } elseif ($filter === 'expired') {
                // For expired filter, prefer status-based filtering if available
                if ($hasStatus) {
                    $query->whereHas('passes') // has passes at all
                          ->whereDoesntHave('passes', function ($subQ) {
                              $subQ->where('status', 'active');
                          });
                } else {
                    // Fallback: Expired = has passes, but NONE match the active rule above
                    $query->whereHas('passes')
                          ->whereDoesntHave('passes', function ($subQ) {
                              $subQ->where(function ($passQ) {
                                  $passQ->where('pass_type', 'unlimited')
                                        ->where('expires_at', '>=', now()->toDateString());
                              })->orWhere(function ($creditQ) {
                                  $creditQ->where('pass_type', 'credits')
                                          ->where('expires_at', '>=', now()->toDateString())
                                          ->where('credits', '>', 0);
                              });
                          });
                }
            } elseif ($filter !== 'all') {
                // Handle other specific filters (active_unlimited, expired_unlimited, etc.)
                $query->where(function ($q) use ($filter) {
                    if ($filter !== 'all') {
                        $q->whereHas('passes', function ($subQ) use ($filter) {
                            switch ($filter) {
                                case 'active_unlimited':
                                    $subQ->where('pass_type', 'unlimited')->where('expires_at', '>=', now()->toDateString());
                                    break;
                                case 'expired_unlimited':
                                    $subQ->where('pass_type', 'unlimited')->where('expires_at', '<', now()->toDateString());
                                    break;
                                case 'active_credits':
                                    $subQ->where('pass_type', 'credits')->where('credits', '>', 0)->where('expires_at', '>=', now()->toDateString());
                                    break;
                                case 'expired_credits':
                                    $subQ->where('pass_type', 'credits')->where('expires_at', '<', now()->toDateString());
                                    break;
                            }
                        });
                    } else {
                        $q->whereHas('passes');
                    }
                })->orWhere(function ($q) use ($filter) {
                    switch ($filter) {
                        case 'active_unlimited':
                            $q->where('unlimited_pass_expires_at', '>=', now()->toDateString());
                            break;
                        case 'expired_unlimited':
                            $q->where('unlimited_pass_expires_at', '<', now()->toDateString());
                            break;
                        case 'active_credits':
                            $q->where('credits', '>', 0)->where(function ($dateQ) {
                                $dateQ->whereNull('credits_expires_at')->orWhere('credits_expires_at', '>=', now()->toDateString());
                            });
                            break;
                        case 'expired_credits':
                            $q->where('credits', '>', 0)->where('credits_expires_at', '<', now()->toDateString());
                            break;
                        case 'all':
                        default:
                            $q->where('credits', '>', 0)->orWhereNotNull('unlimited_pass_expires_at');
                            break;
                    }
                });
            } else {
                // For 'all' filter
                $query->where(function ($q) {
                    $q->whereHas('passes')
                      ->orWhere('credits', '>', 0)
                      ->orWhereNotNull('unlimited_pass_expires_at');
                });
            }

            if ($sortBy === 'name' || $sortBy === 'email') {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // Sorting by pass attributes is more complex and may require subqueries or joins.
                // For now, we sort by user name as a fallback.
                $query->orderBy('name', 'asc');
            }

            $users = $query->paginate(20)->appends($request->query());
        } catch (\Exception $e) {
            // If user_passes table doesn't exist, return empty collection
            $users = User::query()->whereRaw('1 = 0')->paginate(20)->appends($request->query());
        }

        return view('admin.class-passes.index', compact('users', 'search', 'sortBy', 'sortOrder', 'filter'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'expires_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $filter = $request->input('filter', 'active');

        try {
            $query = User::query();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $query->with(['passes' => function ($q) {
                $q->orderBy('expires_at', 'desc');
            }]);

            $hasStatus = \Illuminate\Support\Facades\Schema::hasTable('user_passes') && \Illuminate\Support\Facades\Schema::hasColumn('user_passes', 'status');
            if ($filter === 'active') {
                if ($hasStatus) {
                    $query->whereHas('passes', function ($subQ) {
                        $subQ->where('status', 'active');
                    });
                } else {
                    $query->whereHas('passes', function ($subQ) {
                        $subQ->where(function ($passQ) {
                            $passQ->where('pass_type', 'unlimited')
                                  ->where('expires_at', '>=', now()->toDateString());
                        })->orWhere(function ($creditQ) {
                            $creditQ->where('pass_type', 'credits')
                                    ->where('expires_at', '>=', now()->toDateString())
                                    ->where('credits', '>', 0);
                        });
                    });
                }
            } elseif ($filter === 'expired') {
                if ($hasStatus) {
                    $query->whereHas('passes')
                          ->whereDoesntHave('passes', function ($subQ) {
                              $subQ->where('status', 'active');
                          });
                } else {
                    $query->whereHas('passes')
                          ->whereDoesntHave('passes', function ($subQ) {
                              $subQ->where(function ($passQ) {
                                  $passQ->where('pass_type', 'unlimited')
                                        ->where('expires_at', '>=', now()->toDateString());
                              })->orWhere(function ($creditQ) {
                                  $creditQ->where('pass_type', 'credits')
                                          ->where('expires_at', '>=', now()->toDateString())
                                          ->where('credits', '>', 0);
                              });
                          });
                }
            } elseif ($filter !== 'all') {
                $query->where(function ($q) use ($filter) {
                    if ($filter !== 'all') {
                        $q->whereHas('passes', function ($subQ) use ($filter) {
                            switch ($filter) {
                                case 'active_unlimited':
                                    $subQ->where('pass_type', 'unlimited')->where('expires_at', '>=', now()->toDateString());
                                    break;
                                case 'expired_unlimited':
                                    $subQ->where('pass_type', 'unlimited')->where('expires_at', '<', now()->toDateString());
                                    break;
                                case 'active_credits':
                                    $subQ->where('pass_type', 'credits')->where('credits', '>', 0)->where('expires_at', '>=', now()->toDateString());
                                    break;
                                case 'expired_credits':
                                    $subQ->where('pass_type', 'credits')->where('expires_at', '<', now()->toDateString());
                                    break;
                            }
                        });
                    } else {
                        $q->whereHas('passes');
                    }
                })->orWhere(function ($q) use ($filter) {
                    switch ($filter) {
                        case 'active_unlimited':
                            $q->where('unlimited_pass_expires_at', '>=', now()->toDateString());
                            break;
                        case 'expired_unlimited':
                            $q->where('unlimited_pass_expires_at', '<', now()->toDateString());
                            break;
                        case 'active_credits':
                            $q->where('credits', '>', 0)->where(function ($dateQ) {
                                $dateQ->whereNull('credits_expires_at')->orWhere('credits_expires_at', '>=', now()->toDateString());
                            });
                            break;
                        case 'expired_credits':
                            $q->where('credits', '>', 0)->where('credits_expires_at', '<', now()->toDateString());
                            break;
                        case 'all':
                        default:
                            $q->where('credits', '>', 0)->orWhereNotNull('unlimited_pass_expires_at');
                            break;
                    }
                });
            } else {
                $query->where(function ($q) {
                    $q->whereHas('passes')
                      ->orWhere('credits', '>', 0)
                      ->orWhereNotNull('unlimited_pass_expires_at');
                });
            }

            if ($sortBy === 'name' || $sortBy === 'email') {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('name', 'asc');
            }

            $users = $query->get();
        } catch (\Exception $e) {
            $users = collect();
        }

        $filename = 'class-passes-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Active Type', 'Credits', 'Expires', 'Source', 'Status']);

            foreach ($users as $user) {
                $hasActiveUnlimited = $user->hasActiveUnlimitedPass();
                $totalCredits = $user->getNonMemberAvailableCredits();

                $activeUnlimitedPass = null;
                $firstCreditPass = null;

                try {
                    $activeUnlimitedPass = $user->passes()->where('pass_type', 'unlimited')->where('expires_at', '>=', now()->toDateString())->orderBy('expires_at', 'desc')->first();
                    $firstCreditPass = $user->passes()->where('pass_type', 'credits')->where('expires_at', '>=', now()->toDateString())->where('credits', '>', 0)->orderBy('expires_at', 'asc')->first();
                } catch (\Exception $e) {
                }

                $passSource = '';
                $passExpiry = '';
                $isActive = false;
                $activeType = 'None';

                if ($activeUnlimitedPass) {
                    $passSource = (string) $activeUnlimitedPass->source;
                    $passExpiry = optional($activeUnlimitedPass->expires_at)->format('Y-m-d');
                    $isActive = true;
                    $activeType = 'Unlimited';
                } elseif ($firstCreditPass) {
                    $passSource = (string) $firstCreditPass->source;
                    $passExpiry = optional($firstCreditPass->expires_at)->format('Y-m-d');
                    $isActive = $firstCreditPass->credits > 0;
                    $activeType = $isActive ? 'Credits' : 'None';
                } elseif ($hasActiveUnlimited) {
                    $isActive = true;
                    $activeType = 'Unlimited';
                } elseif ($totalCredits > 0) {
                    $isActive = true;
                    $activeType = 'Credits';
                }

                fputcsv($out, [
                    $user->name,
                    $user->email,
                    $activeType,
                    (int) $totalCredits,
                    $passExpiry ?: '',
                    $passSource ?: '',
                    $isActive ? 'Active' : 'Expired',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
