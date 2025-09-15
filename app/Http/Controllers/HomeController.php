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
        
        // Get current week's classes based on selected date
        $startOfWeek = $selectedDate->copy()->startOfWeek();
        $endOfWeek = $selectedDate->copy()->endOfWeek();
        
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
        
        // Get week data for navigation
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
        
        return view('welcome', compact('selectedDateClasses', 'weekDays', 'selectedDate', 'prevWeek', 'nextWeek'));
    }

    public function getClasses(Request $request)
    {
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        
        // Get current week's classes based on selected date
        $startOfWeek = $selectedDate->copy()->startOfWeek();
        
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
        
        // Get week data for navigation
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
            'classes' => $selectedDateClasses->map(function($class) {
                $bookedCount = $class->bookings()->count();
                $availableSpots = $class->max_spots - $bookedCount;
                
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'start_time' => $class->start_time,
                    'end_time' => $class->end_time,
                    'class_date' => $class->class_date->toDateString(),
                    'price' => $class->price,
                    'max_spots' => $class->max_spots,
                    'booked_count' => $bookedCount,
                    'available_spots' => $availableSpots,
                    'instructor' => [
                        'name' => $class->instructor->name ?? 'No Instructor',
                        'initials' => substr($class->instructor->name ?? 'IN', 0, 2),
                        'photo_url' => $class->instructor && $class->instructor->photo ? asset('storage/' . $class->instructor->photo) : 'https://www.gravatar.com/avatar/?d=mp&s=100'
                    ]
                ];
            }),
            'weekDays' => $weekDays,
            'selectedDate' => $selectedDate->format('l, F j'),
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek
        ]);
    }
}
