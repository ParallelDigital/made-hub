<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Made Hub!</title>
    <style>
        body {
            font-family: 'Figtree', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .email-container {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #8b5cf6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
        }
        .content {
            padding: 40px 30px;
            background: #ffffff;
            color: #1a1a1a;
        }
        .highlight-box {
            background: linear-gradient(135deg, #f3f0ff 0%, #e9d5ff 100%);
            border: 2px solid #8b5cf6;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .highlight-box h3 {
            color: #6d28d9;
            margin: 0 0 15px 0;
            font-size: 1.3em;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            color: #ffffff;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 13px;
        }
        .class-image {
            max-width: 100%;
            border-radius: 12px;
            margin: 25px 0;
            border: 3px solid #c8b7ed;
        }
        .brand-text {
            color: #8b5cf6;
            font-weight: 600;
        }
        .section {
            margin: 30px 0;
            padding: 20px 0;
        }
        .section h3 {
            color: #6d28d9;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .hub-info {
            background: linear-gradient(135deg, #2a2a2a 0%, #3d2a4d 100%);
            border: 2px solid #c8b7ed;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .desktop-columns {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        .desktop-column {
            flex: 1;
        }
        .image-column {
            flex: 1;
            text-align: center;
        }
        @media (max-width: 768px) {
            .desktop-columns {
                flex-direction: column;
            }
            .desktop-column,
            .image-column {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 style="margin: 0; font-size: 2.2em; font-weight: 700;">🎉 Welcome to Made Running!</h1>
            <p style="margin: 15px 0 0 0; font-size: 1.1em; color: #ffffff;">Hi {{ $member->name ?? 'Valued Member' }},</p>
        </div>

        <div class="content">
            <p style="font-size: 1.1em; margin-bottom: 25px;">
                Welcome to <span class="brand-text">Made Running</span>! Your membership is now active and you're ready to start booking classes and enjoying exclusive member perks.
            </p>

            <div class="highlight-box">
                <h3>✨ Your Member Benefits</h3>
                <ul style="text-align: left; font-size: 1.05em; line-height: 1.8; margin: 15px 0; padding-left: 25px;">
                    <li><strong>5 classes per month</strong> included — book any eligible class</li>
                    <li><strong>Book meeting rooms</strong> for work or training sessions</li>
                    <li><strong>Monthly giveaways</strong> and prizes exclusive to members</li>
                    <li><strong>Early access</strong> to special events and workshops</li>
                    <li><strong>Member support</strong> whenever you need help</li>
                </ul>
            </div>

            <div class="section">
                <h3>💪 Ready to Get Started?</h3>
                <p>Log in now to book your first class and discover why <span class="brand-text">Made Running</span> is the ultimate fitness destination. Our state-of-the-art facilities and expert instructors are here to support your goals.</p>
            </div>

            <p style="text-align: center; margin: 35px 0;">
                <a href="{{ route('login') }}" class="cta-button">Log In and Book a Class</a>
            </p>

            <div style="background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center;">
                <p style="margin: 0; font-size: 0.95em; color: #92400e;">
                    <strong>💡 Tip:</strong> If you haven't set your password yet, use the default password: <strong style="color: #b45309; font-size: 1.1em;">Made2025!</strong>
                </p>
                <p style="margin: 10px 0 0 0; font-size: 0.9em; color: #78350f;">
                    You can update it in your profile after logging in.
                </p>
            </div>

            <p>If you have any questions or need assistance, our friendly staff is always here to help!</p>

            <p>
                See you soon,<br>
                <strong class="brand-text">The Made Running Team</strong><br>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Made Running. All rights reserved.</p>
            <p>Visit us at: <a href="{{ url('/') }}" style="color: #8b5cf6; text-decoration: none;">{{ url('/') }}</a></p>
        </div>
    </div>
</body>
</html>
