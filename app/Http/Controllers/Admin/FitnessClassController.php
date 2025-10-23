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
        // For calendar view (default) - group classes by name
        else {
            // Get all classes without pagination first
            $allClasses = $query->orderBy('name')->orderBy('class_date', 'asc')->get();
            
            // Group classes by name
            $groupedClasses = [];
            foreach ($allClasses as $class) {
                $className = $class->name;
                if (!isset($groupedClasses[$className])) {
                    $groupedClasses[$className] = [
                        'name' => $className,
                        'classes' => [],
                        'instructor' => $class->instructor->name ?? 'No Instructor',
                        'total_instances' => 0
                    ];
                }
                $groupedClasses[$className]['classes'][] = $class;
                $groupedClasses[$className]['total_instances']++;
            }
            
            // Convert to collection for pagination
            $classes = collect(array_values($groupedClasses));
            $perPage = 15;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
            $currentPageItems = $classes->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $classes = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $classes->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            
            $view = 'admin.classes.index';
        }
        
        // Get instructors for filter dropdown
        $instructors = \App\Models\Instructor::where('active', true)->orderBy('name')->get();
        
        return view($view, compact('classes', 'instructors'));
    }

    /**
     * Deduplicate class instances for admin calendar: prefer child instances (have parent_class_id)
     * over their parent templates when they share the same slot/date/instructor/location/name.
     */
    private function dedupeCalendarClasses($classes)
    {
        if (!$classes) {
            return collect();
        }

        $grouped = $classes->groupBy(function($c) {
            $date = $c->class_date instanceof \Carbon\Carbon ? $c->class_date->toDateString() : (string) $c->class_date;
            $name = strtolower(trim((string) $c->name));
            $time = (string) $c->start_time;
            $instructor = (string) ($c->instructor_id ?? '0');
            $location = strtolower(trim((string) ($c->location ?? '')));
            return $date.'|'.$time.'|'.$instructor.'|'.$location.'|'.$name;
        });

        return $grouped->map(function($group) {
            // Prefer a child instance if present
            $child = $group->first(function($c) { return !is_null($c->parent_class_id); });
            if ($child) {
                return $child;
            }
            // Otherwise prefer an active entry
            $active = $group->first(function($c) { return (bool) $c->active; });
            if ($active) {
                return $active;
            }
            // Fallback: latest by ID
            return $group->sortByDesc('id')->first();
        })->values();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $instructors = Instructor::where('active', true)->get();
        $classTypes = \App\Models\ClassType::orderBy('name')->get();
        return view('admin.classes.create', compact('instructors', 'classTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_type_id' => 'nullable|exists:class_types,id',
            'instructor_id' => 'required|exists:instructors,id',
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_spots' => 'required|integer|min:1',
            'price' => 'required_unless:members_only,1|numeric|min:0',
            'members_only' => 'sometimes|boolean',
            'location' => 'nullable|string|max:255',
            'active' => 'boolean',
            'recurring' => 'boolean',
            'recurring_weekly' => 'boolean',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'recurring_frequency' => 'required|string|in:none,weekly,biweekly,monthly',
            'recurring_until' => 'nullable|date|after:class_date',
        ]);

        // Ensure members-only classes are free
        if ($request->boolean('members_only')) {
            $validated['members_only'] = true;
            $validated['price'] = 0;
        } else {
            $validated['members_only'] = false;
        }

        // Keep legacy boolean in sync for schedule queries
        $validated['recurring'] = ($validated['recurring_frequency'] ?? 'none') !== 'none';

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
    public function show(Request $request, FitnessClass $class)
    {
        // Get the specific date filter from request (for recurring classes)
        $filterDate = $request->input('date');
        
        if ($class->isRecurring() && !$class->isChildClass()) {
            $class->load(['instructor']);
            
            // If a specific date is provided, filter bookings for that date only
            if ($filterDate) {
                $targetDate = \Carbon\Carbon::parse($filterDate)->toDateString();
                $filteredBookings = $class->bookings()
                    ->whereDate('booking_date', $targetDate)
                    ->with('user')
                    ->get();
                $class->setRelation('bookings', $filteredBookings);
                
                // Override the display date to show the filtered date
                $class->display_date = \Carbon\Carbon::parse($targetDate);
            } else {
                // No date filter - show ALL bookings across all dates
                $class->load(['bookings.user', 'childClasses.bookings.user']);
                $allBookings = collect($class->bookings ? $class->bookings->all() : []);
                foreach ($class->childClasses as $child) {
                    if ($child->relationLoaded('bookings')) {
                        $allBookings = $allBookings->merge($child->bookings);
                    } else {
                        $allBookings = $allBookings->merge($child->bookings()->with('user')->get());
                    }
                }
                $class->setRelation('bookings', $allBookings);
            }
        } elseif ($class->isChildClass()) {
            $class->load(['instructor', 'bookings.user', 'parentClass.bookings.user']);
            $targetDate = $class->class_date ? $class->class_date->toDateString() : null;
            $parentDateBookings = collect();
            if ($class->parentClass && $class->parentClass->relationLoaded('bookings')) {
                $parentDateBookings = $class->parentClass->bookings->filter(function($b) use ($targetDate) {
                    return $b->booking_date && $b->booking_date->toDateString() === $targetDate;
                });
            } elseif ($class->parentClass && $targetDate) {
                $parentDateBookings = $class->parentClass->bookings()
                    ->whereDate('booking_date', $targetDate)
                    ->with('user')
                    ->get();
            }
            $class->setRelation('bookings', $class->bookings->merge($parentDateBookings));
        } else {
            $class->load('instructor', 'bookings.user');
        }

        return view('admin.classes.show', compact('class', 'filterDate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FitnessClass $class)
    {
        $instructors = Instructor::where('active', true)->get();
        $classTypes = \App\Models\ClassType::orderBy('name')->get();
        return view('admin.classes.edit', compact('class', 'instructors', 'classTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FitnessClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_type_id' => 'nullable|exists:class_types,id',
            'instructor_id' => 'required|exists:instructors,id',
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_spots' => 'required|integer|min:1',
            'price' => 'required_unless:members_only,1|numeric|min:0',
            'members_only' => 'sometimes|boolean',
            'location' => 'nullable|string|max:255',
            'active' => 'boolean',
            'recurring' => 'boolean',
            'recurring_weekly' => 'boolean',
            'recurring_days' => 'nullable|array',
            'recurring_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'recurring_frequency' => 'required|string|in:none,weekly,biweekly,monthly',
            'recurring_until' => 'nullable|date|after:class_date',
        ]);

        // Ensure members-only classes are free
        if ($request->boolean('members_only')) {
            $validated['members_only'] = true;
            $validated['price'] = 0;
        } else {
            $validated['members_only'] = false;
        }

        // Keep legacy boolean in sync for schedule queries
        $validated['recurring'] = ($validated['recurring_frequency'] ?? 'none') !== 'none';

        // Convert recurring_days array to comma-separated string
        if (isset($validated['recurring_days'])) {
            $validated['recurring_days'] = implode(',', $validated['recurring_days']);
        }

        $class->update($validated);

        // Handle recurring instances creation/update
        $oldRecurringFrequency = $class->getOriginal('recurring_frequency');
        $newRecurringFrequency = $validated['recurring_frequency'];
        $oldRecurringDays = $class->getOriginal('recurring_days');
        $newRecurringDays = $validated['recurring_days'] ?? '';
        $oldClassDate = $class->getOriginal('class_date');
        $newClassDate = $validated['class_date'];

        // If recurring settings changed, handle child instances
        if ($oldRecurringFrequency !== $newRecurringFrequency || $oldRecurringDays !== $newRecurringDays || $oldClassDate !== $newClassDate) {
            // Delete existing child instances if recurring settings were removed or changed
            if ($oldRecurringFrequency !== 'none') {
                FitnessClass::where('parent_class_id', $class->id)->delete();
            }

            // Create new recurring instances if now set to recurring
            if ($newRecurringFrequency !== 'none' && !empty($validated['recurring_days']) && $validated['recurring_until']) {
                $this->createRecurringInstances($class, $validated);
            }
        } elseif ($newRecurringFrequency !== 'none' && !empty($validated['recurring_days']) && $validated['recurring_until']) {
            // Check if recurring_until date changed - if so, recreate instances
            $oldUntilDate = $class->getOriginal('recurring_until');
            $newUntilDate = $validated['recurring_until'];

            if ($oldUntilDate !== $newUntilDate) {
                // Delete existing child instances
                FitnessClass::where('parent_class_id', $class->id)->delete();
                // Create new ones with updated end date
                $this->createRecurringInstances($class, $validated);
            }
        }

        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FitnessClass $class)
    {
        // Cancel any bookings for this class
        $bookings = $class->bookings()->where('status', 'confirmed')->get();
        foreach ($bookings as $booking) {
            $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        // Delete child instances first
        FitnessClass::where('parent_class_id', $class->id)->delete();

        $class->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'Class and all recurring instances deleted successfully.');
    }

    /**
     * Cancel the specified class (deactivate it and cancel bookings).
     */
    public function cancel(Request $request, FitnessClass $class)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $reason = $request->input('reason', '');
        $bookingCount = 0;
        $affectedUsers = collect();

        try {
            // Cancel any bookings for this class
            $bookings = $class->bookings()->where('status', 'confirmed')->with('user')->get();
            foreach ($bookings as $booking) {
                $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                $bookingCount++;

                if ($booking->user) {
                    $affectedUsers->push($booking->user);
                }
            }

            // Cancel child instances if this is a parent recurring class
            if ($class->isRecurring() && !$class->isChildClass()) {
                $childInstances = FitnessClass::where('parent_class_id', $class->id)
                    ->where('class_date', '>=', now()->format('Y-m-d'))
                    ->get();

                foreach ($childInstances as $instance) {
                    $instanceBookings = $instance->bookings()->where('status', 'confirmed')->with('user')->get();
                    foreach ($instanceBookings as $booking) {
                        $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                        $bookingCount++;

                        if ($booking->user) {
                            $affectedUsers->push($booking->user);
                        }
                    }
                    $instance->update(['active' => false]);
                }
            }

            // Deactivate the class
            $class->update(['active' => false]);

            // Send cancellation emails to affected users
            if ($affectedUsers->count() > 0 && !empty($reason)) {
                $this->sendCancellationEmails($class, $affectedUsers, $reason);
            }

            $message = "Class cancelled successfully.";
            if ($bookingCount > 0) {
                $message .= " {$bookingCount} associated bookings were also cancelled.";
            }
            if ($affectedUsers->count() > 0 && !empty($reason)) {
                $message .= " Cancellation notices sent to {$affectedUsers->count()} affected members.";
            }

            return redirect()
                ->route('admin.classes.show', $class)
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Failed to cancel class: ' . $e->getMessage(), [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('admin.classes.show', $class)
                ->with('error', 'Failed to cancel class. Please try again or contact support.');
        }
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
        
        $classes = $query->get();

        // Deduplicate by (date | start_time | instructor | location | name)
        $classes = $this->dedupeCalendarClasses($classes);

        $classes = $classes->map(function($class) {
            $bookedCount = 0;
            try {
                if ($class->isChildClass()) {
                    $bookedCount = $class->bookings()->count();
                    if ($bookedCount === 0 && $class->parentClass) {
                        $bookedCount = $class->parentClass->bookings()
                            ->whereDate('booking_date', $class->class_date->toDateString())
                            ->count();
                    }
                } else {
                    $bookedCount = $class->bookings()
                        ->when($class->class_date, function($q) use ($class) {
                            return $q->whereDate('booking_date', $class->class_date->toDateString());
                        })
                        ->count();
                }
            } catch (\Throwable $e) {
                $bookedCount = $class->bookings()->count();
            }

            $classDate = $class->class_date->format('Y-m-d');
            
            // For recurring classes, include the date parameter in the URL
            $showUrl = $class->isRecurring() && !$class->isChildClass()
                ? route('admin.classes.show', ['class' => $class->id, 'date' => $classDate])
                : route('admin.classes.show', $class->id);
            
            return [
                'id' => $class->id,
                'title' => ($class->classType->name ?? $class->name) . ($class->instructor ? ' - ' . $class->instructor->name : ''),
                'start' => $classDate . 'T' . $class->start_time,
                'end' => $classDate . 'T' . $class->end_time,
                'url' => $showUrl,
                'backgroundColor' => $class->classType->color ?? '#3b82f6',
                'borderColor' => $class->classType->color ?? '#3b82f6',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'instructor' => $class->instructor ? $class->instructor->name : 'No Instructor',
                    'location' => $class->location ?? 'N/A',
                    'capacity' => $class->max_spots,
                    'booked' => $bookedCount,
                    'status' => $class->active ? 'Active' : 'Inactive',
                    'description' =>
                        'Type: ' . ($class->classType->name ?? 'N/A') . "\n" .
                        'Instructor: ' . ($class->instructor ? $class->instructor->name : 'N/A') . "\n" .
                        'Location: ' . ($class->location ?? 'N/A') . "\n" .
                        'Time: ' . $class->start_time . ' - ' . $class->end_time . "\n" .
                        'Capacity: ' . $bookedCount . '/' . $class->max_spots,
                ],
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

        // Get child instances that will be deleted (to handle associated bookings)
        $instancesToDelete = FitnessClass::where('parent_class_id', $class->id)
            ->where('class_date', '>=', $deleteAfterDate)
            ->get();

        // Cancel any bookings for these instances
        $bookingCount = 0;
        foreach ($instancesToDelete as $instance) {
            $bookings = $instance->bookings()->where('status', 'confirmed')->get();
            foreach ($bookings as $booking) {
                $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                $bookingCount++;
            }
        }

        // Delete child instances on or after the specified date
        $deletedCount = FitnessClass::where('parent_class_id', $class->id)
            ->where('class_date', '>=', $deleteAfterDate)
            ->delete();

        $message = "Deleted {$deletedCount} class instances";
        if ($bookingCount > 0) {
            $message .= " and cancelled {$bookingCount} associated bookings";
        }
        $message .= " on or after {$deleteAfterDate}.";

        return redirect()->route('admin.classes.index')
            ->with('success', $message);
    }

    /**
     * Send cancellation notification emails to affected users
     */
    private function sendCancellationEmails(FitnessClass $class, $affectedUsers, $reason)
    {
        $affectedUsers = $affectedUsers->unique('email');

        foreach ($affectedUsers as $user) {
            try {
                \Mail::to($user->email)->send(new \App\Mail\ClassCancelled($class, $user, $reason));
            } catch (\Exception $e) {
                \Log::warning('Failed to send cancellation email to ' . $user->email . ': ' . $e->getMessage());
            }
        }
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

        // Map day names to Carbon day numbers (0 = Sunday, 1 = Monday, etc.)
        $dayMap = [
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4,
            'friday' => 5, 'saturday' => 6, 'sunday' => 0
        ];

        $instances = [];
        $currentDate = $startDate->copy();

        // For each selected day, find all occurrences within the date range
        foreach ($selectedDays as $dayName) {
            $dayNumber = $dayMap[$dayName];
            $dayDate = $startDate->copy()->next($dayNumber); // Find next occurrence of this day

            // If the start date is already on the selected day, include it (but don't create duplicate)
            if ($startDate->dayOfWeek === $dayNumber) {
                $dayDate = $startDate->copy();
            }

            while ($dayDate->lte($endDate)) {
                // Skip the original class date to avoid duplicates
                if (!$dayDate->eq($startDate)) {
                    $instances[] = [
                        'name' => $parentClass->name,
                        'description' => $parentClass->description,
                        'class_date' => $dayDate->format('Y-m-d'),
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

                // Move to next occurrence based on frequency
                switch ($frequency) {
                    case 'weekly':
                        $dayDate->addWeek();
                        break;
                    case 'biweekly':
                        $dayDate->addWeeks(2);
                        break;
                    case 'monthly':
                        $dayDate->addMonth();
                        break;
                }
            }
        }

        if (!empty($instances)) {
            FitnessClass::insert($instances);
        }
    }
}
