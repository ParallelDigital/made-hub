@php
    $isUnlimited = ($passType === 'unlimited');
    $title = $isUnlimited ? 'New Unlimited Class Pass Purchased' : 'New Class Pass Credits Purchased';
    $expiryText = $expiresAt ? $expiresAt->format('D, M j, Y') : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $title }}</title>
    <style>
        body{margin:0;background:#0f172a;color:#e5e7eb;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif}
        .container{max-width:640px;margin:0 auto;padding:24px}
        .card{background:#111827;border:1px solid #374151;border-radius:12px;overflow:hidden}
        .header{padding:20px;border-bottom:1px solid #374151;display:flex;align-items:center;gap:12px}
        .logo{height:28px}
        .brand{font-weight:700;font-size:18px;color:#fff}
        .content{padding:24px}
        .h1{color:#fff;font-size:20px;margin:0 0 12px}
        .p{line-height:1.6;margin:0 0 12px}
        .muted{color:#9ca3af}
        .cta{display:inline-block;background:#a78bfa;color:#000;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600}
        .hr{height:1px;background:#374151;border:0;margin:20px 0}
        .footer{padding:16px;color:#9ca3af;font-size:12px}
        .badge{display:inline-block;padding:4px 8px;border-radius:9999px;background:#1f2937;color:#e5e7eb;font-size:12px;margin-right:6px}
        .admin-notice{background:#1e293b;border:1px solid #374151;border-radius:8px;padding:16px;margin:16px 0}
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <img class="logo" src="{{ asset('made-running.png') }}" alt="Made Running">
                <div class="brand">Made Running</div>
            </div>
            <div class="content">
                <h1 class="h1">{{ $title }}</h1>

                <div class="admin-notice">
                    <p class="p"><strong>Admin Notification:</strong> A new class pass has been purchased and activated.</p>
                </div>

                <p class="p">Customer: <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>

                @if($isUnlimited)
                    <p class="p">Pass Type: <strong>Unlimited Access</strong></p>
                    <p class="p">Details: Customer can book unlimited classes until expiry.</p>
                @else
                    <p class="p">Pass Type: <strong>{{ $credits }} Credits</strong></p>
                    <p class="p">Details: Customer has {{ $credits }} class credits to use.</p>
                @endif

                @if($expiryText)
                    <p class="p">Expires: <strong>{{ $expiryText }}</strong></p>
                @endif

                <p class="p muted">Source: {{ $source }}</p>

                <div class="hr"></div>

                <a href="{{ route('admin.class-passes.index') }}" class="cta">View All Class Passes</a>
            </div>

            <div class="footer">
                <p>This is an automated notification from the Made Running admin system.</p>
            </div>
        </div>
    </div>
</body>
</html>
