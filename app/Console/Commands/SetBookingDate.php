<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SetBookingDate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:set-date 
                            {booking-id : The booking ID to update}
                            {date : The correct date in YYYY-MM-DD format}';

    /**
     * The console command description.
     */
    protected $description = 'Manually set the booking_date for a specific booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookingId = $this->argument('booking-id');
        $dateStr = $this->argument('date');

        // Validate date format
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $this->error("Invalid date format. Please use YYYY-MM-DD format.");
            return 1;
        }

        $booking = Booking::with(['fitnessClass', 'user'])->find($bookingId);

        if (!$booking) {
            $this->error("Booking ID {$bookingId} not found.");
            return 1;
        }

        $userName = $booking->user->name ?? 'N/A';
        $userEmail = $booking->user->email ?? 'N/A';
        $className = $booking->fitnessClass->name ?? 'N/A';
        $currentDate = $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'Not set';
        
        $this->info("Booking Details:");
        $this->line("  ID: {$booking->id}");
        $this->line("  User: {$userName} ({$userEmail})");
        $this->line("  Class: {$className}");
        $this->line("  Current booking_date: {$currentDate}");
        $this->line("  Class date (parent): {$booking->fitnessClass->class_date->format('Y-m-d')}");
        $this->line("  Booked at: {$booking->booked_at->format('Y-m-d H:i:s')}");
        $this->newLine();
        $this->line("  New booking_date will be: {$date->format('Y-m-d')}");
        $this->newLine();

        if (!$this->confirm('Do you want to proceed with this update?')) {
            $this->info('Update cancelled.');
            return 0;
        }

        $booking->booking_date = $date->format('Y-m-d');
        $booking->save();

        $this->info("✓ Successfully updated booking {$bookingId} with date {$date->format('Y-m-d')}");

        return 0;
    }
}
