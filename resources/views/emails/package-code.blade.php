<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your MADE Package Code</title>
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .card { background: #111827; border: 1px solid #334155; border-radius: 12px; padding: 24px; }
        .logo { text-align: center; margin-bottom: 12px; }
        .logo img { max-width: 180px; height: auto; display: inline-block; background: #ffffff; border-radius: 8px; padding: 4px; }
        .brand { color: #c084fc; font-weight: 700; letter-spacing: 0.4px; text-align: center; }
        .title { font-size: 22px; margin: 8px 0 8px; color: #fff; text-align: left; }
        .subtitle { margin: 0 0 16px; color: #94a3b8; font-size: 14px; }
        .code { display:inline-block; padding: 12px 16px; background: #0b1220; color: #fff; border-radius: 8px; font-weight: 700; letter-spacing: 1px; border: 1px solid #334155; }
        .hr { border: 0; border-top: 1px solid #334155; margin: 20px 0; }
        .cta { display: inline-block; background: #8b5cf6; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .footer { color: #64748b; font-size: 12px; margin-top: 20px; text-align: center; }
    </style>
    
</head>
<body>
<div class="container">
    <div class="card">
        <div class="logo">
            <img src="{{ asset('made-running.png') }}" alt="Made Running">
        </div>
        <div class="brand">Made Running</div>
        <h1 class="title">Your MADE package code</h1>
        <p class="subtitle">Thank you for your purchase. Here is your code:</p>

        <div class="code">{{ $code }}</div>

        <p style="margin-top: 16px;">Package: <strong>{{ strtoupper($type) }}</strong></p>
        <p style="margin-top: 8px;">Use this code at checkout to redeem your package. If you have questions, reply to this email.</p>

        <hr class="hr" />
        <p><a class="cta" href="{{ url('/checkout') }}">Redeem now</a></p>

        <div class="footer">
            This code is valid for 1 month from the time of issue unless otherwise stated.
        </div>
    </div>
</div>
</body>
</html>
