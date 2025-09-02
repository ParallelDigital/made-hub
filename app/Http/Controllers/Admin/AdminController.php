<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_instructors' => \App\Models\Instructor::count(),
            'total_classes' => \App\Models\FitnessClass::count(),
            'total_bookings' => \App\Models\Booking::count(),
        ];

        return view('admin.dashboard', compact('stats'));
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
