<?php

namespace App\Http\Controllers;

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
}
