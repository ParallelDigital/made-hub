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
        .header {
            background-color: #111111;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
        }
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
            background-color: #f9f9f9;
            border-left: 4px solid #fcd34d; /* primary color */
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
        .footer {
            background-color: #111111;
            color: #aaaaaa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .footer a {
            color: #fcd34d;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Booking Confirmed</h1>
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
                <div style="display: inline-block;">{!! base64_decode($qrCode) !!}</div>
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
