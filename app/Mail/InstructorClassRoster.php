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
        $attendees = Booking::where('fitness_class_id', $this->class->id)
            ->where('status', 'confirmed')
            ->when($this->bookingDate, function ($query) {
                $query->where('booking_date', $this->bookingDate);
            })
            ->with('user')
            ->get()
            ->sortBy('user.name');

        \Log::info('InstructorClassRoster email data', [
            'class_id' => $this->class->id,
            'class_name' => $this->class->name,
            'max_spots' => $this->class->max_spots,
            'booking_date' => $this->bookingDate,
            'attendees_count' => $attendees->count(),
            'context' => $this->context,
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
