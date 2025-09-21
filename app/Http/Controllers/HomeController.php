<?php

namespace App\Http\Controllers;

use App\Models\FitnessClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get selected date or default to today
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $showPast = $request->boolean('show_past', false);
        
        // Get current week's classes based on selected date (Monday to Sunday)
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $selectedDate->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Get selected date's classes for the schedule display
        $selectedDateString = $selectedDate->toDateString();
        $dayOfWeek = $selectedDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        
        // Get both exact date matches and recurring classes for this day of week
        $selectedDateClasses = FitnessClass::with(['instructor', 'bookings'])
            ->where('active', 1)
            ->where(function($query) use ($selectedDateString, $dayOfWeek) {
                // Exact date match
                $query->whereDate('class_date', $selectedDateString)
                      // OR recurring classes that match this day of week
                      ->orWhere(function($subQuery) use ($dayOfWeek) {
                          $subQuery->where('recurring', 1)
                                   ->whereRaw("strftime('%w', class_date) = ?", [(string)$dayOfWeek]); // SQLite strftime %w is 0-based (0=Sunday)
                      });
            })
            ->orderBy('start_time')
            ->get();

        // Filter out past classes by default
        $now = Carbon::now();
        $selectedDateClasses = $selectedDateClasses->filter(function($class) use ($selectedDate, $now, $showPast) {
            // For future dates: show all
            if ($selectedDate->isFuture()) {
                return true;
            }
            // For past dates: show only if explicitly toggled
            if ($selectedDate->isPast() && !$selectedDate->isToday()) {
                return $showPast;
            }
            // For today: show future classes by default; include past only if toggled
            $startTime = !empty($class->start_time) ? $class->start_time : '00:00';
            $selectedStart = Carbon::parse($selectedDate->toDateString() . ' ' . $startTime);
            return $showPast ? true : $selectedStart->greaterThanOrEqualTo($now);
        });
        
        // Get week data for navigation (Monday–Sunday)
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'day' => $date->format('D'),
                'date' => $date->format('j'),
                'full_date' => $date->format('Y-m-d'),
                'is_today' => $date->isToday(),
                'is_selected' => $date->isSameDay($selectedDate)
            ];
        }
        
        // Navigation dates
        $prevWeek = $startOfWeek->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $startOfWeek->copy()->addWeek()->format('Y-m-d');
        
        return view('welcome', compact('selectedDateClasses', 'weekDays', 'selectedDate', 'prevWeek', 'nextWeek', 'showPast'));
    }

    public function getClasses(Request $request)
    {
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $showPast = $request->boolean('show_past', false);
        
        // Get current week's classes based on selected date (Monday to Sunday)
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        
        // Get selected date's classes for the schedule display
        $selectedDateString = $selectedDate->toDateString();
        $dayOfWeek = $selectedDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        
        // Get both exact date matches and recurring classes for this day of week
        $selectedDateClasses = FitnessClass::with(['instructor', 'bookings'])
            ->where('active', 1)
            ->where(function($query) use ($selectedDateString, $dayOfWeek) {
                // Exact date match
                $query->whereDate('class_date', $selectedDateString)
                      // OR recurring classes that match this day of week
                      ->orWhere(function($subQuery) use ($dayOfWeek) {
                          $subQuery->where('recurring', 1)
                                   ->whereRaw("strftime('%w', class_date) = ?", [(string)$dayOfWeek]); // SQLite strftime %w is 0-based (0=Sunday)
                      });
            })
            ->orderBy('start_time')
            ->get();


        // Homepage calendar: show ALL classes (past and future)
        // No filtering applied - users see complete schedule



        
        // Get week data for navigation (Monday–Sunday)
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'day' => $date->format('D'),
                'date' => $date->format('j'),
                'full_date' => $date->format('Y-m-d'),
                'is_today' => $date->isToday(),
                'is_selected' => $date->isSameDay($selectedDate)
            ];
        }
        
        // Navigation dates
        $prevWeek = $startOfWeek->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $startOfWeek->copy()->addWeek()->format('Y-m-d');


        return response()->json([
            'classes' => $selectedDateClasses->map(function($class) use ($selectedDate, $request) {
                $bookedCount = $class->bookings()->count();
                $availableSpots = $class->max_spots - $bookedCount;
                
                // Calculate duration properly handling overnight classes
                $duration = null;
                $start = null;
                $end = null;
                if (!empty($class->start_time) && !empty($class->end_time)) {
                    $classDate = $class->class_date instanceof \Carbon\Carbon ? $class->class_date->toDateString() : (string) $class->class_date;
                    $start = \Carbon\Carbon::parse(trim(($classDate ?: now()->toDateString()) . ' ' . $class->start_time));
                    $end = \Carbon\Carbon::parse(trim(($classDate ?: now()->toDateString()) . ' ' . $class->end_time));
                    
                    // Handle overnight classes
                    if ($end->lessThan($start)) {
                        $end->addDay();
                    }
                    $duration = $start->diffInMinutes($end);
                } else {
                    $duration = $class->duration ?? 60; // Default fallback
                }

                // Determine if the class (for the selected day) is in the past
                $selectedStart = null;
                if (!empty($class->start_time)) {
                    $selectedStart = \Carbon\Carbon::parse($selectedDate->toDateString() . ' ' . $class->start_time);
                }
                $isPast = $selectedStart ? $selectedStart->lessThan(now()) : false;
                
                $user = $request->user();
                $isBookedByMe = $user ? ($class->relationLoaded('bookings') ? $class->bookings->contains('user_id', $user->id) : $class->bookings()->where('user_id', $user->id)->exists()) : false;

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'start_time' => $class->start_time,
                    'end_time' => $class->end_time,
                    'duration' => $duration,
                    'class_date' => $class->class_date->toDateString(),
                    'price' => $class->price,
                    'max_spots' => $class->max_spots,
                    'booked_count' => $bookedCount,
                    'available_spots' => $availableSpots,
                    'is_past' => $isPast,
                    'members_only' => (bool) $class->members_only,
                    'instructor' => [
                        'name' => $class->instructor->name ?? 'No Instructor',
                        'initials' => substr($class->instructor->name ?? 'IN', 0, 2),
                        'photo_url' => $class->instructor && $class->instructor->photo ? asset('storage/' . $class->instructor->photo) : 'https://www.gravatar.com/avatar/?d=mp&s=100'
                    ],
                    'is_booked_by_me' => $isBookedByMe,
                ];
            }),
            'weekDays' => $weekDays,
            'selectedDate' => $selectedDate->format('l, F j'),
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'showPast' => $showPast,
        ]);
    }
}
