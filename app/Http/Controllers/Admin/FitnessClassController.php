<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FitnessClass;
use App\Models\Instructor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FitnessClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FitnessClass::with(['instructor', 'classType']);
        
        // Filter by instructor if provided
        if ($request->filled('instructor')) {
            $query->where('instructor_id', $request->instructor);
        }
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }
        
        // For list view
        if ($request->view === 'list') {
            $classes = $query->orderBy('class_date', 'desc')->paginate(15)->appends($request->query());
            $view = 'admin.classes.list';
        } 
        // For calendar view (default)
        else {
            $classes = $query->orderBy('class_date', 'asc')->paginate(15);
            $view = 'admin.classes.index';
        }
        
        // Get instructors for filter dropdown
        $instructors = \App\Models\Instructor::where('active', true)->orderBy('name')->get();
        
        return view($view, compact('classes', 'instructors'));
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
            'class_type_id' => 'required|exists:class_types,id',
            'instructor_id' => 'required|exists:instructors,id',
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_spots' => 'required|integer|min:1',
            'price' => 'required_unless:members_only,1|numeric|min:0',
            'members_only' => 'sometimes|boolean',
            'location' => 'required|string|max:255',
            'active' => 'boolean',
            'recurring' => 'boolean',
            'recurring_weekly' => 'boolean',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'integer|between:0,6',
            'recurring_frequency' => 'nullable|integer|min:1',
            'recurring_until' => 'nullable|date|after:class_date',
        ]);

        // Ensure members-only classes are free
        if ($request->boolean('members_only')) {
            $validated['members_only'] = true;
            $validated['price'] = 0;
        } else {
            $validated['members_only'] = false;
        }

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
            'class_type_id' => 'required|exists:class_types,id',
            'instructor_id' => 'required|exists:instructors,id',
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_spots' => 'required|integer|min:1',
            'price' => 'required_unless:members_only,1|numeric|min:0',
            'members_only' => 'sometimes|boolean',
            'location' => 'required|string|max:255',
            'active' => 'boolean',
            'recurring' => 'boolean',
            'recurring_weekly' => 'boolean',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'integer|between:0,6',
            'recurring_frequency' => 'nullable|integer|min:1',
            'recurring_until' => 'nullable|date|after:class_date',
        ]);

        // Ensure members-only classes are free
        if ($request->boolean('members_only')) {
            $validated['members_only'] = true;
            $validated['price'] = 0;
        } else {
            $validated['members_only'] = false;
        }

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
        
        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }
    
    /**
     * Get classes data for the calendar
     */
    public function getCalendarData(Request $request): JsonResponse
    {
        $query = FitnessClass::with(['instructor', 'classType']);
        
        // Apply filters
        if ($request->filled('instructor')) {
            $query->where('instructor_id', $request->instructor);
        }
        
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }
        
        // Filter by date range if provided
        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('class_date', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay()
            ]);
        }
        
        $classes = $query->get()->map(function($class) {
            return [
                'id' => $class->id,
                'title' => $class->classType->name . ($class->instructor ? ' - ' . $class->instructor->name : ''),
                'start' => $class->class_date->format('Y-m-d') . 'T' . $class->start_time,
                'end' => $class->class_date->format('Y-m-d') . 'T' . $class->end_time,
                'url' => route('admin.classes.show', $class->id),
                'backgroundColor' => $class->classType->color ?? '#3b82f6',
                'borderColor' => $class->classType->color ?? '#3b82f6',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'instructor' => $class->instructor ? $class->instructor->name : 'No Instructor',
                    'location' => $class->location,
                    'capacity' => $class->max_spots,
                    'booked' => $class->bookings()->count(),
                    'status' => $class->active ? 'Active' : 'Inactive',
                    'description' => 
                        'Type: ' . $class->classType->name . '\n' .
                        'Instructor: ' . ($class->instructor ? $class->instructor->name : 'N/A') . '\n' .
                        'Location: ' . $class->location . '\n' .
                        'Time: ' . $class->start_time . ' - ' . $class->end_time . '\n' .
                        'Capacity: ' . $class->bookings()->count() . '/' . $class->max_spots
                ]
            ];
        });
        
        return response()->json($classes);
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
                    'members_only' => (bool) $parentClass->members_only,
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
