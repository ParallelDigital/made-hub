<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FitnessClass;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\InstructorClassRoster;

class SendInstructorMorningReminders extends Command
{
    protected $signature = 'classes:send-instructor-morning-reminders';
    protected $description = 'Send instructors a morning roster email for classes happening today';

    public function handle()
    {
        $now = now();
        $today = $now->toDateString();
        
        $this->info('Scanning for classes happening today: ' . $today);

        // Get all active classes with instructors
        $allClasses = FitnessClass::with(['instructor'])
            ->whereNotNull('start_time')
            ->where('active', true)
            ->get();

        $sent = 0;
        $processed = []; // Track which class+date combinations we've sent

        foreach ($allClasses as $class) {
            $email = $class->instructor?->email;
            if (!$email) {
                continue;
            }

            try {
                // Check if we've already sent a morning reminder for this class+date
                $key = $class->id . '|' . $today;
                if (in_array($key, $processed)) {
                    continue;
                }

                // Check if there are any bookings for this class today
                $bookingCount = \App\Models\Booking::where('fitness_class_id', $class->id)
                    ->where('booking_date', $today)
                    ->where('status', 'confirmed')
                    ->count();

                // Only send if there are bookings today
                if ($bookingCount > 0) {
                    Mail::to($email)->send(new InstructorClassRoster($class, 'morning_reminder', $today));
                    $processed[] = $key;
                    $sent++;
                    
                    $this->info(sprintf(
                        'Sent morning reminder for class ID %d (%s) on %s to %s - %d bookings',
                        $class->id,
                        $class->name,
                        $today,
                        $email,
                        $bookingCount
                    ));
                }
            } catch (\Throwable $e) {
                $this->error('Failed to send morning reminder for class ID ' . $class->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Instructor morning reminders sent: {$sent}");
        return 0;
    }
}
