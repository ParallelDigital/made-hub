<?php

/**
 * Cleanup script to consolidate duplicate classes
 * This script finds duplicate classes with the same name, instructor, date, and time,
 * then consolidates them into a single class.
 * 
 * Run with: php database/cleanup_duplicate_classes.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FitnessClass;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

echo "=== Duplicate Classes Cleanup Script ===" . PHP_EOL . PHP_EOL;

// Find duplicate classes
$classes = FitnessClass::whereNull('parent_class_id')
    ->orderBy('name')
    ->orderBy('class_date')
    ->orderBy('start_time')
    ->get();

$grouped = $classes->groupBy(function($class) {
    return $class->name . '|' . $class->class_date . '|' . $class->start_time . '|' . $class->instructor_id;
});

$duplicatesFound = 0;
$classesConsolidated = 0;
$bookingsMoved = 0;

foreach ($grouped as $key => $group) {
    if ($group->count() > 1) {
        $duplicatesFound++;
        echo "Found duplicate: {$group->first()->name}" . PHP_EOL;
        echo "  Date: {$group->first()->class_date}, Time: {$group->first()->start_time}" . PHP_EOL;
        echo "  {$group->count()} duplicate classes found: IDs " . $group->pluck('id')->implode(', ') . PHP_EOL;
        
        // Keep the first class (lowest ID)
        $keepClass = $group->first();
        $duplicates = $group->slice(1);
        
        echo "  Keeping class ID: {$keepClass->id}" . PHP_EOL;
        echo "  Will consolidate: " . $duplicates->pluck('id')->implode(', ') . PHP_EOL;
        
        DB::beginTransaction();
        try {
            // Move all bookings from duplicate classes to the kept class
            foreach ($duplicates as $duplicate) {
                $bookings = Booking::where('fitness_class_id', $duplicate->id)->get();
                echo "    Moving {$bookings->count()} bookings from class {$duplicate->id} to {$keepClass->id}" . PHP_EOL;
                
                foreach ($bookings as $booking) {
                    // Check if this user already has a booking for the kept class on the same date
                    $existingBooking = Booking::where('fitness_class_id', $keepClass->id)
                        ->where('user_id', $booking->user_id)
                        ->where('booking_date', $booking->booking_date)
                        ->first();
                    
                    if ($existingBooking) {
                        echo "      Skipping duplicate booking for user {$booking->user_id} (already exists)" . PHP_EOL;
                    } else {
                        $booking->fitness_class_id = $keepClass->id;
                        $booking->save();
                        $bookingsMoved++;
                    }
                }
                
                // Delete the duplicate class
                $duplicate->delete();
                $classesConsolidated++;
                echo "    Deleted duplicate class ID: {$duplicate->id}" . PHP_EOL;
            }
            
            DB::commit();
            echo "  ✓ Consolidation complete" . PHP_EOL;
        } catch (\Exception $e) {
            DB::rollBack();
            echo "  ✗ Error: {$e->getMessage()}" . PHP_EOL;
        }
        
        echo PHP_EOL;
    }
}

echo "=== Summary ===" . PHP_EOL;
echo "Duplicate groups found: {$duplicatesFound}" . PHP_EOL;
echo "Classes consolidated: {$classesConsolidated}" . PHP_EOL;
echo "Bookings moved: {$bookingsMoved}" . PHP_EOL;

if ($duplicatesFound === 0) {
    echo "✓ No duplicates found! Database is clean." . PHP_EOL;
} else {
    echo "✓ Cleanup complete!" . PHP_EOL;
}
