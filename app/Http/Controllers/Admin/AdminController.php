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

        // Get recent bookings for dashboard
        $recentBookings = \App\Models\Booking::with(['user', 'fitnessClass'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

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

        return view('admin.dashboard', compact('stats', 'calendarData', 'calendarDates', 'view', 'weekOffset', 'currentWeekStart', 'recentBookings'));
    }

    private function getWeeklyCalendarData($classes, $weekStart)
    {
        $calendarData = collect(range(0, 6))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        // Prefer concrete, date-specific instances (children) over recurring templates (parents)
        if (method_exists($classes, 'sortBy')) {
            $classes = $classes->sortBy(function($c) {
                return is_null($c->parent_class_id) ? 1 : 0; // children first
            });
        }

        // Track seen class keys per day index to avoid duplicates
        $seen = array_fill(0, 7, []);

        foreach ($classes as $class) {
            if ($class->recurring) {
                // Handle recurring classes
                $recurringDays = $this->parseRecurringDays($class->recurring_days);
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                
                foreach ($recurringDays as $dayName) {
                    if (isset($dayMapping[$dayName])) {
                        $dayIndex = $dayMapping[$dayName];
                        $key = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                        if (!isset($seen[$dayIndex][$key])) {
                            $calendarData[$dayIndex]->push($class);
                            $seen[$dayIndex][$key] = true;
                        }
                    }
                }
            } else {
                // Handle one-time classes
                $classDate = \Carbon\Carbon::parse($class->class_date);
                if ($classDate->between($weekStart, $weekStart->copy()->addDays(6))) {
                    $dayIndex = $classDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
                    $key = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                    if (!isset($seen[$dayIndex][$key])) {
                        $calendarData[$dayIndex]->push($class);
                        $seen[$dayIndex][$key] = true;
                    }
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

        // Prefer concrete, date-specific instances (children) over recurring templates (parents)
        if (method_exists($classes, 'sortBy')) {
            $classes = $classes->sortBy(function($c) {
                return is_null($c->parent_class_id) ? 1 : 0; // children first
            });
        }

        // Track seen class keys per day index to avoid duplicates
        $seen = array_fill(0, 42, []);

        foreach ($classes as $class) {
            if ($class->recurring) {
                // Handle recurring classes - add once per recurring day in the month
                $recurringDays = $this->parseRecurringDays($class->recurring_days);
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                
                foreach ($recurringDays as $dayName) {
                    if (isset($dayMapping[$dayName])) {
                        $dayOfWeek = $dayMapping[$dayName];
                        $keyTemplate = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                        // Add the class to every occurrence of this day in the month, avoiding duplicates
                        for ($dayIndex = $dayOfWeek; $dayIndex <= 41; $dayIndex += 7) {
                            if (!isset($seen[$dayIndex][$keyTemplate])) {
                                $calendarData[$dayIndex]->push($class);
                                $seen[$dayIndex][$keyTemplate] = true;
                            }
                        }
                    }
                }
            } else {
                // Handle one-time classes
                $classDate = \Carbon\Carbon::parse($class->class_date);
                $daysDiff = $monthStart->diffInDays($classDate);
                if ($daysDiff >= 0 && $daysDiff <= 41) {
                    $key = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                    if (!isset($seen[$daysDiff][$key])) {
                        $calendarData[$daysDiff]->push($class);
                        $seen[$daysDiff][$key] = true;
                    }
                }
            }
        }

        return $calendarData;
    }

    /**
     * Parse recurring days from either JSON or comma-separated string.
     * Returns an array of lowercase day names.
     */
    private function parseRecurringDays($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map(function ($s) {
                return strtolower(trim((string) $s));
            }, $raw)));
        }
        if (is_string($raw)) {
            $trim = trim($raw);
            if ($trim === '') {
                return [];
            }
            $decoded = json_decode($trim, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(function ($s) {
                    return strtolower(trim((string) $s));
                }, $decoded)));
            }
            // Fallback to comma-separated values
            return array_values(array_filter(array_map(function ($s) {
                return strtolower(trim((string) $s));
            }, explode(',', $trim))));
        }
        return [];
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
