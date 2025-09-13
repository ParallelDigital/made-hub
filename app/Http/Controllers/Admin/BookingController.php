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

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'fitnessClass.instructor']);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled',
        ]);

        $booking->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Booking status updated successfully.');
    }

    /**
     * Resend the booking confirmation email.
     */
    public function resendConfirmation(Booking $booking)
    {
        try {
            // The Mailable will handle its own data loading, so we just pass the booking.
            \Illuminate\Support\Facades\Mail::to($booking->user->email)->send(new \App\Mail\BookingConfirmed($booking));

            return redirect()->back()->with('success', 'Booking confirmation email has been resent successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to resend booking confirmation for booking ID: ' . $booking->id, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to resend email. Please check the logs for details.');
        }
    }
}
