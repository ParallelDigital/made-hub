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

        // Find all classes happening today that haven't received a morning reminder yet
        $classes = FitnessClass::with(['instructor', 'bookings.user'])
            ->whereNull('instructor_morning_sent_at')
            ->whereNotNull('class_date')
            ->whereNotNull('start_time')
            ->whereDate('class_date', $today)
            ->get();

        $sent = 0;

        foreach ($classes as $class) {
            $email = $class->instructor?->email;
            if (!$email) {
                $this->warn('No instructor email for class ID ' . $class->id);
                continue;
            }

            try {
                Mail::to($email)->send(new InstructorClassRoster($class, 'morning_reminder'));
                $class->instructor_morning_sent_at = now();
                $class->save();
                $sent++;
                $this->info('Sent morning reminder for class ID ' . $class->id . ' (' . $class->name . ') to ' . $email);
            } catch (\Throwable $e) {
                $this->error('Failed to send morning reminder for class ID ' . $class->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Instructor morning reminders sent: {$sent}");
        return 0;
    }
}
