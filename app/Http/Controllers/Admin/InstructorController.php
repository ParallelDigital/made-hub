<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;

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
            'email' => 'required|email|unique:instructors,email',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|string',
            'photo' => 'nullable|string|max:255',
            'active' => 'boolean'
        ]);

        Instructor::create($validated);

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:instructors,email,' . $instructor->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|string',
            'photo' => 'nullable|string|max:255',
            'active' => 'boolean'
        ]);

        $instructor->update($validated);

        return redirect()->route('admin.instructors.index')->with('success', 'Instructor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->delete();
        return redirect()->route('admin.instructors.index')->with('success', 'Instructor deleted successfully.');
    }
}
