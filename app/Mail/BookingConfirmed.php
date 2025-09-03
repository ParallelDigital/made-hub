<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $qrUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $qrUrl)
    {
        $this->booking = $booking->load(['user', 'fitnessClass']);
        $this->qrUrl = $qrUrl;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $class = $this->booking->fitnessClass;
        $subject = 'Your booking is confirmed: ' . ($class->name ?? 'Fitness Class');

        return $this->subject($subject)
            ->view('emails.booking_confirmed')
            ->with([
                'booking' => $this->booking,
                'qrUrl' => $this->qrUrl,
            ]);
    }
}
