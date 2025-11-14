<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Roster</title>
    <style>
        body { background: #f5f5f5; color: #1a1a1a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); color: #ffffff; padding: 32px 20px; text-align: center; }
        .logo img { max-width: 180px; height: auto; display: inline-block; background: #ffffff; border-radius: 8px; padding: 8px; }
        .brand { color: #ffffff; font-weight: 700; font-size: 18px; letter-spacing: 0.4px; margin-top: 12px; }
        .header h1 { margin: 12px 0 0; font-size: 22px; color: #ffffff; font-weight: 600; }
        .content { padding: 32px 24px; color: #1a1a1a; }
        .meta { background: linear-gradient(to right, #f3f0ff 0%, #e9d5ff 100%); border-left: 4px solid #8b5cf6; padding: 16px 20px; margin: 20px 0; border-radius: 8px; }
        .meta p { margin: 8px 0; color: #1a1a1a; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #e5e5e5; font-size: 14px; }
        th { background: #f3f0ff; font-weight: 700; color: #1a1a1a; }
        td { color: #333333; }
        .footer { background: #f5f5f5; color: #666666; text-align: center; padding: 20px; font-size: 12px; }
        .tag { display:inline-block; padding: 6px 12px; border-radius: 999px; background: #f3f0ff; color: #8b5cf6; font-size: 13px; font-weight: 600; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
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
                <p><strong>Date:</strong> {{ $bookingDate ? \Carbon\Carbon::parse($bookingDate)->format('l, j F Y') : $class->class_date?->format('l, j F Y') }}</p>
                <p><strong>Time:</strong> {{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('g:i A') : '' }}
                    @if($class->end_time)
                        – {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}
                    @endif
                </p>
                <p><strong>Location:</strong> {{ $class->location ?? 'Studio' }}</p>
                <p><strong>Capacity:</strong> {{ $attendees->count() }} / {{ (int) $class->max_spots }}</p>
            </div>

            <h3 style="margin: 20px 0 12px; color: #1a1a1a; font-size: 18px; font-weight: 600;">Attendees</h3>
            @if($attendees->count() === 0)
                <p><span class="tag">No attendees yet</span></p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%;">Name</th>
                            <th style="width: 35%;">Email</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 15%;">Booked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendees as $booking)
                            <tr>
                                <td>{{ $booking->user?->name ?? '—' }}</td>
                                <td>{{ $booking->user?->email ?? '—' }}</td>
                                <td>
                                    @if($booking->status === 'confirmed')
                                        <span class="status-badge status-confirmed">Paid</span>
                                    @elseif($booking->status === 'pending_payment')
                                        <span class="status-badge status-pending">Pay on Arrival</span>
                                    @else
                                        <span class="status-badge">{{ ucfirst($booking->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $booking->booked_at?->format('d M H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <p style="margin-top:16px; color:#666666; font-size: 13px;">This roster includes all confirmed bookings and "Pay on Arrival" reservations at the time this email was sent.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} MadeHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
