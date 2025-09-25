<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Roster</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #222; }
        .container { max-width: 680px; margin: 24px auto; background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .header { background: #000; color: #fff; padding: 16px 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .content { padding: 20px; }
        .meta { background:#f9f9ff; border-left: 4px solid #c8b7ed; padding: 12px 16px; margin: 16px 0; }
        .meta p { margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #fafafa; font-weight: 700; }
        .footer { background: #000; color: #aaa; text-align: center; padding: 16px; font-size: 12px; }
        .tag { display:inline-block; padding: 4px 8px; border-radius: 999px; background:#eee; font-size:12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                @if($context === 'reminder')
                    Class Roster (1-hour Reminder)
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
