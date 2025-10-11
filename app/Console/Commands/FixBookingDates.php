<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\FitnessClass;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FixBookingDates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:fix-dates 
                            {--dry-run : Show what would be updated without making changes}
                            {--booking-id= : Fix a specific booking ID}
                            {--class-id= : Fix bookings for a specific class ID}
                            {--past-only : Only fix bookings for classes that have already occurred}
                            {--all : Fix all bookings (including upcoming - use with caution)}';

    /**
     * The console command description.
     */
    protected $description = 'Fix booking_date for existing bookings where it was not set correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $bookingId = $this->option('booking-id');
        $classId = $this->option('class-id');
        $pastOnly = $this->option('past-only');
        $all = $this->option('all');

        $this->info('Starting booking date fix process...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Safety check: require either --past-only or --all flag
        if (!$pastOnly && !$all && !$bookingId) {
            $this->error('Safety check: You must specify either --past-only or --all flag.');
            $this->info('For safety on a live site, use: --past-only');
            return 1;
        }

        if ($pastOnly) {
            $this->info('MODE: Only fixing past bookings (safe for live site)');
        } elseif ($all) {
            $this->warn('MODE: Fixing ALL bookings including upcoming ones');
        }

        // Build query
        $query = Booking::with('fitnessClass')
            ->whereNull('booking_date'); // Only fix bookings without a booking_date set

        if ($bookingId) {
            $query->where('id', $bookingId);
        }

        if ($classId) {
            $query->where('fitness_class_id', $classId);
        }

        $bookings = $query->get();

        // Filter for past bookings only if requested
        if ($pastOnly && !$bookingId) {
            $now = Carbon::now('Europe/London');
            $bookings = $bookings->filter(function ($booking) use ($now) {
                if (!$booking->fitnessClass) return false;
                
                $classDateTime = Carbon::parse(
                    $booking->fitnessClass->class_date->format('Y-m-d') . ' ' . $booking->fitnessClass->start_time,
                    'Europe/London'
                );
                
                return $classDateTime->lessThan($now);
            });
        }

        if ($bookings->isEmpty()) {
            $this->info('No bookings found that need fixing.');
            return 0;
        }

        $this->info("Found {$bookings->count()} booking(s) to process.");
        $this->newLine();

        $fixed = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            $class = $booking->fitnessClass;
            
            if (!$class) {
                $this->warn("Booking ID {$booking->id}: Class not found, skipping");
                $skipped++;
                continue;
            }

            // Strategy: Use the booked_at timestamp to guess the intended date
            // Assumption: Users book for dates on or after their booking date
            $bookedAt = Carbon::parse($booking->booked_at);
            $classDate = Carbon::parse($class->class_date);
            
            // If the class is recurring or if booked_at is after class_date, 
            // assume they booked for a date close to when they made the booking
            $inferredDate = null;
            
            if ($bookedAt->isAfter($classDate)) {
                // They booked after the parent class date - likely a recurring class
                // Use the date they booked (or closest class occurrence)
                $inferredDate = $bookedAt->format('Y-m-d');
            } else {
                // They booked before or on the class date - use class_date
                $inferredDate = $classDate->format('Y-m-d');
            }

            $userEmail = $booking->user->email ?? 'N/A';
            
            $this->line("Booking ID {$booking->id}:");
            $this->line("  Class: {$class->name}");
            $this->line("  User: {$userEmail}");
            $this->line("  Booked at: {$bookedAt->format('Y-m-d H:i:s')}");
            $this->line("  Class date (parent): {$classDate->format('Y-m-d')}");
            $this->line("  Inferred booking date: {$inferredDate}");

            if (!$dryRun) {
                $booking->booking_date = $inferredDate;
                $booking->save();
                $this->info("  ✓ Updated");
                $fixed++;
            } else {
                $this->comment("  → Would update (dry run)");
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Processed: {$bookings->count()}");
        if (!$dryRun) {
            $this->info("  Fixed: {$fixed}");
        } else {
            $this->info("  Would fix: {$bookings->count()}");
        }
        $this->info("  Skipped: {$skipped}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return 0;
    }
}
