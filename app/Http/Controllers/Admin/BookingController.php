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
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'booked_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Build the query with relationships
        $query = Booking::with(['user', 'fitnessClass']);

        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('fitnessClass', function($classQuery) use ($search) {
                    $classQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // Apply sorting
        switch ($sortBy) {
            case 'user_name':
                $query->join('users', 'bookings.user_id', '=', 'users.id')
                      ->select('bookings.*')
                      ->orderBy('users.name', $sortOrder);
                break;
            case 'class_name':
                $query->join('fitness_classes', 'bookings.fitness_class_id', '=', 'fitness_classes.id')
                      ->select('bookings.*')
                      ->orderBy('fitness_classes.name', $sortOrder);
                break;
            case 'status':
                $query->orderBy('status', $sortOrder);
                break;
            case 'booked_at':
            default:
                $query->orderBy('booked_at', $sortOrder === 'asc' ? 'asc' : 'desc');
                break;
        }

        $bookings = $query->paginate(20)->appends($request->query());

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
     * Delete the specified booking.
     */
    public function destroy(Booking $booking)
    {
        try {
            $userName = $booking->user->name;
            $className = $booking->fitnessClass->name;
            $classDate = $booking->fitnessClass->class_date->format('M j, Y');
            
            $booking->delete();

            return redirect()->route('admin.bookings.index')
                ->with('success', "Booking for {$userName} in {$className} on {$classDate} has been permanently deleted.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete booking ID: ' . $booking->id, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete booking. Please try again.');
        }
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
