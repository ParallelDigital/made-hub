<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by search term (name, email, user_login)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('user_login', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by registration date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $users = $query->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        // Get unique roles for filter dropdown
        $roles = User::whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->sort();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Include both 'administrator' and 'admin' so either can be assigned
        $roles = ['subscriber', 'administrator', 'admin', 'editor', 'author', 'contributor', 'wpamelia-customer'];
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|max:255',
            'user_login' => 'nullable|string|max:255|unique:users,user_login,' . $user->id,
            'nickname' => 'nullable|string|max:255',
        ]);

        $user->update($request->only([
            'name', 'first_name', 'last_name', 'email', 'role', 'user_login', 'nickname'
        ]));

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Add credits to a user (legacy credits or monthly credits).
     */
    public function addCredits(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:1|max:1000',
            'credit_type' => 'required|in:legacy,monthly',
            'note' => 'nullable|string|max:500',
        ]);

        $amount = (int) $data['amount'];
        $type = $data['credit_type'];

        if ($type === 'legacy') {
            // One-off legacy credits used when no active membership
            $user->increment('credits', $amount);
            $label = 'credits';
        } else {
            // Monthly credit top-up for members
            $user->increment('monthly_credits', $amount);
            $label = 'monthly credits';
        }

        $note = isset($data['note']) ? $data['note'] : null;

        \Log::info('Admin added user credits', [
            'admin_id' => $request->user()->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'credit_type' => $type,
            'note' => $note,
        ]);

        $userLabel = $user->name ?: $user->email;
        return back()->with('success', "Added {$amount} {$label} to {$userLabel}.");
    }

    /**
     * Export filtered users as CSV.
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Apply the same filters as index()
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('user_login', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $filename = 'users_export_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'ID', 'Name', 'First Name', 'Last Name', 'Email', 'Role', 'Username', 'Nickname',
                'Registered (WP)', 'Created At'
            ]);

            $query->orderByDesc('created_at')->chunk(1000, function ($rows) use ($handle) {
                foreach ($rows as $u) {
                    fputcsv($handle, [
                        $u->id,
                        $u->name,
                        $u->first_name,
                        $u->last_name,
                        $u->email,
                        $u->role,
                        $u->user_login,
                        $u->nickname,
                        optional($u->user_registered)->format('Y-m-d H:i:s'),
                        optional($u->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
