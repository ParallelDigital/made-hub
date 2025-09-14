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

    /** @var string|null */
    private $qrCodeRaw = null;
    /** @var string|null */
    private $qrAttachmentName = null;
    /** @var string|null */
    private $qrMime = null;

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
        // Ensure QR image bytes are prepared (JPEG preferred, PNG fallback)
        $qrCodeBase64 = $this->prepareQrImage($qrUrl);

        return new Content(
            view: 'emails.booking_confirmed',
            with: [
                'booking' => $this->booking,
                'qrCode' => $qrCodeBase64,
                'qrUrl' => $qrUrl,
                'qrMime' => $this->qrMime ?? 'image/jpeg',
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
        // Ensure QR is prepared in case attachments() is evaluated before content()
        if (empty($this->qrCodeRaw)) {
            $qrUrl = URL::signedRoute('booking.checkin', ['booking' => $this->booking->id]);
            $this->prepareQrImage($qrUrl);
        }

        if (!empty($this->qrCodeRaw)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(function () {
                    return $this->qrCodeRaw;
                }, $this->qrAttachmentName ?? 'checkin-qr.jpg')->withMime($this->qrMime ?? 'image/jpeg'),
            ];
        }

        return [];
    }

    /**
     * Generate QR image bytes (JPEG preferred, PNG fallback), set properties, and return base64 data URI payload.
     */
    private function prepareQrImage(string $qrUrl): ?string
    {
        try {
            $qrPng = QrCode::format('png')->size(300)->margin(1)->generate($qrUrl);
            $qrPngString = (string) $qrPng;

            // Convert PNG -> JPG using GD (if available)
            $jpgData = null;
            if (function_exists('imagecreatefromstring')) {
                $im = @imagecreatefromstring($qrPngString);
                if ($im !== false) {
                    ob_start();
                    imagejpeg($im, null, 90);
                    $jpgData = ob_get_clean();
                    imagedestroy($im);
                }
            }

            if ($jpgData) {
                $this->qrCodeRaw = $jpgData;
                $this->qrMime = 'image/jpeg';
                $this->qrAttachmentName = 'checkin-qr.jpg';
                return base64_encode($jpgData);
            } else {
                // PNG fallback
                $this->qrCodeRaw = $qrPngString;
                $this->qrMime = 'image/png';
                $this->qrAttachmentName = 'checkin-qr.png';
                return base64_encode($qrPngString);
            }
        } catch (\Throwable $e) {
            $this->qrCodeRaw = null;
            $this->qrMime = null;
            $this->qrAttachmentName = null;
            return null;
        }
    }
}
