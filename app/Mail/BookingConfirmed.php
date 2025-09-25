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
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Part\DataPart;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string|null */
    private $qrCodeRaw = null;
    /** @var string|null */
    private $qrAttachmentName = null;
    /** @var string|null */
    private $qrMime = null;
    /** @var string|null */
    private $qrCid = null;

    /**
     * Create a new message instance.
     */
    public function __construct(public Booking $booking, ?string $qrUrl = null)
    {
        // $qrUrl is accepted for backward compatibility but not required,
        // since this Mailable generates the signed URL internally.
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
        $qrUrl = URL::signedRoute('user.checkin', [
            'user' => $this->booking->user->id,
            'qr_code' => $this->booking->user->qr_code,
        ]);
        // Ensure QR image bytes are prepared (JPEG preferred, PNG fallback)
        $qrCodeBase64 = $this->prepareQrImage($qrUrl);

        // Also embed the QR as an inline MIME part with a fixed Content-ID so the view
        // can reference it via cid:qr-code. We keep the file attachment (in attachments()).
        $this->withSymfonyMessage(function (SymfonyEmail $email) {
            if (!empty($this->qrCodeRaw) && !empty($this->qrMime)) {
                $part = new DataPart($this->qrCodeRaw, $this->qrAttachmentName ?? 'checkin-qr.jpg', $this->qrMime);
                // Ensure it's inline and uses a predictable Content-ID
                $part = $part->asInline();
                // Symfony generates a Content-ID automatically; to ensure consistency, set it explicitly
                $headers = $part->getHeaders();
                $headers->remove('Content-ID');
                $headers->addIdHeader('Content-ID', 'qr-code');
                $email->addPart($part);
                $this->qrCid = 'cid:qr-code';
            }
        });

        return new Content(
            view: 'emails.booking_confirmed',
            with: [
                'booking' => $this->booking,
                'qrCode' => $qrCodeBase64,
                'qrUrl' => $qrUrl,
                'qrMime' => $this->qrMime ?? 'image/jpeg',
                'qrAttachmentName' => $this->qrAttachmentName ?? 'checkin-qr.jpg',
                'qrCid' => $this->qrCid,
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
            $this->booking->loadMissing('user');
            $qrUrl = URL::signedRoute('user.checkin', [
                'user' => $this->booking->user->id,
                'qr_code' => $this->booking->user->qr_code,
            ]);
            $this->prepareQrImage($qrUrl);
        }

        $attachments = [];

        // Image attachment (JPG/PNG)
        if (!empty($this->qrCodeRaw)) {
            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromData(function () {
                return $this->qrCodeRaw;
            }, $this->qrAttachmentName ?? 'checkin-qr.jpg')->withMime($this->qrMime ?? 'image/jpeg');
        }

        // Optional PDF attachment if Dompdf is available
        $pdfBytes = $this->generateQrPdf();
        if ($pdfBytes) {
            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromData(function () use ($pdfBytes) {
                return $pdfBytes;
            }, 'checkin-qr.pdf')->withMime('application/pdf');
        }

        if (!empty($attachments)) {
            return $attachments;
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

    /**
     * Optionally render a simple one-page PDF with the QR code embedded, if Dompdf is available.
     * Returns raw PDF bytes or null when unavailable/failure.
     */
    private function generateQrPdf(): ?string
    {
        // Only attempt if Dompdf is installed
        if (!class_exists(\Dompdf\Dompdf::class)) {
            return null;
        }

        try {
            // Ensure we have base64 image data for embedding in PDF
            $this->booking->loadMissing('user');
            $qrUrl = URL::signedRoute('user.checkin', [
                'user' => $this->booking->user->id,
                'qr_code' => $this->booking->user->qr_code,
            ]);
            $base64 = $this->prepareQrImage($qrUrl);
            if (empty($base64) || empty($this->qrMime)) {
                return null;
            }
            $dataUri = 'data:' . $this->qrMime . ';base64,' . $base64;

            $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif; margin:32px;} .card{border:1px solid #ddd;border-radius:8px;padding:24px;text-align:center} img{width:240px;height:240px} h1{font-size:18px;margin:12px 0 8px} p{font-size:12px;color:#444;margin:4px 0}</style></head><body>' .
                '<div class="card">' .
                '<h1>Check-in QR Code</h1>' .
                '<p>Present this QR at the studio to check in.</p>' .
                '<img src="' . $dataUri . '" alt="QR Code" />' .
                '<p style="margin-top:12px;">Booking #'.htmlspecialchars((string)$this->booking->id).'</p>' .
                '</div>' .
                '</body></html>';

            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
