<?php

namespace App\Http\Controllers;

use App\Models\FitnessClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get selected date or default to today
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $showPast = $request->boolean('show_past', false);
        
        // Get current week's classes based on selected date (Monday to Sunday)
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $selectedDate->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Get selected date's classes for the schedule display
        $selectedDateString = $selectedDate->toDateString();
        $dayOfWeek = $selectedDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        
        // Fetch exact-date classes OR recurring templates, then filter recurring by selected day in PHP (DB-agnostic)
        $selectedDateClasses = FitnessClass::with(['instructor', 'bookings'])
            ->where('active', 1)
            ->where(function($query) use ($selectedDateString) {
                $query->whereDate('class_date', $selectedDateString)
                      ->orWhere('recurring', 1);
            })
            ->orderBy('start_time')
            ->get()
            ->filter(function($class) use ($selectedDate) {
                $classDate = $class->class_date instanceof \Carbon\Carbon ? $class->class_date->toDateString() : (string) $class->class_date;
                if ($classDate === $selectedDate->toDateString()) {
                    return true; // exact date
                }
                if ($class->recurring) {
                    $days = $this->parseRecurringDays($class->recurring_days);
                    $dayName = strtolower($selectedDate->format('l'));
                    return in_array($dayName, $days, true);
                }
                return false;
            });

        // Deduplicate: prefer a concrete class scheduled on this exact date over a recurring template
        $selectedDateClasses = $this->dedupeClassesForSelectedDate($selectedDateClasses, $selectedDate);
        
        // Show all classes (past and future) on the homepage for the selected date
        // No filtering applied here; frontend will label past classes appropriately
        
        // Get week data for navigation (Monday–Sunday)
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'day' => $date->format('D'),
                'date' => $date->format('j'),
                'full_date' => $date->format('Y-m-d'),
                'is_today' => $date->isToday(),
                'is_selected' => $date->isSameDay($selectedDate)
            ];
        }
        
        // Navigation dates
        $prevWeek = $startOfWeek->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $startOfWeek->copy()->addWeek()->format('Y-m-d');
        
        return view('welcome', compact('selectedDateClasses', 'weekDays', 'selectedDate', 'prevWeek', 'nextWeek', 'showPast'));
    }

    public function getClasses(Request $request)
    {
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $showPast = $request->boolean('show_past', false);
        
        // Get current week's classes based on selected date (Monday to Sunday)
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        
        // Get selected date's classes for the schedule display
        $selectedDateString = $selectedDate->toDateString();
        $dayOfWeek = $selectedDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        
        // Get both exact date matches and recurring classes for this day of week
        $selectedDateClasses = FitnessClass::with(['instructor', 'bookings'])
            ->where('active', 1)
            ->where(function($query) use ($selectedDateString, $dayOfWeek) {
                // Exact date match
                $query->whereDate('class_date', $selectedDateString)
                      // OR recurring classes that match this day of week
                      ->orWhere(function($subQuery) use ($dayOfWeek) {
                          $subQuery->where('recurring', 1)
                                   ->whereRaw("strftime('%w', class_date) = ?", [(string)$dayOfWeek]); // SQLite strftime %w is 0-based (0=Sunday)
                      });
            })
            ->orderBy('start_time')
            ->get();


        // Homepage calendar: show ALL classes (past and future)
        // No filtering applied - users see complete schedule



        
        // Get week data for navigation (Monday–Sunday)
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'day' => $date->format('D'),
                'date' => $date->format('j'),
                'full_date' => $date->format('Y-m-d'),
                'is_today' => $date->isToday(),
                'is_selected' => $date->isSameDay($selectedDate)
            ];
        }
        
        // Navigation dates
        $prevWeek = $startOfWeek->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $startOfWeek->copy()->addWeek()->format('Y-m-d');


        return response()->json([
            'classes' => $selectedDateClasses->map(function($class) use ($selectedDate, $request) {
                $bookedCount = $class->bookings()
                    ->where('booking_date', $selectedDate->toDateString())
                    ->where('status', 'confirmed')
                    ->count();
                $availableSpots = $class->max_spots - $bookedCount;
                
                // Calculate duration properly handling overnight classes
                $duration = null;
                $start = null;
                $end = null;
                if (!empty($class->start_time) && !empty($class->end_time)) {
                    $classDate = $class->class_date instanceof \Carbon\Carbon ? $class->class_date->toDateString() : (string) $class->class_date;
                    $start = \Carbon\Carbon::parse(trim(($classDate ?: now()->toDateString()) . ' ' . $class->start_time));
                    $end = \Carbon\Carbon::parse(trim(($classDate ?: now()->toDateString()) . ' ' . $class->end_time));
                    
                    // Handle overnight classes
                    if ($end->lessThan($start)) {
                        $end->addDay();
                    }
                    $duration = $start->diffInMinutes($end);
                } else {
                    $duration = $class->duration ?? 60; // Default fallback
                }

                // Determine if the class (for the selected day) is in the past
                $selectedStart = null;
                if (!empty($class->start_time)) {
                    $tz = 'Europe/London';
                    $dateString = \Carbon\Carbon::parse($selectedDate)->setTimezone($tz)->toDateString();
                    $selectedStart = \Carbon\Carbon::parse($dateString . ' ' . $class->start_time, $tz);
                }
                $isPast = $selectedStart ? $selectedStart->lessThan(\Carbon\Carbon::now('Europe/London')) : false;
                
                $user = $request->user();
                $isBookedByMe = $user ? $class->bookings()
                    ->where('user_id', $user->id)
                    ->where('booking_date', $selectedDate->toDateString())
                    ->where('status', 'confirmed')
                    ->exists() : false;

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'description' => $class->description,
                    'start_time' => $class->start_time,
                    'end_time' => $class->end_time,
                    'duration' => $duration,
                    'class_date' => $class->class_date->toDateString(),
                    'price' => $class->price,
                    'max_spots' => $class->max_spots,
                    'booked_count' => $bookedCount,
                    'available_spots' => $availableSpots,
                    'is_past' => $isPast,
                    'members_only' => (bool) $class->members_only,
                    'instructor' => [
                        'name' => $class->instructor->name ?? 'No Instructor',
                        'initials' => substr($class->instructor->name ?? 'IN', 0, 2),
                        'photo_url' => $class->instructor && $class->instructor->photo ? asset('storage/' . $class->instructor->photo) : 'https://www.gravatar.com/avatar/?d=mp&s=100'
                    ],
                    'is_booked_by_me' => $isBookedByMe,
                ];
            }),
            'weekDays' => $weekDays,
            'selectedDate' => $selectedDate->format('l, F j'),
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'showPast' => $showPast,
        ]);
    }

    /**
     * Deduplicate classes for a selected date to avoid showing both a recurring template
     * and a concrete dated class at the same time slot. Preference order:
     * 1) Exact-date match; 2) Child instance (has parent_class_id); 3) First item.
     */
    private function dedupeClassesForSelectedDate($classes, Carbon $selectedDate)
    {
        if (!$classes) {
            return collect();
        }

        $grouped = collect($classes)->groupBy(function($c) {
            // Visual identity: collapse classes with same name and normalized time
            $name = strtolower(trim((string) ($c->name ?? '')));
            $timeKey = $this->normalizeTimeKey((string) ($c->start_time ?? ''));
            return $name.'|'.$timeKey;
        });

        $result = collect();
        foreach ($grouped as $key => $group) {
            $choice = $group->first();

            // Prefer exact-date match for the selected day
            $exact = $group->first(function($c) use ($selectedDate) {
                $date = $c->class_date;
                if ($date instanceof \Carbon\Carbon) {
                    return $date->isSameDay($selectedDate);
                }
                try {
                    return \Carbon\Carbon::parse($date)->isSameDay($selectedDate);
                } catch (\Throwable $e) {
                    return false;
                }
            });
            if ($exact) {
                $choice = $exact;
            } else {
                // Otherwise prefer a child instance over a recurring template
                $child = $group->first(function($c) {
                    return !is_null($c->parent_class_id);
                });
                if ($child) {
                    $choice = $child;
                }
            }

            $result->push($choice);
        }

        return $result->sortBy('start_time')->values();
    }

    /**
     * Normalize a time string (HH:mm or HH:mm:ss) to HH:mm for stable grouping keys
     */
    private function normalizeTimeKey(string $time): string
    {
        if ($time === '') {
            return '';
        }
        try {
            // Try Carbon parse to be safe
            $t = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            return $t ? $t->format('H:i') : substr($time, 0, 5);
        } catch (\Throwable $e) {
            // Fallback: trim to first 5 chars if format unknown
            return substr($time, 0, 5);
        }
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
}
