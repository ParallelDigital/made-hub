<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FitnessClass;
use App\Models\Booking;
use App\Models\Instructor;

class DebugClassBookings extends Command
{
    protected $signature = 'debug:class-bookings {class_id?}';
    protected $description = 'Debug class bookings and show where they are stored';

    public function handle()
    {
        $classId = $this->argument('class_id');
        
        if ($classId) {
            $this->debugSpecificClass($classId);
        } else {
            $this->showAllClassesWithBookings();
        }
    }
    
    private function showAllClassesWithBookings()
    {
        $this->info('=== All Classes with Bookings ===');
        $this->newLine();
        
        $classes = FitnessClass::with(['instructor', 'bookings'])
            ->where('active', true)
            ->orderBy('name')
            ->get();
        
        foreach ($classes as $class) {
            $bookingCount = $class->bookings->count();
            if ($bookingCount > 0) {
                $this->line("Class ID: {$class->id}");
                $this->line("Name: {$class->name}");
                $this->line("Instructor: " . ($class->instructor->name ?? 'N/A'));
                $this->line("Is Recurring: " . ($class->isRecurring() ? 'Yes' : 'No'));
                $this->line("Parent Class ID: " . ($class->parent_class_id ?? 'N/A'));
                $this->line("Total Bookings: {$bookingCount}");
                $this->line("Class Date: " . ($class->class_date ? $class->class_date->format('Y-m-d') : 'N/A'));
                $this->newLine();
                
                // Show booking details
                foreach ($class->bookings as $booking) {
                    $this->line("  - Booking ID: {$booking->id}, User: {$booking->user->name}, Date: {$booking->booking_date}, Status: {$booking->status}");
                }
                $this->newLine();
                $this->line(str_repeat('-', 80));
                $this->newLine();
            }
        }
    }
    
    private function debugSpecificClass($classId)
    {
        $class = FitnessClass::with(['instructor', 'bookings.user', 'childClasses', 'parentClass'])->find($classId);
        
        if (!$class) {
            $this->error("Class not found with ID: {$classId}");
            return;
        }
        
        $this->info('=== Class Details ===');
        $this->line("Class ID: {$class->id}");
        $this->line("Name: {$class->name}");
        $this->line("Instructor: " . ($class->instructor->name ?? 'N/A'));
        $this->line("Instructor ID: " . ($class->instructor->id ?? 'N/A'));
        $this->line("Is Recurring: " . ($class->isRecurring() ? 'Yes' : 'No'));
        $this->line("Is Child Class: " . ($class->isChildClass() ? 'Yes' : 'No'));
        $this->line("Parent Class ID: " . ($class->parent_class_id ?? 'N/A'));
        $this->line("Class Date: " . ($class->class_date ? $class->class_date->format('Y-m-d') : 'N/A'));
        $this->line("Max Spots: {$class->max_spots}");
        $this->newLine();
        
        // Show child classes if any
        if ($class->childClasses->count() > 0) {
            $this->info('=== Child Classes ===');
            foreach ($class->childClasses as $child) {
                $this->line("Child ID: {$child->id}, Date: {$child->class_date}, Bookings: " . $child->bookings->count());
            }
            $this->newLine();
        }
        
        // Show all bookings for this class
        $this->info('=== Bookings for Class ID ' . $class->id . ' ===');
        $bookings = Booking::where('fitness_class_id', $class->id)
            ->with('user')
            ->orderBy('booking_date')
            ->get();
        
        if ($bookings->count() === 0) {
            $this->warn("No bookings found for this class ID");
        } else {
            $this->line("Total: {$bookings->count()} bookings");
            $this->newLine();
            foreach ($bookings as $booking) {
                $this->line("  Booking ID: {$booking->id}");
                $this->line("  User: {$booking->user->name} ({$booking->user->email})");
                $this->line("  Booking Date: {$booking->booking_date}");
                $this->line("  Status: {$booking->status}");
                $this->line("  Created: {$booking->created_at}");
                $this->newLine();
            }
        }
        
        // Check for bookings on child classes
        if ($class->childClasses->count() > 0) {
            $this->info('=== Bookings on Child Classes ===');
            foreach ($class->childClasses as $child) {
                $childBookings = Booking::where('fitness_class_id', $child->id)->with('user')->get();
                if ($childBookings->count() > 0) {
                    $this->line("Child Class ID {$child->id} ({$child->class_date}):");
                    foreach ($childBookings as $booking) {
                        $this->line("  - {$booking->user->name}, Date: {$booking->booking_date}, Status: {$booking->status}");
                    }
                    $this->newLine();
                }
            }
        }
        
        // Check ALL bookings in database for this class name (in case bookings are on wrong ID)
        $this->info('=== All Bookings for Classes Named "' . $class->name . '" ===');
        $allMatchingClasses = FitnessClass::where('name', $class->name)->pluck('id');
        $allBookings = Booking::whereIn('fitness_class_id', $allMatchingClasses)
            ->with('user')
            ->orderBy('booking_date')
            ->get();
        
        if ($allBookings->count() > 0) {
            $this->line("Found {$allBookings->count()} total bookings across all classes with this name:");
            foreach ($allBookings as $booking) {
                $this->line("  Class ID: {$booking->fitness_class_id}, User: {$booking->user->name}, Date: {$booking->booking_date}, Status: {$booking->status}");
            }
        }
    }
}
