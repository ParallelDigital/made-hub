<?php

namespace App\Http\Controllers;

use App\Models\FitnessClass;
use Illuminate\Support\Facades\Auth;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Eager load the instructor and their upcoming classes
        $user->load(['instructor.fitnessClasses' => function ($query) {
            $query->where('class_date', '>=', now()->toDateString())
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
}
