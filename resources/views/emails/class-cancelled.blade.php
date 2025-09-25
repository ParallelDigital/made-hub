<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Cancelled</title>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .email-container { max-width: 640px; margin: 24px auto; background: #111827; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
        .header { background: transparent; color: #e2e8f0; padding: 20px; text-align: center; }
        .header h1 { margin: 8px 0 0; font-size: 22px; color: #ffffff; }
        .brand { color: #c084fc; font-weight: 700; letter-spacing: 0.4px; margin-top: 8px; }
        .logo-container img { max-width: 180px; height: auto; display: inline-block; background: #ffffff; border-radius: 8px; padding: 4px; }
        .content { padding: 24px; color: #e2e8f0; line-height: 1.6; }
        .content h2 { font-size: 18px; color: #ffffff; }
        .class-details { margin: 20px 0; padding: 16px; background: #0b1220; border: 1px solid #334155; border-left: 4px solid #dc2626; border-radius: 8px; }
        .class-details p { margin: 6px 0; }
        .reason-box { margin: 20px 0; padding: 15px; background: #1b1220; border: 1px solid #fecaca33; border-radius: 8px; color: #fecaca; }
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
            <h1>Class Cancelled</h1>
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
                <a href="{{ url('/dashboard') }}" class="cta">Browse Available Classes</a>
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
