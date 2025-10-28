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

        // Get recent bookings for dashboard
        $recentBookings = \App\Models\Booking::with(['user', 'fitnessClass'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

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

        return view('admin.dashboard', compact('stats', 'calendarData', 'calendarDates', 'view', 'weekOffset', 'currentWeekStart', 'recentBookings'));
    }

    private function getWeeklyCalendarData($classes, $weekStart)
    {
        $calendarData = collect(range(0, 6))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        // Prefer concrete, date-specific instances (children) over recurring templates (parents)
        if (method_exists($classes, 'sortBy')) {
            $classes = $classes->sortBy(function($c) {
                return is_null($c->parent_class_id) ? 1 : 0; // children first
            });
        }

        // Track seen class keys per day index to avoid duplicates
        $seen = array_fill(0, 7, []);

        foreach ($classes as $class) {
            if ($class->recurring) {
                // Handle recurring classes
                $recurringDays = $this->parseRecurringDays($class->recurring_days);
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                
                foreach ($recurringDays as $dayName) {
                    if (isset($dayMapping[$dayName])) {
                        $dayIndex = $dayMapping[$dayName];
                        $key = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                        if (!isset($seen[$dayIndex][$key])) {
                            $calendarData[$dayIndex]->push($class);
                            $seen[$dayIndex][$key] = true;
                        }
                    }
                }
            } else {
                // Handle one-time classes
                $classDate = \Carbon\Carbon::parse($class->class_date);
                if ($classDate->between($weekStart, $weekStart->copy()->addDays(6))) {
                    $dayIndex = $classDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
                    $key = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                    if (!isset($seen[$dayIndex][$key])) {
                        $calendarData[$dayIndex]->push($class);
                        $seen[$dayIndex][$key] = true;
                    }
                }
            }
        }

        return $calendarData;
    }

    private function getMonthlyCalendarData($classes, $monthStart)
    {
        $calendarData = collect(range(0, 41))->mapWithKeys(function($day) {
            return [$day => collect()];
        });

        // Prefer concrete, date-specific instances (children) over recurring templates (parents)
        if (method_exists($classes, 'sortBy')) {
            $classes = $classes->sortBy(function($c) {
                return is_null($c->parent_class_id) ? 1 : 0; // children first
            });
        }

        // Track seen class keys per day index to avoid duplicates
        $seen = array_fill(0, 42, []);

        foreach ($classes as $class) {
            if ($class->recurring) {
                // Handle recurring classes - add once per recurring day in the month
                $recurringDays = $this->parseRecurringDays($class->recurring_days);
                $dayMapping = [
                    'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6
                ];
                
                foreach ($recurringDays as $dayName) {
                    if (isset($dayMapping[$dayName])) {
                        $dayOfWeek = $dayMapping[$dayName];
                        $keyTemplate = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                        // Add the class to every occurrence of this day in the month, avoiding duplicates
                        for ($dayIndex = $dayOfWeek; $dayIndex <= 41; $dayIndex += 7) {
                            if (!isset($seen[$dayIndex][$keyTemplate])) {
                                $calendarData[$dayIndex]->push($class);
                                $seen[$dayIndex][$keyTemplate] = true;
                            }
                        }
                    }
                }
            } else {
                // Handle one-time classes
                $classDate = \Carbon\Carbon::parse($class->class_date);
                $daysDiff = $monthStart->diffInDays($classDate);
                if ($daysDiff >= 0 && $daysDiff <= 41) {
                    $key = strtolower(trim($class->name)).'|'.$class->start_time.'|'.(string)($class->instructor_id ?? '0').'|'.strtolower(trim($class->location ?? ''));
                    if (!isset($seen[$daysDiff][$key])) {
                        $calendarData[$daysDiff]->push($class);
                        $seen[$daysDiff][$key] = true;
                    }
                }
            }
        }

        return $calendarData;
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

    public function sendWelcomeEmails()
    {
        $users = \App\Models\User::whereNotNull('membership_start_date')
            ->whereNotIn('role', ['admin', 'administrator', 'instructor'])
            ->orderBy('name')
            ->get();

        $sentCount = 0;
        $failedCount = 0;
        $sentEmails = [];
        $failedEmails = [];

        foreach ($users as $user) {
            if (empty($user->email)) {
                continue;
            }

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\MemberWelcome($user, 5));
                $sentEmails[] = $user->email;
                $sentCount++;
                
                // Small delay to avoid overwhelming mail server
                usleep(500000); // 0.5 second delay
            } catch (\Exception $e) {
                $failedEmails[] = $user->email;
                $failedCount++;
                \Log::error('Failed to send welcome email to ' . $user->email . ': ' . $e->getMessage());
            }
        }

        $message = "Successfully sent {$sentCount} welcome emails to members.";
        if ($failedCount > 0) {
            $message .= " {$failedCount} emails failed to send.";
        }

        return redirect()->route('admin.dashboard')
            ->with('success', $message)
            ->with('sent_emails', $sentEmails)
            ->with('failed_emails', $failedEmails)
            ->with('sent_count', $sentCount)
            ->with('failed_count', $failedCount);
    }

    public function resetMemberPasswords()
    {
        $newPassword = 'Made2025!';
        $hashedPassword = \Illuminate\Support\Facades\Hash::make($newPassword);

        // Only get members with active subscriptions (membership_start_date is set)
        $users = \App\Models\User::whereNotNull('membership_start_date')
            ->whereNotIn('role', ['admin', 'administrator', 'instructor'])
            ->orderBy('name')
            ->get();

        $updatedCount = 0;
        $updatedUsers = [];

        foreach ($users as $user) {
            $user->password = $hashedPassword;
            $user->save();
            
            $updatedUsers[] = $user->name . ' (' . $user->email . ') - ✅ Member';
            $updatedCount++;
        }

        $message = "Successfully reset passwords for {$updatedCount} members with active subscriptions to 'Made2025!'";

        return redirect()->route('admin.dashboard')
            ->with('success', $message)
            ->with('updated_users', $updatedUsers)
            ->with('updated_count', $updatedCount);
    }

    public function createMemberAccounts()
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            // Find or create "member" membership
            $memberMembership = \App\Models\Membership::where('name', 'member')->first();
            
            if (!$memberMembership) {
                // Create default member membership if it doesn't exist
                $memberMembership = \App\Models\Membership::create([
                    'name' => 'member',
                    'description' => 'Monthly Member',
                    'price' => 0,
                    'duration_days' => 30,
                    'class_credits' => 5,
                    'unlimited' => false,
                    'active' => true,
                ]);
            }
            
            $createdCount = 0;
            $updatedCount = 0;
            $createdUsers = [];
            $updatedUsers = [];
            $newPassword = 'Made2025!';
            $hashedPassword = \Illuminate\Support\Facades\Hash::make($newPassword);

            // Fetch all active subscriptions from Stripe
            $subscriptions = \Stripe\Subscription::all([
                'status' => 'active',
                'limit' => 100,
            ]);

            foreach ($subscriptions->data as $subscription) {
                // Get customer details
                $customer = \Stripe\Customer::retrieve($subscription->customer);
                
                if (!$customer->email) {
                    continue;
                }

                // Check if user already exists
                $existingUser = \App\Models\User::where('email', $customer->email)->first();
                
                if ($existingUser) {
                    // Update existing user to ensure they have proper membership access
                    $existingUser->membership_start_date = $existingUser->membership_start_date ?? \Carbon\Carbon::createFromTimestamp($subscription->current_period_start);
                    $existingUser->stripe_customer_id = $customer->id;
                    $existingUser->stripe_subscription_id = $subscription->id;
                    $existingUser->email_verified_at = $existingUser->email_verified_at ?? now();
                    
                    // Ensure role is 'subscriber' (convert from 'user' or 'member')
                    if (in_array($existingUser->role, ['user', 'member'])) {
                        $existingUser->role = 'subscriber';
                    }
                    
                    // Assign member membership
                    $existingUser->membership_id = $memberMembership->id;
                    
                    $existingUser->save();
                    
                    $updatedUsers[] = $existingUser->name . ' (' . $existingUser->email . ') - ✅ Updated with subscriber role & member membership';
                    $updatedCount++;
                    continue;
                }

                // Create new user account
                $user = \App\Models\User::create([
                    'name' => $customer->name ?? $customer->email,
                    'email' => $customer->email,
                    'password' => $hashedPassword,
                    'role' => 'subscriber',
                    'membership_id' => $memberMembership->id,
                    'membership_start_date' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_start),
                    'stripe_customer_id' => $customer->id,
                    'stripe_subscription_id' => $subscription->id,
                    'email_verified_at' => now(),
                ]);

                $createdUsers[] = $user->name . ' (' . $user->email . ') - ✅ Account created with subscriber role & member membership';
                $createdCount++;
            }

            $message = "Synced Stripe subscriptions: Created {$createdCount} new accounts, updated {$updatedCount} existing accounts.";

            return redirect()->route('admin.dashboard')
                ->with('success', $message)
                ->with('created_users', $createdUsers)
                ->with('updated_users', $updatedUsers)
                ->with('created_count', $createdCount)
                ->with('updated_count', $updatedCount);

        } catch (\Exception $e) {
            \Log::error('Failed to sync member accounts from Stripe: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to sync member accounts: ' . $e->getMessage());
        }
    }

    public function migrateToSubscriberRole()
    {
        // Find or create "member" membership
        $memberMembership = \App\Models\Membership::where('name', 'member')->first();
        
        if (!$memberMembership) {
            // Create default member membership if it doesn't exist
            $memberMembership = \App\Models\Membership::create([
                'name' => 'member',
                'description' => 'Monthly Member',
                'price' => 0,
                'duration_days' => 30,
                'class_credits' => 5,
                'unlimited' => false,
                'active' => true,
            ]);
        }

        // Get all users with 'user' or 'member' role (excluding admins and instructors)
        $users = \App\Models\User::whereIn('role', ['user', 'member'])
            ->orderBy('name')
            ->get();

        $migratedCount = 0;
        $migratedUsers = [];

        foreach ($users as $user) {
            $oldRole = $user->role;
            $user->role = 'subscriber';
            
            // Assign member membership if they have a subscription
            if ($user->membership_start_date) {
                $user->membership_id = $memberMembership->id;
            }
            
            $user->save();
            
            $memberStatus = $user->membership_start_date ? '✅ Member with membership' : '👤 User';
            $migratedUsers[] = $user->name . ' (' . $user->email . ') - ' . $oldRole . ' → subscriber - ' . $memberStatus;
            $migratedCount++;
        }

        $message = "Successfully migrated {$migratedCount} users to 'subscriber' role with 'member' membership.";

        return redirect()->route('admin.dashboard')
            ->with('success', $message)
            ->with('migrated_users', $migratedUsers)
            ->with('migrated_count', $migratedCount);
    }

    public function resetUserPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('admin.dashboard')
                ->with('error', "User with email {$request->email} not found.");
        }

        $newPassword = 'Made2025!';
        $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
        $user->save();

        return redirect()->route('admin.dashboard')
            ->with('success', "Password reset successfully for {$user->name} ({$user->email}) to 'Made2025!'");
    }
}
