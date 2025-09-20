<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Credits Added</title>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .card { background: #111827; border: 1px solid #334155; border-radius: 12px; padding: 24px; }
        .brand { color: #c084fc; font-weight: 700; letter-spacing: 0.4px; }
        .title { font-size: 22px; margin: 0 0 8px; color: #fff; }
        .subtitle { margin: 0 0 20px; color: #94a3b8; font-size: 14px; }
        .list { margin: 0; padding: 0; list-style: none; }
        .list li { margin: 10px 0; }
        .label { color: #94a3b8; font-size: 12px; display: block; }
        .value { color: #e2e8f0; font-size: 16px; font-weight: 600; }
        .hr { border: 0; border-top: 1px solid #334155; margin: 20px 0; }
        .note { background: #0b1220; border: 1px solid #334155; padding: 12px; border-radius: 8px; color: #cbd5e1; font-size: 14px; }
        .footer { color: #64748b; font-size: 12px; margin-top: 20px; text-align: center; }
        .cta { display: inline-block; background: #8b5cf6; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .text { color: #ffffff;}
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="brand">Made Running</div>
        <h1 class="title">Credits Added to Your Account</h1>
        <p class="subtitle">Hi {{ $user->first_name ?: $user->name ?: 'there' }}, we've added credits to your account.</p>

        <ul class="list">
            <li>
                <span class="label">Amount</span>
                <span class="value">+{{ $amount }} {{ ucfirst($creditLabel) }}</span>
            </li>
            <li>
                <span class="label">New Balance</span>
                <span class="value">{{ $newBalance }} {{ ucfirst($creditLabel) }}</span>
            </li>
        </ul>

        @if(!empty($note))
            <hr class="hr" />
            <div class="note">
                <strong>Note from Admin:</strong>
                <div>{{ $note }}</div>
            </div>
        @endif

        <hr class="hr" />
        <p class="text">If you have any questions, just reply to this email and our team will help you out.</p>
        <p style="margin-top: 16px;">
            <a class="cta" href="{{ url('/dashboard') }}">View your dashboard</a>
        </p>

        <div class="footer">
            You’re receiving this because credits were adjusted on your account.
        </div>
    </div>
</div>
</body>
</html>
