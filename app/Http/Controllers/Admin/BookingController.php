<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the bookings.
     */
    public function index(Request $request)
    {
        $bookings = Booking::with(['user', 'fitnessClass'])
            ->orderByDesc('booked_at')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.bookings.index', compact('bookings'));
    }
}
