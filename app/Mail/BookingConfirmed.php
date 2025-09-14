<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Eager load the class name for the subject line, but handle if it's missing.
        $this->booking->load('fitnessClass');

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Booking Confirmed: ' . ($this->booking->fitnessClass?->name ?? 'Your Class'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Eager load all necessary relationships for the view.
        $this->booking->load(['user', 'fitnessClass.instructor']);

        // Generate the QR code URL and the image data directly within the mailer.
        $qrUrl = URL::signedRoute('booking.checkin', ['booking' => $this->booking->id]);
        
        // Generate QR code - use SVG since PNG requires Imagick
        try {
            $qrCodeSvg = QrCode::format('svg')->size(200)->generate($qrUrl);
            $qrCodeBase64 = base64_encode($qrCodeSvg);
            $qrCodeFormat = 'svg';
        } catch (\Exception $e) {
            // If QR generation fails, we'll show the URL instead
            $qrCodeBase64 = null;
            $qrCodeFormat = null;
        }

        return new Content(
            view: 'emails.booking_confirmed',
            with: [
                'booking' => $this->booking,
                'qrCode' => $qrCodeBase64,
                'qrUrl' => $qrUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
