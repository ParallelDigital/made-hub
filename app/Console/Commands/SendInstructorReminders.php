<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FitnessClass;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\InstructorClassRoster;

class SendInstructorReminders extends Command
{
    protected $signature = 'classes:send-instructor-reminders';
    protected $description = 'Send instructors a roster email 1 hour before class starts';

    public function handle()
    {
        $now = now();
        // Target window: classes starting about 60 minutes from now, within a 2-minute window
        $start = $now->copy()->addHour()->subMinutes(1)->startOfMinute();
        $end = $now->copy()->addHour()->addMinutes(1)->endOfMinute();
        $targetDate = $start->toDateString();

        $this->info('Scanning for classes starting between ' . $start . ' and ' . $end);

        // Get all active classes with instructors
        $allClasses = FitnessClass::with(['instructor'])
            ->whereNotNull('start_time')
            ->where('active', true)
            ->get();

        $sent = 0;
        $processed = []; // Track which class+date combinations we've sent

        foreach ($allClasses as $class) {
            try {
                // Parse start_time
                $parsed = Carbon::parse($class->start_time);
                $timeStr = $parsed->format('H:i:s');
                $classStart = Carbon::parse($targetDate . ' ' . $timeStr);

                // Check if this class starts in our target window
                if (!$classStart->betweenIncluded($start, $end)) {
                    continue;
                }

                // Check if we've already sent a reminder for this class+date
                $key = $class->id . '|' . $targetDate;
                if (in_array($key, $processed)) {
                    continue;
                }

                $email = $class->instructor?->email;
                if (!$email) {
                    $this->warn('No instructor email for class ID ' . $class->id);
                    continue;
                }

                // Check if there are any bookings for this class on this date
                $bookingCount = \App\Models\Booking::where('fitness_class_id', $class->id)
                    ->where('booking_date', $targetDate)
                    ->where('status', 'confirmed')
                    ->count();

                // Send reminder even if no bookings (instructor should know)
                Mail::to($email)->send(new InstructorClassRoster($class, 'reminder', $targetDate));
                $processed[] = $key;
                $sent++;
                
                $this->info(sprintf(
                    'Sent reminder for class ID %d (%s) on %s to %s - %d bookings',
                    $class->id,
                    $class->name,
                    $targetDate,
                    $email,
                    $bookingCount
                ));
            } catch (\Throwable $e) {
                $this->error('Failed to send reminder for class ID ' . $class->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Instructor reminders sent: {$sent}");
        return 0;
    }
}
