<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FitnessClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        // Top 3 most booked classes
        $topClasses = Booking::select('fitness_class_id', DB::raw('count(*) as booking_count'))
            ->where('status', 'confirmed')
            ->groupBy('fitness_class_id')
            ->orderBy('booking_count', 'desc')
            ->limit(3)
            ->with(['fitnessClass.instructor', 'fitnessClass.classType'])
            ->get()
            ->map(function($booking) {
                return [
                    'class' => $booking->fitnessClass,
                    'booking_count' => $booking->booking_count
                ];
            });

        // User who has booked the most classes
        $topUser = Booking::select('user_id', DB::raw('count(*) as booking_count'))
            ->where('status', 'confirmed')
            ->groupBy('user_id')
            ->orderBy('booking_count', 'desc')
            ->with('user')
            ->first();

        // Calculate revenue earned
        // Revenue from paid bookings (those with stripe_session_id)
        $paidBookingsRevenue = Booking::whereNotNull('stripe_session_id')
            ->where('status', 'confirmed')
            ->join('fitness_classes', 'bookings.fitness_class_id', '=', 'fitness_classes.id')
            ->sum('fitness_classes.price');

        // Additional statistics
        $totalBookings = Booking::where('status', 'confirmed')->count();
        $totalRevenue = $paidBookingsRevenue;
        
        // Monthly breakdown for the last 6 months
        // Use SQLite-compatible date formatting
        $monthlyRevenue = Booking::whereNotNull('stripe_session_id')
            ->where('status', 'confirmed')
            ->where('booked_at', '>=', now()->subMonths(6))
            ->join('fitness_classes', 'bookings.fitness_class_id', '=', 'fitness_classes.id')
            ->select(
                DB::raw("strftime('%Y-%m', bookings.booked_at) as month"),
                DB::raw('SUM(fitness_classes.price) as revenue'),
                DB::raw('COUNT(*) as bookings')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Top 5 users by bookings
        $topUsers = Booking::select('user_id', DB::raw('count(*) as booking_count'))
            ->where('status', 'confirmed')
            ->groupBy('user_id')
            ->orderBy('booking_count', 'desc')
            ->limit(5)
            ->with('user')
            ->get();

        // Class type distribution
        $classTypeDistribution = Booking::where('status', 'confirmed')
            ->join('fitness_classes', 'bookings.fitness_class_id', '=', 'fitness_classes.id')
            ->leftJoin('class_types', 'fitness_classes.class_type_id', '=', 'class_types.id')
            ->select('class_types.name as type', DB::raw('count(*) as booking_count'))
            ->groupBy('class_types.name')
            ->orderBy('booking_count', 'desc')
            ->get();

        return view('admin.reports.index', compact(
            'topClasses',
            'topUser',
            'totalRevenue',
            'totalBookings',
            'monthlyRevenue',
            'topUsers',
            'classTypeDistribution'
        ));
    }
}
