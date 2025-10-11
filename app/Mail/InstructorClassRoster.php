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
     */
    public function __construct(public FitnessClass $class, public string $context = 'booking_update')
    {
    }

    public function envelope(): Envelope
    {
        $this->class->loadMissing(['instructor']);

        // Compute attendees count for subject
        $attendeesCount = Booking::where('fitness_class_id', $this->class->id)
            ->where('status', 'confirmed')
            ->count();

        $dateStr = optional($this->class->class_date)->format('D j M Y');
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
        // Load roster with users
        $this->class->loadMissing(['bookings.user', 'instructor']);
        $attendees = $this->class->bookings
            ->where('status', 'confirmed')
            ->sortBy('user.name');

        return new Content(
            view: 'emails.instructor.class-roster',
            with: [
                'class' => $this->class,
                'attendees' => $attendees,
                'context' => $this->context,
            ],
        );
    }
}
