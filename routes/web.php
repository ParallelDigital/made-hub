<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');
Route::get('/api/classes', [App\Http\Controllers\HomeController::class, 'getClasses']);
Route::get('/purchase', [App\Http\Controllers\PurchaseController::class, 'index'])->name('purchase.index');

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
    Route::resource('classes', App\Http\Controllers\Admin\FitnessClassController::class);
    Route::post('classes/{class}/delete-after-date', [App\Http\Controllers\Admin\FitnessClassController::class, 'deleteAfterDate'])->name('classes.delete-after-date');
    Route::resource('memberships', App\Http\Controllers\Admin\MembershipController::class);
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);
    Route::resource('pricing', App\Http\Controllers\Admin\PricingController::class);
    // Admin bookings list
    Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show', 'update']);
    Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
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
