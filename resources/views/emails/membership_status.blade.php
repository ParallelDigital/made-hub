<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Membership Update</title>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .card { background: #111827; border: 1px solid #334155; border-radius: 12px; padding: 24px; }
        .brand { color: #c084fc; font-weight: 700; letter-spacing: 0.4px; }
        .title { font-size: 22px; margin: 0 0 8px; color: #fff; }
        .subtitle { margin: 0 0 20px; color: #94a3b8; font-size: 14px; }
        .hr { border: 0; border-top: 1px solid #334155; margin: 20px 0; }
        .footer { color: #64748b; font-size: 12px; margin-top: 20px; text-align: center; }
        .cta { display: inline-block; background: #8b5cf6; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .text { color: #ffffff;}
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="brand">Made Running</div>
        @if($event === 'activation')
            <h1 class="title">Your Membership is Active</h1>
            <p class="subtitle">Hi {{ $user->first_name ?: $user->name ?: 'there' }}, your MADE membership is now active. We've added your monthly class credits to your account.</p>
        @elseif($event === 'renewal')
            <h1 class="title">Membership Renewed</h1>
            <p class="subtitle">Hi {{ $user->first_name ?: $user->name ?: 'there' }}, your membership has renewed and your class credits have been refreshed.</p>
        @else
            <h1 class="title">Membership Update</h1>
            <p class="subtitle">Hi {{ $user->first_name ?: $user->name ?: 'there' }}, here's an update on your membership.</p>
        @endif

        <p class="text">Details:</p>
        <ul>
            <li>Current monthly credits: <strong>{{ (int)($user->monthly_credits ?? 0) }}</strong></li>
            @if(!empty($user->stripe_subscription_id))
                <li>Next credit refresh: On renewal</li>
            @else
                <li>Next credit refresh: {{ now()->startOfMonth()->addMonth()->format('D, M j, Y') }}</li>
            @endif
        </ul>

        <hr class="hr" />
        <p class="text">You can view your credits and upcoming classes on your dashboard.</p>
        <p style="margin-top: 16px;">
            <a class="cta" href="{{ url('/dashboard') }}">Go to dashboard</a>
        </p>

        <div class="footer">
            You’re receiving this email because your membership status changed.
        </div>
    </div>
</div>
</body>
</html>
