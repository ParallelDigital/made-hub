<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .email-container { max-width: 640px; margin: 24px auto; background: #111827; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
        .header { background: transparent; color: #e2e8f0; padding: 20px; text-align: center; }
        .header h1 { margin: 8px 0 0; font-size: 22px; color: #ffffff; }
        .brand { color: #c084fc; font-weight: 700; letter-spacing: 0.4px; margin-top: 8px; }
        .logo-container img { max-width: 180px; height: auto; display: inline-block; background: #ffffff; border-radius: 8px; padding: 4px; }
        .content { padding: 24px; color: #e2e8f0; line-height: 1.6; }
        .content h2 { font-size: 18px; color: #ffffff; }
        .booking-details { margin: 20px 0; padding: 16px; background: #0b1220; border: 1px solid #334155; border-left: 4px solid #c084fc; border-radius: 8px; }
        .booking-details p { margin: 6px 0; }
        .qr-code { text-align: center; margin-top: 24px; }
        .qr-code img { max-width: 200px; height: auto; }
        .cta { display: inline-block; background: #8b5cf6; color: #fff !important; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .footer { background: transparent; color: #64748b; padding: 16px 20px; text-align: center; font-size: 12px; }
        .footer a { color: #c084fc; text-decoration: none; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="brand-badge">
                <div class="logo-container">
                    <img src="{{ asset('made-running.png') }}" alt="Made Running">
                </div>
            </div>
            <div class="brand">Made Running</div>
            <h1>Booking Confirmed</h1>
        </div>
        <div class="content">
            <h2>Hi {{ $booking->user->name ?? 'there' }},</h2>
            <p>Your spot is secured! We're excited to see you at the studio. Here are your booking details:</p>

            <div class="booking-details">
                <p><strong>Class:</strong> {{ $booking->fitnessClass?->name ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ ($booking->booking_date ?? $booking->fitnessClass?->class_date)?->format('l, F j, Y') ?? 'N/A' }}</p>
                <p><strong>Time:</strong> {{ $booking->fitnessClass?->start_time ? \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') : 'N/A' }}</p>
                <p><strong>Instructor:</strong> {{ $booking->fitnessClass?->instructor?->name ?? 'N/A' }}</p>
            </div>

            <div class="qr-code">
                <h3>Your QR Code for Check-in</h3>
                <p>Present this code at the front desk to check in quickly.</p>
                @if(isset($qrCid))
                    <img src="{{ $qrCid }}" alt="Your Booking QR Code" style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
                @elseif($qrCode)
                    <img src="data:{{ $qrMime ?? 'image/jpeg' }};base64,{{ $qrCode }}" alt="Your Booking QR Code" style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
                @endif
                <p style="margin-top:12px; font-size: 13px; color:#94a3b8;">The QR code is also attached to this email as an image for your convenience.</p>
                <p style="margin-top:16px;"><a href="{{ $qrCid ?? $qrUrl }}" class="cta" @if(isset($qrCid)) download="{{ $qrAttachmentName ?? 'checkin-qr.jpg' }}" @endif>Open QR Code Image</a></p>
                <p style="margin-top:8px; font-size: 12px; color:#94a3b8; word-break: break-all;">Or copy this link: {{ $qrUrl }}</p>
            </div>

            <p>If you have any questions or need to make changes, please don't hesitate to contact us.</p>
            <p>See you soon,<br>The MadeHub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} MadeHub. All rights reserved.</p>
            <p><a href="{{ url('/') }}">Visit our website</a></p>
        </div>
    </div>
</body>
</html>
