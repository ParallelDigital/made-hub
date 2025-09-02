<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FitnessClass;
use App\Models\Instructor;
use Illuminate\Http\Request;

class FitnessClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = FitnessClass::with('instructor')->paginate(15);
        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $instructors = Instructor::where('active', true)->get();
        return view('admin.classes.create', compact('instructors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'duration' => 'required|integer|min:15|max:180',
            'max_spots' => 'required|integer|min:1|max:50',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'active' => 'boolean'
        ]);

        FitnessClass::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FitnessClass $class)
    {
        $class->load('instructor', 'bookings.user');
        return view('admin.classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FitnessClass $class)
    {
        $instructors = Instructor::where('active', true)->get();
        return view('admin.classes.edit', compact('class', 'instructors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FitnessClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'duration' => 'required|integer|min:15|max:180',
            'max_spots' => 'required|integer|min:1|max:50',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'active' => 'boolean'
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FitnessClass $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully.');
    }
}
