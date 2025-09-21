<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
        ->with('fitnessClass.instructor')
        ->get()
        ->sortBy(function($booking) {
            return $booking->fitnessClass->class_date . ' ' . $booking->fitnessClass->start_time;
        });

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
    Route::resource('instructors', App\Http\Controllers\Admin\InstructorController::class);
    Route::get('instructors/{instructor}/classes', [App\Http\Controllers\Admin\InstructorController::class, 'getClasses'])
        ->name('instructors.classes');
    Route::resource('classes', App\Http\Controllers\Admin\FitnessClassController::class);
    Route::get('classes/calendar-data', [App\Http\Controllers\Admin\FitnessClassController::class, 'getCalendarData'])->name('classes.calendar-data');
    Route::post('classes/{class}/delete-after-date', [App\Http\Controllers\Admin\FitnessClassController::class, 'deleteAfterDate'])->name('classes.delete-after-date');
    Route::get('memberships/export', [App\Http\Controllers\Admin\MembershipController::class, 'export'])->name('memberships.export');
    Route::resource('memberships', App\Http\Controllers\Admin\MembershipController::class);
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);
    // Admin bookings list
    Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show', 'update']);
    Route::post('bookings/{booking}/resend-confirmation', [App\Http\Controllers\Admin\BookingController::class, 'resendConfirmation'])->name('bookings.resend-confirmation');
    Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/credits/add', [App\Http\Controllers\Admin\UserController::class, 'addCredits'])->name('users.credits.add');
    Route::get('reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports');
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
