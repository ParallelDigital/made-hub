<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\FitnessClass;
use App\Models\ClassType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $instructors = Instructor::orderBy('name')->withCount('fitnessClasses')->paginate(15);

        // Calendar parameters (match admin dashboard)
        $view = $request->get('view', 'weekly'); // weekly or monthly
        $weekOffset = (int) $request->get('week', 0);
        $selectedInstructor = $request->get('instructor');

        $currentWeekStart = now()->startOfWeek(\Carbon\Carbon::SUNDAY)->addWeeks($weekOffset);

        // Fetch classes for calendar, optionally filtered by instructor
        $classesQuery = \App\Models\FitnessClass::with('instructor')
            ->where('active', true)
            ->orderBy('start_time');
        if (!empty($selectedInstructor)) {
            $classesQuery->where('instructor_id', $selectedInstructor);
        }
        $classes = $classesQuery->get();

        if ($view === 'weekly') {
            $calendarData = $this->buildWeeklyCalendarData($classes, $currentWeekStart);
            $calendarDates = collect(range(0, 6))->map(function($day) use ($currentWeekStart) {
                return $currentWeekStart->copy()->addDays($day);
            });
        } else {
            $monthStart = $currentWeekStart->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $calendarData = $this->buildMonthlyCalendarData($classes, $monthStart);
            $calendarDates = collect(range(0, 41))->map(function($day) use ($monthStart) {
                return $monthStart->copy()->addDays($day);
            });
        }

        // For instructor dropdown (active only)
        $allInstructors = Instructor::where('active', true)->orderBy('name')->get();

        return view('admin.instructors.index', [
            'instructors' => $instructors,
            'allInstructors' => $allInstructors,
            'view' => $view,
            'weekOffset' => $weekOffset,
            'currentWeekStart' => $currentWeekStart,
            'calendarData' => $calendarData,
            'calendarDates' => $calendarDates,
            'selectedInstructor' => $selectedInstructor,
        ]);
    }

    private function buildWeeklyCalendarData($classes, $weekStart)
    {
        $calendarData = collect(range(0, 6))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        foreach ($classes as $class) {
            if ($class->recurring) {
                $recurringDays = json_decode($class->recurring_days, true) ?? [];
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                foreach ($recurringDays as $dayName) {
                    if (isset($dayMapping[$dayName])) {
                        $calendarData[$dayMapping[$dayName]]->push($class);
                    }
                }
            } else {
                $classDate = \Carbon\Carbon::parse($class->class_date);
                if ($classDate->between($weekStart, $weekStart->copy()->addDays(6))) {
                    $calendarData[$classDate->dayOfWeek]->push($class);
                }
            }
        }

        return $calendarData;
    }

    private function buildMonthlyCalendarData($classes, $monthStart)
    {
        $calendarData = collect(range(0, 41))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        foreach ($classes as $class) {
            if ($class->recurring) {
                $recurringDays = json_decode($class->recurring_days, true) ?? [];
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                for ($week = 0; $week < 6; $week++) {
                    foreach ($recurringDays as $dayName) {
                        if (isset($dayMapping[$dayName])) {
                            $dayIndex = ($week * 7) + $dayMapping[$dayName];
                            if ($dayIndex <= 41) {
                                $calendarData[$dayIndex]->push($class);
                            }
                        }
                    }
                }
            } else {
                $classDate = \Carbon\Carbon::parse($class->class_date);
                $daysDiff = $monthStart->diffInDays($classDate, false);
                if ($daysDiff >= 0 && $daysDiff <= 41) {
                    $calendarData[$daysDiff]->push($class);
                }
            }
        }

        return $calendarData;
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

        $instructorData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'active' => $request->has('active'),
        ];

        if ($request->hasFile('photo')) {
            $instructorData['photo'] = $request->file('photo')->store('instructors', 'public');
        }

        // Create the Instructor record
        $instructor = Instructor::create($instructorData);

        return redirect()->route('admin.instructors.show', $instructor)->with('success', 'Instructor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Instructor $instructor)
    {
        // Calendar controls (match admin dashboard)
        $view = request('view', 'weekly'); // weekly or monthly
        $weekOffset = (int) request('week', 0);
        $currentWeekStart = now()->startOfWeek(\Carbon\Carbon::SUNDAY)->addWeeks($weekOffset);

        // Load instructor and their classes
        $instructor->load('fitnessClasses');

        // Fetch classes only for this instructor
        $classes = \App\Models\FitnessClass::with('instructor')
            ->where('active', true)
            ->where('instructor_id', $instructor->id)
            ->orderBy('start_time')
            ->get();

        if ($view === 'weekly') {
            $calendarData = $this->buildWeeklyCalendarData($classes, $currentWeekStart);
            $calendarDates = collect(range(0, 6))->map(function($day) use ($currentWeekStart) {
                return $currentWeekStart->copy()->addDays($day);
            });
        } else {
            $monthStart = $currentWeekStart->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $calendarData = $this->buildMonthlyCalendarData($classes, $monthStart);
            $calendarDates = collect(range(0, 41))->map(function($day) use ($monthStart) {
                return $monthStart->copy()->addDays($day);
            });
        }

        // Active class types list (if used elsewhere in the view)
        $classTypes = ClassType::where('active', true)->orderBy('name')->get();

        return view('admin.instructors.show', [
            'instructor' => $instructor,
            'classTypes' => $classTypes,
            'view' => $view,
            'weekOffset' => $weekOffset,
            'currentWeekStart' => $currentWeekStart,
            'calendarData' => $calendarData,
            'calendarDates' => $calendarDates,
        ]);
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
        // Find the current linked user by instructor's existing email, and any target user by the new email.
        $oldUser = User::where('email', $instructor->email)->first();
        $targetUser = User::where('email', $request->input('email'))->first();

        // Build robust uniqueness validation: allow the target user's email, and ensure instructors.email stays unique.
        $ignoreUserId = $targetUser?->id ?? $oldUser?->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($ignoreUserId),
                Rule::unique('instructors', 'email')->ignore($instructor->id),
            ],
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
            'active' => 'boolean'
        ]);

        try {
            DB::transaction(function () use ($request, $instructor, $validated, $oldUser, $targetUser) {
                // Choose which user record to update: prefer an existing user with the new email.
                $userToUpdate = $targetUser ?: $oldUser;

                if (!$userToUpdate) {
                    // No user exists with either email; create one
                    $userToUpdate = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => !empty($validated['password']) ? Hash::make($validated['password']) : Hash::make(\Str::random(16)),
                        'role' => 'instructor',
                    ]);
                } else {
                    // Update details on the chosen user; ensure it has the new email
                    $userData = [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'role' => 'instructor',
                    ];
                    if (!empty($validated['password'])) {
                        $userData['password'] = Hash::make($validated['password']);
                    }
                    $userToUpdate->update($userData);
                }

                // Handle photo upload within the transaction.
                $photoPath = $instructor->photo;
                if ($request->hasFile('photo')) {
                    // Delete old photo if it exists
                    if ($instructor->photo && Storage::disk('public')->exists($instructor->photo)) {
                        Storage::disk('public')->delete($instructor->photo);
                    }
                    // Store new photo
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

        return redirect()->route('admin.instructors.edit', $instructor)->with('success', 'Instructor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        // Don't delete the instructor if they have classes
        if ($instructor->fitnessClasses()->exists()) {
            return redirect()
                ->route('admin.instructors.index')
                ->with('error', 'Cannot delete instructor with assigned classes. Please reassign or delete the classes first.');
        }

        // Delete the associated user
        if ($instructor->user) {
            $instructor->user->delete();
        }

        // Delete the photo if it exists
        if ($instructor->photo) {
            Storage::delete($instructor->photo);
        }

        $instructor->delete();

        return redirect()
            ->route('admin.instructors.index')
            ->with('success', 'Instructor deleted successfully.');
    }

    /**
     * Get instructor's classes for the calendar
     */
    /**
     * Get instructor's classes for the calendar.
     *
     * @param  \App\Models\Instructor  $instructor
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClasses(Instructor $instructor): JsonResponse
    {
        $start = request('start');
        $end = request('end');

        $classes = FitnessClass::where('instructor_id', $instructor->id)
            ->where(function($query) use ($start, $end) {
                if ($start && $end) {
                    $query->whereBetween('class_date', [
                        Carbon::parse($start)->startOfDay(),
                        Carbon::parse($end)->endOfDay()
                    ]);
                }
            })
            ->with(['classType', 'bookings', 'instructor'])
            ->get()
            ->map(function($class) {
                try {
                    $classDateStr = is_string($class->class_date)
                        ? $class->class_date
                        : ($class->class_date ? $class->class_date->toDateString() : null);

                    if (!$classDateStr) {
                        // Fallback: skip invalid records gracefully
                        return null;
                    }

                    $startDateTime = Carbon::parse($classDateStr . ' ' . $class->start_time);
                    $endDateTime = Carbon::parse($classDateStr . ' ' . $class->end_time);
                    
                    // If end time is before start time, it means the class ends the next day
                    if ($endDateTime->lessThan($startDateTime)) {
                        $endDateTime->addDay();
                    }
                    
                    $classType = $class->classType ?? new ClassType([
                        'id' => null,
                        'name' => 'Other',
                        'duration' => 60,
                        'color' => '#6b7280' // gray-500
                    ]);
                    
                    return [
                        'id' => $class->id,
                        'title' => $class->name . ' - ' . $classType->name,
                        'start' => $startDateTime->toDateTimeString(),
                        'end' => $endDateTime->toDateTimeString(),
                        'url' => route('admin.classes.edit', $class->id),
                        'backgroundColor' => $classType->color,
                        'borderColor' => $classType->color,
                        'textColor' => '#ffffff',
                        'classNames' => [
                            $class->active ? 'active-class' : 'inactive-class',
                            'class-type-' . strtolower(str_replace(' ', '-', $classType->name))
                        ],
                        'extendedProps' => [
                            'class_type' => [
                                'id' => $classType->id,
                                'name' => $classType->name,
                                'duration' => $classType->duration,
                                'color' => $classType->color
                            ],
                            'location' => $class->location,
                            'max_spots' => $class->max_spots,
                            'booked_spots' => $class->bookings->count(),
                            'available_spots' => $class->max_spots - $class->bookings->count(),
                            'status' => $class->active ? 'active' : 'inactive',
                            'price' => $class->price,
                            'instructor' => [
                                'id' => $class->instructor->id,
                                'name' => $class->instructor->name,
                                'email' => $class->instructor->email
                            ]
                        ]
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error formatting class for calendar', [
                        'class_id' => $class->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            })
            ->filter(); // Remove any null values from the map

        return response()->json($classes);
    }
}
