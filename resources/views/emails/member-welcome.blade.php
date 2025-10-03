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
            color: #ffffff;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #000000;
        }
        .email-container {
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #c8b7ed;
        }
        .header {
            background: linear-gradient(135deg, #c8b7ed 0%, #a78bfa 100%);
            color: #000000;
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
            background: linear-gradient(90deg, transparent, #ffffff, transparent);
        }
        .content {
            padding: 40px 30px;
            background: #1a1a1a;
        }
        .highlight-box {
            background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
            border: 2px solid #c8b7ed;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .highlight-box h3 {
            color: #ffffff;
            margin: 0 0 15px 0;
            font-size: 1.3em;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #c8b7ed 0%, #a78bfa 100%);
            color: #000000;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(200, 183, 237, 0.3);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 183, 237, 0.4);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #c8b7ed;
            color: #ffffff;
            font-size: 13px;
        }
        .class-image {
            max-width: 100%;
            border-radius: 12px;
            margin: 25px 0;
            border: 3px solid #c8b7ed;
        }
        .brand-text {
            color: #c8b7ed;
            font-weight: 600;
        }
        .section {
            margin: 30px 0;
            padding: 20px 0;
        }
        .section h3 {
            color: #c8b7ed;
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
            <h1 style="margin: 0; font-size: 2.2em; font-weight: 700;">🎉 Welcome to Made Hub!</h1>
            <p style="margin: 15px 0 0 0; font-size: 1.1em; opacity: 0.9;">Hi {{ $member->name ?? 'Valued Member' }},</p>
        </div>

        <div class="content">
            <p style="font-size: 1.1em; margin-bottom: 25px;">
                Welcome to the <span class="brand-text">Made Hub</span> family! We're thrilled to have you join our premium fitness community.
            </p>

            <div class="highlight-box">
                <h3>🎫 Your Membership Includes:</h3>
                <p style="font-size: 1.3em; margin: 10px 0;">
                    <strong>{{ $remainingClasses }} Free Classes</strong><br>
                    <span style="font-size: 0.9em; opacity: 0.9;">to get you started on your fitness journey!</span>
                </p>
            </div>

            <div class="section">
                <h3>💪 Ready to Get Started?</h3>
                <p>Book your first class and discover why <span class="brand-text">Made Hub</span> is the ultimate fitness destination. Our state-of-the-art facilities and expert instructors are here to support your goals.</p>
            </div>

            <div class="desktop-columns">
                <div class="image-column">
                    <img src="{{ asset('class.jpg') }}" alt="Made Hub Fitness Class" class="class-image">
                </div>
                <div class="desktop-column">
                    <div class="hub-info">
                        <h3>🏢 Hub Access & Opening Hours</h3>
                        <p style="margin: 10px 0;">Feel free to use the hub anytime during our convenient opening hours:</p>
                        <p style="font-size: 1.2em; margin: 15px 0;">
                            <strong>🕐 8:30 AM - 9:00 PM</strong><br>
                            <strong>📅 Monday - Sunday</strong>
                        </p>
                    </div>

                    <div class="section">
                        <h3>💰 Need More Classes?</h3>
                        <p>Run out of free classes? No problem! You can purchase:</p>
                        <ul style="margin: 15px 0; padding-left: 20px;">
                            <li style="margin: 8px 0;"><strong>Extra Class Packs</strong> - Individual sessions when you need them</li>
                            <li style="margin: 8px 0;"><strong>Class Bundles</strong> - Multiple classes at discounted rates</li>
                            <li style="margin: 8px 0;"><strong>Unlimited Memberships</strong> - Complete access to all our facilities</li>
                        </ul>
                    </div>
                </div>
            </div>

            <p style="text-align: center; margin: 35px 0;">
                <a href="{{ url('/#schedule') }}" class="cta-button">Browse Classes Now</a>
            </p>

            <p>If you have any questions or need assistance, our friendly staff is always here to help!</p>

            <p>
                Best regards,<br>
                <strong class="brand-text">The Made Hub Team</strong><br>
            </p>
        </div>

        <div class="footer">
            <p>&copy; 2025 Made Hub. All rights reserved.</p>
            <p>Visit us at: <a href="{{ url('/') }}" style="color: #c8b7ed;">www.madehub.co.uk</a></p>
        </div>
    </div>
</body>
</html>
