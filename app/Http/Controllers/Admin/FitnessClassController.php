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
            'class_date' => 'nullable|date',
            'max_spots' => 'required|integer|min:1|max:50',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:instructors,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'active' => 'boolean',
            'recurring_frequency' => 'required|in:none,weekly,biweekly,monthly',
            'recurring_until' => 'nullable|date|after:class_date',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        // Convert recurring_days array to comma-separated string
        if (isset($validated['recurring_days'])) {
            $validated['recurring_days'] = implode(',', $validated['recurring_days']);
        }

        // Create the main class
        $class = FitnessClass::create($validated);

        // Create recurring instances if needed
        if ($validated['recurring_frequency'] !== 'none' && !empty($validated['recurring_days']) && $validated['recurring_until']) {
            $this->createRecurringInstances($class, $validated);
        }

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
            'class_date' => 'nullable|date',
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

    /**
     * Delete all class instances after a specific date
     */
    public function deleteAfterDate(Request $request, FitnessClass $class)
    {
        $request->validate([
            'delete_after_date' => 'required|date'
        ]);

        $deleteAfterDate = $request->delete_after_date;
        
        // Delete child instances after the specified date
        $deletedCount = FitnessClass::where('parent_class_id', $class->id)
            ->where('class_date', '>', $deleteAfterDate)
            ->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', "Deleted {$deletedCount} class instances after {$deleteAfterDate}.");
    }

    /**
     * Create recurring instances based on frequency and selected days
     */
    private function createRecurringInstances(FitnessClass $parentClass, array $validated)
    {
        $startDate = \Carbon\Carbon::parse($validated['class_date']);
        $endDate = \Carbon\Carbon::parse($validated['recurring_until']);
        $frequency = $validated['recurring_frequency'];
        $selectedDays = explode(',', $validated['recurring_days']);
        
        // Map day names to Carbon day numbers
        $dayMap = [
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4,
            'friday' => 5, 'saturday' => 6, 'sunday' => 0
        ];

        $currentDate = $startDate->copy();
        $instances = [];

        while ($currentDate->lte($endDate)) {
            $dayName = strtolower($currentDate->format('l'));
            
            if (in_array($dayName, $selectedDays) && !$currentDate->eq($startDate)) {
                $instances[] = [
                    'name' => $parentClass->name,
                    'description' => $parentClass->description,
                    'class_date' => $currentDate->format('Y-m-d'),
                    'instructor_id' => $parentClass->instructor_id,
                    'max_spots' => $parentClass->max_spots,
                    'price' => $parentClass->price,
                    'start_time' => $parentClass->start_time,
                    'end_time' => $parentClass->end_time,
                    'active' => $parentClass->active,
                    'recurring_frequency' => 'none',
                    'recurring_days' => null,
                    'parent_class_id' => $parentClass->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Increment based on frequency
            switch ($frequency) {
                case 'weekly':
                    $currentDate->addWeek();
                    break;
                case 'biweekly':
                    $currentDate->addWeeks(2);
                    break;
                case 'monthly':
                    $currentDate->addMonth();
                    break;
            }
        }

        if (!empty($instances)) {
            FitnessClass::insert($instances);
        }
    }
}
