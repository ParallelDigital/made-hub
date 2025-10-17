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

        $this->info('Scanning for classes starting between ' . $start . ' and ' . $end);

        $classes = FitnessClass::with(['instructor', 'bookings.user'])
            ->whereNull('instructor_reminder_sent_at')
            ->whereNotNull('class_date')
            ->whereNotNull('start_time')
            ->get()
            ->filter(function ($class) use ($start, $end) {
                try {
                    // Parse start_time as Carbon regardless of whether it includes a date
                    $parsed = Carbon::parse($class->start_time);
                    if ($class->class_date) {
                        // Combine the stored date with the parsed time portion
                        $timeStr = $parsed->format('H:i:s');
                        $classStart = Carbon::parse($class->class_date->toDateString() . ' ' . $timeStr);
                    } else {
                        $classStart = $parsed;
                    }
                } catch (\Throwable $e) {
                    return false;
                }
                return $classStart->betweenIncluded($start, $end);
            });

        $sent = 0;

        foreach ($classes as $class) {
            $email = $class->instructor?->email;
            if (!$email) {
                $this->warn('No instructor email for class ID ' . $class->id);
                continue;
            }

            try {
                $bookingDate = $class->class_date ? $class->class_date->format('Y-m-d') : null;
                Mail::to($email)->send(new InstructorClassRoster($class, 'reminder', $bookingDate));
                $class->instructor_reminder_sent_at = now();
                $class->save();
                $sent++;
                $this->info('Sent reminder for class ID ' . $class->id . ' to ' . $email);
            } catch (\Throwable $e) {
                $this->error('Failed to send reminder for class ID ' . $class->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Instructor reminders sent: {$sent}");
        return 0;
    }
}
