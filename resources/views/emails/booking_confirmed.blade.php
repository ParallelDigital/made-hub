<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .header { background-color: #000000; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
        }
        .brand-badge { display: inline-flex; align-items: center; gap: 10px; justify-content: center; }
        .brand-title { color: #c8b7ed; font-weight: 800; letter-spacing: 0.5px; }
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content h2 {
            font-size: 20px;
            color: #111111;
        }
        .booking-details {
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9ff;
            border-left: 4px solid #c8b7ed; /* brand primary */
        }
        .booking-details p {
            margin: 5px 0;
        }
        .qr-code {
            text-align: center;
            margin-top: 30px;
        }
        .qr-code img {
            max-width: 200px;
            height: auto;
        }
        .button {
            display: inline-block;
            background-color: #c8b7ed;
            color: #000000 !important;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
        }
        .footer {
            background-color: #000000;
            color: #aaaaaa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .footer a {
            color: #c8b7ed;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="brand-badge">
                <img src="{{ asset('made-running.webp') }}" alt="Made Running" width="150" height="100" >
            </div>
            <h1 style="margin-top:10px;">Booking Confirmed</h1>
        </div>
        <div class="content">
            <h2>Hi {{ $booking->user->name ?? 'there' }},</h2>
            <p>Your spot is secured! We're excited to see you at the studio. Here are your booking details:</p>

            <div class="booking-details">
                <p><strong>Class:</strong> {{ $booking->fitnessClass?->name ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ $booking->fitnessClass?->class_date?->format('l, F j, Y') ?? 'N/A' }}</p>
                <p><strong>Time:</strong> {{ $booking->fitnessClass?->start_time ? \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') : 'N/A' }}</p>
                <p><strong>Instructor:</strong> {{ $booking->fitnessClass?->instructor?->name ?? 'N/A' }}</p>
            </div>

            <div class="qr-code">
                <h3>Your QR Code for Check-in</h3>
                <p>Present this code at the front desk to check in quickly.</p>
                @if($qrCode)
                    <img src="data:{{ $qrMime ?? 'image/jpeg' }};base64,{{ $qrCode }}" alt="Your Booking QR Code" style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
                @endif
                <p style="margin-top:12px; font-size: 13px; color:#555;">The QR code is also attached to this email as an image for your convenience.</p>
                <p style="margin-top:16px;"><a href="{{ $qrUrl }}" class="button">Open Check-in Link</a></p>
                <p style="margin-top:8px; font-size: 12px; color:#666; word-break: break-all;">Or copy this link: {{ $qrUrl }}</p>
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
