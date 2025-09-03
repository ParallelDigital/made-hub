<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_instructors' => \App\Models\Instructor::count(),
            'total_classes' => \App\Models\FitnessClass::count(),
            'total_bookings' => \App\Models\Booking::count(),
        ];

        // Calendar parameters
        $view = $request->get('view', 'weekly'); // weekly or monthly
        $weekOffset = (int) $request->get('week', 0); // weeks from current week
        
        // Calculate current week start (Sunday)
        $currentWeekStart = now()->startOfWeek(\Carbon\Carbon::SUNDAY)->addWeeks($weekOffset);
        
        // Get classes for calendar view
        $classes = \App\Models\FitnessClass::with('instructor')
            ->where('active', true)
            ->orderBy('start_time')
            ->get();

        if ($view === 'weekly') {
            // Weekly view: 7 days starting from Sunday
            $calendarData = $this->getWeeklyCalendarData($classes, $currentWeekStart);
            $calendarDates = collect(range(0, 6))->map(function($day) use ($currentWeekStart) {
                return $currentWeekStart->copy()->addDays($day);
            });
        } else {
            // Monthly view: full month grid
            $monthStart = $currentWeekStart->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $calendarData = $this->getMonthlyCalendarData($classes, $monthStart);
            $calendarDates = collect(range(0, 41))->map(function($day) use ($monthStart) {
                return $monthStart->copy()->addDays($day);
            });
        }

        return view('admin.dashboard', compact('stats', 'calendarData', 'calendarDates', 'view', 'weekOffset', 'currentWeekStart'));
    }

    private function getWeeklyCalendarData($classes, $weekStart)
    {
        $calendarData = collect(range(0, 6))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        foreach ($classes as $class) {
            if ($class->recurring) {
                // Handle recurring classes
                $recurringDays = json_decode($class->recurring_days, true) ?? [];
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                
                foreach ($recurringDays as $dayName) {
                    if (isset($dayMapping[$dayName])) {
                        $dayIndex = $dayMapping[$dayName];
                        $calendarData[$dayIndex]->push($class);
                    }
                }
            } else {
                // Handle one-time classes
                $classDate = \Carbon\Carbon::parse($class->class_date);
                if ($classDate->between($weekStart, $weekStart->copy()->addDays(6))) {
                    $dayIndex = $classDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
                    $calendarData[$dayIndex]->push($class);
                }
            }
        }

        return $calendarData;
    }

    private function getMonthlyCalendarData($classes, $monthStart)
    {
        $calendarData = collect(range(0, 41))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        foreach ($classes as $class) {
            if ($class->recurring) {
                // Handle recurring classes
                $recurringDays = json_decode($class->recurring_days, true) ?? [];
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                
                // For each week in the month view (6 weeks)
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
                // Handle one-time classes
                $classDate = \Carbon\Carbon::parse($class->class_date);
                $daysDiff = $monthStart->diffInDays($classDate);
                if ($daysDiff >= 0 && $daysDiff <= 41) {
                    $calendarData[$daysDiff]->push($class);
                }
            }
        }

        return $calendarData;
    }

    public function users()
    {
        $users = \App\Models\User::paginate(20);
        return view('admin.users', compact('users'));
    }

    public function reports()
    {
        $monthlyBookings = \App\Models\Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        return view('admin.reports', compact('monthlyBookings'));
    }
}
