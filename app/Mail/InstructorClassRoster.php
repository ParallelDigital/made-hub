<?php

namespace App\Mail;

use App\Models\FitnessClass;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class InstructorClassRoster extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $context Either 'booking_update', 'reminder', or 'morning_reminder'
     * @param string|null $bookingDate The specific date for this class occurrence (for recurring classes)
     */
    public function __construct(
        public FitnessClass $class,
        public string $context = 'booking_update',
        public ?string $bookingDate = null
    ) {
        // If no specific booking date provided, use the class date
        if (!$this->bookingDate && $this->class->class_date) {
            $this->bookingDate = $this->class->class_date->format('Y-m-d');
        }
    }

    public function envelope(): Envelope
    {
        // Refresh the class from database to ensure we have latest data
        $this->class->refresh();
        $this->class->loadMissing(['instructor']);

        // Compute attendees count for subject - filter by booking_date for recurring classes
        $attendeesCount = Booking::where('fitness_class_id', $this->class->id)
            ->where('status', 'confirmed')
            ->when($this->bookingDate, function ($query) {
                $query->where('booking_date', $this->bookingDate);
            })
            ->count();
        
        // Fallback: If no bookings found with exact date, try whereDate
        if ($attendeesCount === 0 && $this->bookingDate) {
            $attendeesCount = Booking::where('fitness_class_id', $this->class->id)
                ->where('status', 'confirmed')
                ->whereDate('booking_date', $this->bookingDate)
                ->count();
        }

        // Use the specific booking date if provided, otherwise use class_date
        $dateStr = $this->bookingDate 
            ? Carbon::parse($this->bookingDate)->format('D j M Y')
            : optional($this->class->class_date)->format('D j M Y');
        $timeStr = $this->class->start_time ? Carbon::parse($this->class->start_time)->format('H:i') : '';

        $subjectPrefix = match($this->context) {
            'reminder' => 'Reminder (1hr)',
            'morning_reminder' => 'Today\'s Class',
            default => 'New Booking'
        };
        $subject = sprintf(
            '%s: %s — %s %s (%d/%d)',
            $subjectPrefix,
            $this->class->name,
            $dateStr,
            $timeStr,
            $attendeesCount,
            (int) $this->class->max_spots
        );

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        // Refresh the class from database to ensure we have latest data
        $this->class->refresh();
        $this->class->loadMissing(['instructor']);
        
        // Load roster with users - filter by booking_date for recurring classes
        // Try multiple strategies to ensure we find bookings even with date format inconsistencies
        $attendees = Booking::where('fitness_class_id', $this->class->id)
            ->where('status', 'confirmed')
            ->when($this->bookingDate, function ($query) {
                $query->where('booking_date', $this->bookingDate);
            })
            ->with('user')
            ->get();
        
        // Fallback: If no bookings found and we have a booking date, try with whereDate
        if ($attendees->isEmpty() && $this->bookingDate) {
            $attendees = Booking::where('fitness_class_id', $this->class->id)
                ->where('status', 'confirmed')
                ->whereDate('booking_date', $this->bookingDate)
                ->with('user')
                ->get();
        }
        
        // Final fallback: Check if bookings exist for this class on ANY date (helps identify the issue)
        $totalBookingsAnyDate = Booking::where('fitness_class_id', $this->class->id)
            ->where('status', 'confirmed')
            ->count();
        
        $attendees = $attendees->sortBy('user.name');

        // Enhanced logging to debug booking count issues
        \Log::info('InstructorClassRoster email data', [
            'class_id' => $this->class->id,
            'class_name' => $this->class->name,
            'class_date_from_db' => $this->class->class_date ? $this->class->class_date->format('Y-m-d') : null,
            'max_spots' => $this->class->max_spots,
            'booking_date_parameter' => $this->bookingDate,
            'attendees_count' => $attendees->count(),
            'total_bookings_any_date' => $totalBookingsAnyDate,
            'attendees_list' => $attendees->map(function($b) {
                return [
                    'booking_id' => $b->id,
                    'user' => $b->user ? $b->user->name : 'N/A',
                    'booking_date' => $b->booking_date,
                    'fitness_class_id' => $b->fitness_class_id,
                ];
            })->toArray(),
            'context' => $this->context,
            'instructor_name' => $this->class->instructor ? $this->class->instructor->name : 'N/A',
            'instructor_email' => $this->class->instructor ? $this->class->instructor->email : 'N/A',
        ]);

        return new Content(
            view: 'emails.instructor.class-roster',
            with: [
                'class' => $this->class,
                'attendees' => $attendees,
                'context' => $this->context,
                'bookingDate' => $this->bookingDate,
            ],
        );
    }
}
