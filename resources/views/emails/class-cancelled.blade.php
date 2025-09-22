<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Cancelled</title>
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
        .class-details {
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9ff;
            border-left: 4px solid #dc2626; /* red border */
        }
        .class-details p {
            margin: 5px 0;
        }
        .reason-box {
            margin: 20px 0;
            padding: 15px;
            background-color: #fef3f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
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
                <img src="{{ asset('made-club.jpg') }}" alt="Made Running" width="80" height="53" style="max-width: 100%; height: auto; border-radius: 4px;">
            </div>
            <h1 style="margin-top:10px;">Class Cancelled</h1>
        </div>
        <div class="content">
            <h2>Hi {{ $user->name ?? 'there' }},</h2>

            <p>We're sorry to inform you that the following class has been cancelled:</p>

            <div class="class-details">
                <p><strong>Class:</strong> {{ $class->name }}</p>
                <p><strong>Date:</strong> {{ $class->class_date->format('l, F j, Y') }}</p>
                <p><strong>Time:</strong> {{ $class->start_time }} - {{ $class->end_time }}</p>
                <p><strong>Instructor:</strong> {{ $class->instructor->name ?? 'No Instructor' }}</p>
                @if($class->location)
                    <p><strong>Location:</strong> {{ $class->location }}</p>
                @endif
            </div>

            @if($reason)
                <div class="reason-box">
                    <strong>Cancellation Reason:</strong><br>
                    {{ $reason }}
                </div>
            @endif

            <p>Your booking for this class has been automatically cancelled and any credits used will be refunded to your account.</p>

            <p>You can browse and book other available classes on our website:</p>
            <p style="margin-top: 20px;">
                <a href="{{ url('/dashboard') }}" class="button">Browse Available Classes</a>
            </p>

            <p>If you have any questions or need assistance finding another class, please don't hesitate to contact us.</p>

            <p>We're sorry for any inconvenience this may cause.</p>

            <p>Best regards,<br>The MadeHub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} MadeHub. All rights reserved.</p>
            <p><a href="{{ url('/') }}">Visit our website</a></p>
        </div>
    </div>
</body>
</html>
