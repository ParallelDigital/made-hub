<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\StripeWebhookController;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');

// Test email route with detailed debugging
Route::get('/test-email-send', function () {
    try {
        // Enable detailed error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Get a booking with its user; ensure the email goes to the booking's user
        $booking = \App\Models\Booking::with('user')->latest('id')->first();
        if (!$booking) {
            return 'No bookings found in the database';
        }
        try {
            $secret = config('services.stripe.secret');
            // Temporarily use live key if in test mode but live subscriptions exist
            if (config('services.stripe.mode') === 'test' && env('STRIPE_SECRET_LIVE')) {
                $secret = env('STRIPE_SECRET_LIVE');
                \Log::info('Temporarily using LIVE Stripe key for manual sync');
            }
            if (!$secret) {
                return 'Stripe secret key is not set';
            }
        } catch (\Exception $e) {
            return 'An error occurred while trying to get the Stripe secret key';
        }
        $user = $booking->user;
        if (!$user) {
            return 'The selected booking has no associated user';
        }
        
        // Generate user QR URL for logging (mailer generates its own)
        $qrUrl = URL::signedRoute('user.checkin', [
            'user' => $user->id,
            'qr_code' => $user->qr_code,
        ]);
        
        // Log the attempt
        \Log::info('Manual test email send attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'booking_id' => $booking->id,
            'qr_url' => $qrUrl
        ]);
        
        // Send the email to the user who owns the booking
        $email = new \App\Mail\BookingConfirmed($booking, $qrUrl);
        $result = \Mail::to($user->email)->send($email);
        
        // Log the result
        \Log::info('Manual test email send result', [
            'success' => $result !== null,
            'result' => $result
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test email sent to ' . $user->email,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'email_sent' => $result !== null
        ]);
        
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Manual test email error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
Route::get('/api/classes', [App\Http\Controllers\HomeController::class, 'getClasses']);
Route::get('/purchase', [App\Http\Controllers\PurchaseController::class, 'index'])->name('purchase.index');
Route::get('/purchase/package/{type}', [App\Http\Controllers\PurchaseController::class, 'showPackageCheckout'])->name('purchase.package.checkout');
Route::post('/purchase/package/{type}', [App\Http\Controllers\PurchaseController::class, 'processPackageCheckout'])->name('purchase.package.process');
Route::get('/purchase/package/{type}/success', [App\Http\Controllers\PurchaseController::class, 'packageSuccess'])->name('purchase.package.success');

// AJAX login for in-modal authentication (guests only)
Route::post('/ajax/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);
    $remember = (bool) $request->boolean('remember', false);
    if (\Illuminate\Support\Facades\Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 422);
})->middleware('guest')->name('ajax.login');

// Booking Routes
Route::post('/book-with-credits/{classId}', [App\Http\Controllers\BookingController::class, 'bookWithCredits'])->name('booking.credits');
Route::get('/checkout/{class_id}', [App\Http\Controllers\PurchaseController::class, 'showCheckoutForm'])->name('checkout.show');
Route::post('/checkout/{class_id}', [App\Http\Controllers\PurchaseController::class, 'processCheckout'])->name('booking.process-checkout');
Route::post('/cancel-booking/{bookingId}', [App\Http\Controllers\BookingController::class, 'cancel'])->name('booking.cancel');
Route::delete('/delete-booking/{bookingId}', [App\Http\Controllers\BookingController::class, 'delete'])->name('booking.delete');
Route::post('/apply-coupon', [App\Http\Controllers\PurchaseController::class, 'applyCoupon'])->name('booking.apply-coupon');
Route::get('/booking/success', [App\Http\Controllers\BookingController::class, 'success'])->name('booking.success');
Route::get('/booking/confirmation/{classId}', [App\Http\Controllers\BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/checkin/{booking}', [App\Http\Controllers\BookingController::class, 'checkin'])
    ->name('booking.checkin')
    ->middleware('signed');
Route::get('/user/checkin/{user}/{qr_code}', [App\Http\Controllers\BookingController::class, 'userCheckin'])
    ->name('user.checkin')
    ->middleware('signed');
Route::get('/qr-code/{user}', [App\Http\Controllers\UserController::class, 'generateQrCode'])
    ->name('user.qr-code')
    ->middleware('auth');

Route::get('/dashboard', function () {
    $user = Auth::user();

    if (in_array($user->role, ['admin', 'administrator'], true)) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'instructor') {
        return redirect()->route('instructor.dashboard');
    }

    // For regular users, fetch their upcoming bookings
    // Ensure the user has a PIN code (only if column exists)
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'pin_code')) {
        if (empty($user->pin_code)) {
            $user->pin_code = \App\Models\User::generateUniquePinCode();
            $user->save();
        }
    }

    $upcomingBookings = $user->bookings()
        ->whereHas('fitnessClass', function ($query) {
            $query->where('class_date', '>=', now()->toDateString());
        })
        ->where('status', 'confirmed')
        ->with('fitnessClass.instructor')
        ->get()
        ->sortBy(function($booking) {
            return $booking->fitnessClass->class_date . ' ' . $booking->fitnessClass->start_time;
        });

    // Calculate current quarter cancellation info
    $now = now();
    $quarter = ceil($now->month / 3);
    $year = $now->year;
    $quarterStart = \Carbon\Carbon::create($year, (($quarter - 1) * 3) + 1, 1);
    $quarterEnd = \Carbon\Carbon::create($year, $quarter * 3, 1)->endOfMonth();

    $cancellationsThisQuarter = \App\Models\Booking::where('user_id', $user->id)
        ->where('status', 'cancelled')
        ->whereBetween('cancelled_at', [$quarterStart, $quarterEnd])
        ->count();

    $remainingCancellations = max(0, 2 - $cancellationsThisQuarter);

    // Signed URL for user's universal check-in (based on their personal QR code)
    $userQrUrl = \Illuminate\Support\Facades\URL::signedRoute('user.checkin', [
        'user' => $user->id,
        'qr_code' => $user->qr_code,
    ]);

    // Pre-generate an SVG QR image for the dashboard (safe to embed inline on web)
    $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(220)
        ->errorCorrection('M')
        ->generate($userQrUrl);

    return view('dashboard', [
        'upcomingBookings' => $upcomingBookings,
        'userQrUrl' => $userQrUrl,
        'qrSvg' => $qrSvg,
        'remainingCancellations' => $remainingCancellations,
        'quarter' => $quarter,
        'year' => $year,
    ]);
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');
    Route::get('members', [App\Http\Controllers\Admin\UserController::class, 'members'])->name('members.index');
    Route::resource('instructors', App\Http\Controllers\Admin\InstructorController::class);
    Route::get('instructors/{instructor}/classes', [App\Http\Controllers\Admin\InstructorController::class, 'getClasses'])
        ->name('instructors.classes');
    Route::resource('classes', App\Http\Controllers\Admin\FitnessClassController::class);
    Route::get('classes/calendar-data', [App\Http\Controllers\Admin\FitnessClassController::class, 'getCalendarData'])->name('classes.calendar-data');
    Route::post('classes/{class}/delete-after-date', [App\Http\Controllers\Admin\FitnessClassController::class, 'deleteAfterDate'])->name('classes.delete-after-date');
    Route::post('classes/{class}/cancel', [App\Http\Controllers\Admin\FitnessClassController::class, 'cancel'])->name('classes.cancel');
    Route::get('memberships/export', [App\Http\Controllers\Admin\MembershipController::class, 'export'])->name('memberships.export');
    Route::resource('memberships', App\Http\Controllers\Admin\MembershipController::class);
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);
    // Admin bookings list
    Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('bookings/{booking}/resend-confirmation', [App\Http\Controllers\Admin\BookingController::class, 'resendConfirmation'])->name('bookings.resend-confirmation');
    Route::get('users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/credits/add', [App\Http\Controllers\Admin\UserController::class, 'addCredits'])->name('users.credits.add');
    Route::get('reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports');
    Route::post('members/ensure-login-access', function () {
        \Artisan::call('members:ensure-login-access');
        return back()->with('success', 'Login access check completed. Password reset emails sent to members who need them.');
    })->name('members.ensure-login-access');
    Route::post('members/ensure-subscription-users', function () {
        \Artisan::call('members:ensure-subscription-users');
        return back()->with('success', 'Subscription users check completed. All subscription holders now have proper user accounts.');
    })->name('members.ensure-subscription-users');
    Route::post('members/verify-accounts', function () {
        \Artisan::call('members:verify-accounts');
        return back()->with('success', 'Membership accounts verification completed. All membership accounts are properly configured.');
    })->name('members.verify-accounts');
    Route::post('members/create-account', function (Request $request) {
        $email = $request->input('email');
        $name = $request->input('name', 'Member');
        $sendReset = $request->has('send_reset');
        
        $command = "members:create-account {$email}";
        if ($name && $name !== 'Member') {
            $command .= " --name=\"{$name}\"";
        }
        if ($sendReset) {
            $command .= " --send-reset";
        }
        
        \Artisan::call($command);
        return back()->with('success', "Member account created for {$email}.");
    })->name('members.create-account');
    Route::post('members/sync-stripe-members', function () {
        try {
            $secret = config('services.stripe.secret');
            // Temporarily use live key if in test mode but live subscriptions exist
            if (config('services.stripe.mode') === 'test' && env('STRIPE_SECRET_LIVE')) {
                $secret = env('STRIPE_SECRET_LIVE');
                \Log::info('Temporarily using LIVE Stripe key for manual sync');
            }
            if (!$secret) {
                return back()->with('error', 'Stripe secret key not configured');
            }

            $stripe = new \Stripe\StripeClient($secret);
            $allSubs = collect();
            $params = ['limit' => 100, 'expand' => ['data.customer']];
            do {
                $resp = $stripe->subscriptions->all($params);
                $data = collect($resp->data ?? []);
                $allSubs = $allSubs->merge($data);
                if (($resp->has_more ?? false) && $data->isNotEmpty()) {
                    $params['starting_after'] = $data->last()->id;
                } else {
                    break;
                }
            } while (true);

            $created = 0;
            $updated = 0;

            foreach ($allSubs as $sub) {
                $customer = $sub->customer;
                if (!is_object($customer) || empty($customer->email)) {
                    continue;
                }

                $email = $customer->email;
                $name = is_object($customer) ? ($customer->name ?? 'Member') : 'Member';
                $status = $sub->status;
                if ($status === 'past_due') {
                    $status = 'inactive';
                }

                $existingUser = \App\Models\User::where('email', $email)->first();
                
                if (!$existingUser) {
                    \App\Models\User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => bcrypt('temporary_password_' . time() . rand(1000, 9999)),
                        'role' => 'member',
                        'stripe_subscription_id' => $sub->id,
                        'subscription_status' => $status,
                        'email_verified_at' => now(),
                        'credits' => ($status === 'active' || $status === 'trialing') ? 5 : 0, // 5 credits for active members
                    ]);
                    
                    try {
                        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send password reset email to {$email}: " . $e->getMessage());
                    }
                    
                    $created++;
                } else {
                    $updatedData = false;
                    if (empty($existingUser->stripe_subscription_id)) {
                        $existingUser->stripe_subscription_id = $sub->id;
                        $updatedData = true;
                    }
                    if (empty($existingUser->subscription_status) || $existingUser->subscription_status !== $status) {
                        $existingUser->subscription_status = $status;
                        $updatedData = true;
                    }
                    if (!$existingUser->email_verified_at) {
                        $existingUser->email_verified_at = now();
                        $updatedData = true;
                    }
                    // Give 5 credits to active members who don't have them
                    if (($status === 'active' || $status === 'trialing') && $existingUser->credits < 5) {
                        $existingUser->credits = 5;
                        $updatedData = true;
                    }
                    
                    if ($updatedData) {
                        $existingUser->save();
                        $updated++;
                    }
                }
            }

            return back()->with('success', "Stripe members sync completed! Created: {$created}, Updated: {$updated}");
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    })->name('members.sync-stripe-members');
});

// Instructor Routes
Route::middleware(['auth', \App\Http\Middleware\IsInstructor::class])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\InstructorDashboardController::class, 'index'])->name('dashboard');
    Route::get('classes/{class}/members', [App\Http\Controllers\InstructorDashboardController::class, 'showMembers'])->name('classes.members');
    Route::get('classes/{class}/scanner', [App\Http\Controllers\InstructorDashboardController::class, 'showScanner'])->name('classes.scanner');
    Route::post('classes/{class}/scan', [App\Http\Controllers\InstructorDashboardController::class, 'processQrScan'])->name('classes.scan');
});

require __DIR__.'/auth.php';

// Stripe Webhook Endpoint (CSRF exempt)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
