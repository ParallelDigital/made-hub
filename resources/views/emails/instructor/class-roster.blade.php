<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Roster</title>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #111827; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
        .header { background: transparent; color: #e2e8f0; padding: 20px; text-align: center; }
        .logo img { max-width: 180px; height: auto; display: inline-block; background: #ffffff; border-radius: 8px; padding: 4px; }
        .brand { color: #c084fc; font-weight: 700; letter-spacing: 0.4px; margin-top: 8px; }
        .header h1 { margin: 8px 0 0; font-size: 20px; color: #ffffff; }
        .content { padding: 20px; }
        .meta { background: #0b1220; border: 1px solid #334155; border-left: 4px solid #c084fc; padding: 12px 16px; margin: 16px 0; border-radius: 8px; }
        .meta p { margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #334155; font-size: 14px; }
        th { background: #0b1220; font-weight: 700; color: #e2e8f0; }
        .footer { background: transparent; color: #64748b; text-align: center; padding: 16px; font-size: 12px; }
        .tag { display:inline-block; padding: 4px 8px; border-radius: 999px; background:#334155; color:#e2e8f0; font-size:12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('made-running.png') }}" alt="Made Running">
            </div>
            <div class="brand">Made Running</div>
            <h1>
                @if($context === 'reminder')
                    Class Roster (1-hour Reminder)
                @elseif($context === 'morning_reminder')
                    Today's Class Roster
                @else
                    Class Roster Update (New Booking)
                @endif
            </h1>
        </div>
        <div class="content">
            <p>Hi {{ $class->instructor?->name ?? 'Instructor' }},</p>
            <p>
                Below is the current attendee list for your class.
                @if($context === 'reminder')
                    This is your one-hour reminder.
                @elseif($context === 'morning_reminder')
                    This is your morning reminder for today's class.
                @else
                    A new booking has been made and your roster has been updated.
                @endif
            </p>

            <div class="meta">
                <p><strong>Class:</strong> {{ $class->name }}</p>
                <p><strong>Date:</strong> {{ $class->class_date?->format('l, j F Y') }}</p>
                <p><strong>Time:</strong> {{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('g:i A') : '' }}
                    @if($class->end_time)
                        – {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}
                    @endif
                </p>
                <p><strong>Location:</strong> {{ $class->location ?? 'Studio' }}</p>
                <p><strong>Capacity:</strong> {{ $attendees->count() }} / {{ (int) $class->max_spots }}</p>
            </div>

            <h3 style="margin: 12px 0 6px;">Attendees</h3>
            @if($attendees->count() === 0)
                <p><span class="tag">No attendees yet</span></p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width: 42%;">Name</th>
                            <th style="width: 42%;">Email</th>
                            <th style="width: 16%;">Booked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendees as $booking)
                            <tr>
                                <td>{{ $booking->user?->name ?? '—' }}</td>
                                <td>{{ $booking->user?->email ?? '—' }}</td>
                                <td>{{ $booking->booked_at?->format('d M H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <p style="margin-top:16px; color:#555; font-size: 13px;">This roster reflects all confirmed bookings at the time this email was sent.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} MadeHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
