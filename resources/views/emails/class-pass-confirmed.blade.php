@php
    $isUnlimited = ($passType === 'unlimited');
    $title = $isUnlimited ? 'Unlimited Class Pass Activated' : 'Class Pass Credits Added';
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

                <p class="p">Hi {{ $user->name ?? 'there' }},</p>
                @if($isUnlimited)
                    <p class="p">Your <strong>Unlimited</strong> class pass is now active. You can book as many classes as you like until it expires.</p>
                @else
                    <p class="p">We've added <strong>{{ $credits }}</strong> credits to your account. These can be used to book classes.</p>
                @endif

                @if($expiryText)
                    <p class="p">Pass expiry: <strong>{{ $expiryText }}</strong></p>
                @endif

                @if($isNewAccount && $password)
                    <div style="background:#fef3c7;border:2px solid #f59e0b;border-radius:8px;padding:16px;margin:16px 0">
                        <p style="margin:0 0 8px;color:#92400e;font-weight:600">🔐 Your Account Has Been Created</p>
                        <p style="margin:0 0 8px;color:#78350f;line-height:1.6">We've created an account for you so you can access your dashboard and book classes.</p>
                        <p style="margin:0 0 4px;color:#78350f"><strong>Email:</strong> {{ $user->email }}</p>
                        <p style="margin:0;color:#78350f"><strong>Password:</strong> <code style="background:#fde68a;padding:2px 6px;border-radius:4px;font-size:14px">{{ $password }}</code></p>
                    </div>
                @endif

                @if($isMember)
                    <p class="p" style="color:#a78bfa">✨ You are a member! These credits are in addition to your monthly membership credits.</p>
                @endif

                <p class="p muted">Source: {{ $source }}</p>

                <div style="margin:18px 0">
                    <a href="{{ url('/dashboard') }}" class="cta">Go to your dashboard</a>
                </div>

                <div class="hr"></div>
                <p class="p muted">If you didn’t expect this, please contact support.</p>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Made Running
            </div>
        </div>
    </div>
</body>
</html>
