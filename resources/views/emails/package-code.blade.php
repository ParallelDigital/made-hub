<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your MADE Package Code</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111;">
    <h2 style="margin-bottom: 8px;">Your MADE package code</h2>
    <p style="margin: 0 0 16px 0;">Thank you for your purchase. Here is your code:</p>
    <div style="display:inline-block; padding: 12px 16px; background: #111; color: #fff; border-radius: 6px; font-weight: bold; letter-spacing: 1px;">
        {{ $code }}
    </div>
    <p style="margin-top: 16px;">Package: <strong>{{ strtoupper($type) }}</strong></p>
    <p style="margin-top: 8px;">Use this code at checkout to redeem your package. If you have questions, reply to this email.</p>
    <p style="margin-top: 24px; color:#555; font-size: 12px;">This code is valid for 1 month from the time of issue unless otherwise stated.</p>
</body>
</html>
