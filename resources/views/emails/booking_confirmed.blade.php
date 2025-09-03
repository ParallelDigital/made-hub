@php
    // Generate a simple QR using Google Charts (no dependency). For production, consider a local QR library.
    $encoded = urlencode($qrUrl);
    $qrImg = "https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl={$encoded}&choe=UTF-8";
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111;">
    <h2>Booking Confirmed</h2>
    <p>Hi {{ $booking->user->name ?? 'there' }},</p>
    <p>Your booking for <strong>{{ $booking->fitnessClass->name ?? 'your class' }}</strong> is confirmed.</p>

    @if(isset($booking->fitnessClass))
        <p>
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->fitnessClass->class_date)->format('l, F j, Y') }}<br>
            <strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->fitnessClass->end_time)->format('g:i A') }}
        </p>
    @endif

    <p>Show this QR code at the studio to check in:</p>
    <p>
        <img src="{{ $qrImg }}" alt="QR Code" width="200" height="200">
    </p>
    <p>If you can’t scan the code, use this link:</p>
    <p><a href="{{ $qrUrl }}">{{ $qrUrl }}</a></p>

    <p>See you soon!</p>
</body>
</html>
