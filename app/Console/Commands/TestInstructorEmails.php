<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FitnessClass;
use App\Models\Instructor;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestInstructorEmails extends Command
{
    protected $signature = 'test:instructor-emails {--class_id=}';
    protected $description = 'Test instructor email notifications';

    public function handle()
    {
        $this->info('=== Instructor Email Diagnostic ===');
        
        // Check mail configuration
        $this->info('Mail Driver: ' . config('mail.default'));
        $this->info('Mail From: ' . config('mail.from.address'));
        
        // Check if we have instructors with emails
        $instructors = Instructor::whereNotNull('email')->get();
        $this->info("\nInstructors with emails: " . $instructors->count());
        
        foreach ($instructors as $instructor) {
            $this->line("  - {$instructor->name} ({$instructor->email})");
        }
        
        // Check recent classes
        $classId = $this->option('class_id');
        
        if ($classId) {
            $class = FitnessClass::with(['instructor', 'bookings'])->find($classId);
            if (!$class) {
                $this->error("Class ID {$classId} not found");
                return 1;
            }
        } else {
            $class = FitnessClass::with(['instructor', 'bookings'])
                ->where('class_date', '>=', now()->subDay())
                ->orderBy('class_date')
                ->first();
        }
        
        if (!$class) {
            $this->warn("\nNo upcoming classes found");
            return 0;
        }
        
        $this->info("\n=== Testing with Class ===");
        $this->info("Class: {$class->name}");
        $this->info("Date: {$class->class_date}");
        $this->info("Time: {$class->start_time}");
        $this->info("Instructor: " . ($class->instructor ? $class->instructor->name : 'NONE'));
        $this->info("Instructor Email: " . ($class->instructor?->email ?? 'NONE'));
        $this->info("Bookings: " . $class->bookings->count());
        
        if (!$class->instructor) {
            $this->error("This class has no instructor assigned!");
            return 1;
        }
        
        if (!$class->instructor->email) {
            $this->error("Instructor has no email address!");
            return 1;
        }
        
        // Try to send test email
        if ($this->confirm('Send test roster email to ' . $class->instructor->email . '?', true)) {
            try {
                Mail::to($class->instructor->email)->send(new \App\Mail\InstructorClassRoster($class, 'booking_update'));
                $this->info('✓ Test email sent successfully!');
                $this->info('Check the mail logs or inbox');
            } catch (\Exception $e) {
                $this->error('✗ Failed to send email: ' . $e->getMessage());
                Log::error('Test instructor email failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        return 0;
    }
}
