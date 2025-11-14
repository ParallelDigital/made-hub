<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FitnessClass;
use App\Models\Booking;
use App\Models\Instructor;

class DiagnoseInstructorRoster extends Command
{
    protected $signature = 'diagnose:instructor-roster {instructor_email} {class_name?} {date?}';
    protected $description = 'Diagnose why instructor roster shows 0/10 when admin shows 3/10';

    public function handle()
    {
        $email = $this->argument('instructor_email');
        $className = $this->argument('class_name');
        $date = $this->argument('date');
        
        // Find instructor
        $instructor = Instructor::whereHas('user', function($q) use ($email) {
            $q->where('email', $email);
        })->with('user')->first();
        
        if (!$instructor) {
            $this->error("Instructor not found with email: {$email}");
            return;
        }
        
        $this->info("=== Instructor: {$instructor->name} (ID: {$instructor->id}) ===");
        $this->newLine();
        
        // Get their classes
        $query = $instructor->fitnessClasses()->where('active', true);
        
        if ($className) {
            $query->where('name', 'like', "%{$className}%");
        }
        
        $classes = $query->get();
        
        if ($classes->isEmpty()) {
            $this->warn("No active classes found for this instructor");
            return;
        }
        
        $this->info("Found {$classes->count()} active class(es):");
        $this->newLine();
        
        foreach ($classes as $class) {
            $this->line("--- Class ID: {$class->id} ---");
            $this->line("Name: {$class->name}");
            $this->line("Class Date (template): " . ($class->class_date ? $class->class_date->format('Y-m-d') : 'N/A'));
            $this->line("Time: {$class->start_time} - {$class->end_time}");
            $this->line("Max Spots: {$class->max_spots}");
            $this->line("Is Recurring: " . ($class->isRecurring() ? 'Yes' : 'No'));
            
            if ($class->isRecurring()) {
                $days = [];
                if ($class->monday) $days[] = 'Mon';
                if ($class->tuesday) $days[] = 'Tue';
                if ($class->wednesday) $days[] = 'Wed';
                if ($class->thursday) $days[] = 'Thu';
                if ($class->friday) $days[] = 'Fri';
                if ($class->saturday) $days[] = 'Sat';
                if ($class->sunday) $days[] = 'Sun';
                $this->line("Recurring Days: " . implode(', ', $days));
            }
            
            $this->newLine();
            
            // Get ALL bookings for this class
            $allBookings = Booking::where('fitness_class_id', $class->id)
                ->with('user')
                ->orderBy('booking_date')
                ->get();
            
            if ($allBookings->isEmpty()) {
                $this->warn("  ⚠️  No bookings found for this class!");
                $this->newLine();
                continue;
            }
            
            // Group by date
            $byDate = $allBookings->groupBy(function($b) {
                return $b->booking_date instanceof \Carbon\Carbon 
                    ? $b->booking_date->format('Y-m-d') 
                    : $b->booking_date;
            });
            
            $this->info("  Bookings by Date:");
            foreach ($byDate as $bookingDate => $bookings) {
                $confirmed = $bookings->where('status', 'confirmed');
                $this->line("    📅 {$bookingDate}:");
                $this->line("       Total: {$bookings->count()} bookings");
                $this->line("       Confirmed: {$confirmed->count()} bookings");
                
                foreach ($bookings as $booking) {
                    $statusColor = $booking->status === 'confirmed' ? '<fg=green>' : '<fg=yellow>';
                    $this->line("         {$statusColor}{$booking->status}</> - {$booking->user->name} ({$booking->user->email})");
                }
            }
            
            $this->newLine();
            
            // If specific date provided, show detailed analysis
            if ($date) {
                $this->info("  === Analysis for Date: {$date} ===");
                
                $dateBookings = Booking::where('fitness_class_id', $class->id)
                    ->where('booking_date', $date)
                    ->with('user')
                    ->get();
                
                $confirmedCount = $dateBookings->where('status', 'confirmed')->count();
                
                $this->line("  Query: Booking::where('fitness_class_id', {$class->id})->where('booking_date', '{$date}')->where('status', 'confirmed')->count()");
                $this->line("  Result: {$confirmedCount} confirmed bookings");
                
                if ($confirmedCount === 0) {
                    $this->error("  ❌ ISSUE: No confirmed bookings found for this date!");
                    $this->line("  This is why the instructor dashboard/email shows 0/{$class->max_spots}");
                } else {
                    $this->info("  ✅ OK: Found {$confirmedCount} confirmed bookings");
                    $this->line("  Instructor dashboard/email should show {$confirmedCount}/{$class->max_spots}");
                }
                
                $this->newLine();
            }
            
            $this->line(str_repeat('-', 80));
            $this->newLine();
        }
        
        // Summary
        $this->info("=== SUMMARY ===");
        $totalBookings = Booking::whereIn('fitness_class_id', $classes->pluck('id'))
            ->where('status', 'confirmed')
            ->count();
        $this->line("Total confirmed bookings across all classes: {$totalBookings}");
        
        if ($date) {
            $dateBookings = Booking::whereIn('fitness_class_id', $classes->pluck('id'))
                ->where('booking_date', $date)
                ->where('status', 'confirmed')
                ->count();
            $this->line("Confirmed bookings for {$date}: {$dateBookings}");
        }
    }
}
