<?php

namespace App\Http\Controllers;

use App\Models\FitnessClass;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Eager load the instructor and their upcoming classes with bookings
        $user->load(['instructor.fitnessClasses' => function ($query) {
            $query->where('class_date', '>=', now()->toDateString())
                  ->with('bookings.user')
                  ->orderBy('class_date')
                  ->orderBy('start_time');
        }]);

        $upcomingClasses = $user->instructor ? $user->instructor->fitnessClasses : collect();

        return view('instructor.dashboard', [
            'upcomingClasses' => $upcomingClasses,
        ]);
    }

    public function showMembers(FitnessClass $class)
    {
        // Ensure the logged-in instructor is authorized to see this class's members
        $instructorId = Auth::user()->instructor->id;
        if ($class->instructor_id !== $instructorId) {
            abort(403, 'Unauthorized action.');
        }

        $class->load('bookings.user');

        return view('instructor.classes.members', [
            'class' => $class,
            'members' => $class->bookings,
        ]);
    }

    /**
     * Show QR scanner interface for a class
     */
    public function showScanner(FitnessClass $class)
    {
        // Ensure the logged-in instructor is authorized to scan for this class
        $instructorId = Auth::user()->instructor->id;
        if ($class->instructor_id !== $instructorId) {
            abort(403, 'Unauthorized action.');
        }

        return view('instructor.scanner', compact('class'));
    }

    /**
     * Process QR code scan and check in user
     */
    public function processQrScan(Request $request, FitnessClass $class)
    {
        // Ensure the logged-in instructor is authorized
        $instructorId = Auth::user()->instructor->id;
        if ($class->instructor_id !== $instructorId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'qr_code' => 'required|string'
        ]);

        // Find user by QR code
        $user = User::where('qr_code', $request->qr_code)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid QR code. User not found.'
            ]);
        }

        // Check if user has a booking for this class
        $booking = Booking::where('user_id', $user->id)
            ->where('fitness_class_id', $class->id)
            ->where('status', 'confirmed')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => "{$user->name} is not booked for this class.",
                'user_name' => $user->name
            ]);
        }

        // Check if already checked in
        if ($booking->attended) {
            return response()->json([
                'success' => false,
                'message' => "{$user->name} has already been checked in at " . $booking->checked_in_at->format('g:i A'),
                'user_name' => $user->name,
                'already_checked_in' => true
            ]);
        }

        // Check in the user
        $booking->update([
            'attended' => true,
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$user->name} successfully checked in!",
            'user_name' => $user->name,
            'checked_in_at' => now()->format('g:i A')
        ]);
    }
}
