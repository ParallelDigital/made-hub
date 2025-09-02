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
        // For demo: distribute classes across the week
        return $classes->groupBy(function($class, $index) {
            return $index % 7; // Distribute across 7 days
        });
    }

    private function getMonthlyCalendarData($classes, $monthStart)
    {
        // For demo: distribute classes across the month
        return $classes->groupBy(function($class, $index) {
            return $index % 42; // Distribute across 42 days (6 weeks)
        });
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
