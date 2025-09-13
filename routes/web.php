<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');

// Test email route with detailed debugging
Route::get('/test-email-send', function () {
    try {
        // Enable detailed error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Get the first user and booking
        $user = \App\Models\User::first();
        if (!$user) {
            return 'No users found in the database';
        }
        
        $booking = \App\Models\Booking::first();
        if (!$booking) {
            return 'No bookings found in the database';
        }
        
        // Generate QR URL
        $qrUrl = URL::signedRoute('booking.checkin', ['booking' => $booking->id]);
        
        // Log the attempt
        \Log::info('Manual test email send attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'booking_id' => $booking->id,
            'qr_url' => $qrUrl
        ]);
        
        // Send the email
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

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'instructor') {
        return redirect()->route('instructor.dashboard');
    }

    // For regular users, fetch their upcoming bookings
    $upcomingBookings = $user->bookings()
        ->whereHas('fitnessClass', function ($query) {
            $query->where('class_date', '>=', now()->toDateString());
        })
        ->with('fitnessClass.instructor')
        ->get()
        ->sortBy(function($booking) {
            return $booking->fitnessClass->class_date . ' ' . $booking->fitnessClass->start_time;
        });

    return view('dashboard', ['upcomingBookings' => $upcomingBookings]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
    Route::resource('pricing', App\Http\Controllers\Admin\PricingController::class);
    // Admin bookings list
    Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show', 'update']);
    Route::post('bookings/{booking}/resend-confirmation', [App\Http\Controllers\Admin\BookingController::class, 'resendConfirmation'])->name('bookings.resend-confirmation');
    Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::get('reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports');
});

// Instructor Routes
Route::middleware(['auth', 'instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\InstructorDashboardController::class, 'index'])->name('dashboard');
    Route::get('classes/{class}/members', [App\Http\Controllers\InstructorDashboardController::class, 'showMembers'])->name('classes.members');
    Route::get('classes/{class}/scanner', [App\Http\Controllers\InstructorDashboardController::class, 'showScanner'])->name('classes.scanner');
    Route::post('classes/{class}/scan', [App\Http\Controllers\InstructorDashboardController::class, 'processQrScan'])->name('classes.scan');
});

require __DIR__.'/auth.php';
