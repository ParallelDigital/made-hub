<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instructors = Instructor::withCount('fitnessClasses')->paginate(15);
        return view('admin.instructors.index', compact('instructors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.instructors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'required|string|min:8|confirmed',
            'active' => 'boolean'
        ]);

        // Create the User record for authentication
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'instructor',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('instructors', 'public');
        }

        $validated['active'] = $request->has('active');

        // Create the Instructor record
        Instructor::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'photo' => $validated['photo'] ?? null,
            'active' => $validated['active'],
        ]);

        return redirect()->route('admin.instructors.index')->with('success', 'Instructor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Instructor $instructor)
    {
        $instructor->load('fitnessClasses');
        return view('admin.instructors.show', compact('instructor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instructor $instructor)
    {
        return view('admin.instructors.edit', compact('instructor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instructor $instructor)
    {
        // Find the user by the instructor's current email to correctly handle email changes.
        $user = \App\Models\User::where('email', $instructor->email)->first();

        $emailValidationRule = 'required|email|unique:users,email';
        if ($user) {
            $emailValidationRule .= ',' . $user->id;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailValidationRule,
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
            'active' => 'boolean'
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $instructor, $validated, $user) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => 'instructor',
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
                }

                // Update or create the user record.
                \App\Models\User::updateOrCreate(['email' => $instructor->email], $userData);

                // Handle photo upload within the transaction.
                $photoPath = $instructor->photo;
                if ($request->hasFile('photo')) {
                    if ($instructor->photo) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($instructor->photo);
                    }
                    $photoPath = $request->file('photo')->store('instructors', 'public');
                }

                // Update the Instructor record.
                $instructor->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'photo' => $photoPath,
                    'active' => $request->has('active'),
                ]);
            });
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message.
            logger()->error('Failed to update instructor: ' . $e->getMessage());
            return back()->with('error', 'Failed to update instructor. Please try again.');
        }

        return redirect()->route('admin.instructors.index')->with('success', 'Instructor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        // Find and delete the associated User record
        if ($user = \App\Models\User::where('email', $instructor->email)->first()) {
            $user->delete();
        }

        // Delete the instructor's photo if it exists
        if ($instructor->photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($instructor->photo);
        }

        $instructor->delete();

        return redirect()->route('admin.instructors.index')->with('success', 'Instructor and associated user account deleted successfully.');
    }
}
