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
    public function index(Request $request)
    {
        $query = FitnessClass::with('instructor');
        
        // Filter by instructor if provided
        if ($request->filled('instructor')) {
            $query->where('instructor_id', $request->instructor);
        }
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }
        
        $classes = $query->paginate(15)->appends($request->query());
        
        // Get instructors for filter dropdown
        $instructors = \App\Models\Instructor::where('active', true)->orderBy('name')->get();
        
        return view('admin.classes.index', compact('classes', 'instructors'));
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
            'max_spots' => 'required|integer|min:1|max:50',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'active' => 'boolean',
            'recurring_weekly' => 'boolean',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        // Convert recurring_days array to comma-separated string
        if (isset($validated['recurring_days'])) {
            $validated['recurring_days'] = implode(',', $validated['recurring_days']);
        }

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
            'max_spots' => 'required|integer|min:1|max:50',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'active' => 'boolean',
            'recurring_weekly' => 'boolean',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        // Convert recurring_days array to comma-separated string
        if (isset($validated['recurring_days'])) {
            $validated['recurring_days'] = implode(',', $validated['recurring_days']);
        }

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
