<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/api/classes', [App\Http\Controllers\HomeController::class, 'getClasses']);
Route::get('/purchase', [App\Http\Controllers\PurchaseController::class, 'index'])->name('purchase.index');

Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (auth()->check() && auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
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
    Route::resource('memberships', App\Http\Controllers\Admin\MembershipController::class);
    Route::resource('pricing', App\Http\Controllers\Admin\PricingController::class);
    Route::get('users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
    Route::get('reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports');
});

require __DIR__.'/auth.php';
