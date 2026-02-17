<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Welcome to HelpDesk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f0f4f2;
            color: #1a1a1a;
            padding: 40px 20px;
        }
        .wrapper {
            max-width: 580px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background-color: #0A170F;
            border-radius: 16px 16px 0 0;
            padding: 32px 40px;
            text-align: center;
        }
        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .logo-box {
            width: 40px; height: 40px;
            background-color: #0F766E;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 15px; color: white;
        }
        .logo-name {
            color: white;
            font-size: 20px;
            font-weight: 700;
        }
        .hero-icon {
            font-size: 52px;
            margin-bottom: 16px;
        }
        .header h1 {
            color: white;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.3;
        }
        .header p {
            color: rgba(255,255,255,0.65);
            font-size: 15px;
            margin-top: 8px;
        }

        /* Body */
        .body {
            background: white;
            padding: 40px;
        }
        .greeting {
            font-size: 17px;
            color: #1a1a1a;
            margin-bottom: 16px;
        }
        .greeting strong { color: #0F766E; }
        .description {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        /* CTA Button */
        .cta-wrap { text-align: center; margin-bottom: 36px; }
        .cta-btn {
            display: inline-block;
            background-color: #0F766E;
            color: white !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Features */
        .features {
            background: #f8fdfb;
            border: 1px solid #d1f0e8;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .features h3 {
            font-size: 14px;
            font-weight: 700;
            color: #0F766E;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 16px;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }
        .feature-item:last-child { margin-bottom: 0; }
        .feature-icon {
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .feature-text strong {
            display: block;
            font-size: 14px;
            color: #1a1a1a;
            margin-bottom: 2px;
        }
        .feature-text span {
            font-size: 13px;
            color: #777;
        }

        /* Set password notice */
        .notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 32px;
            font-size: 14px;
            color: #92400e;
            line-height: 1.6;
        }
        .notice strong { color: #78350f; }

        /* Divider */
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 28px 0;
        }

        /* Footer */
        .footer {
            background-color: #0A170F;
            border-radius: 0 0 16px 16px;
            padding: 28px 40px;
            text-align: center;
        }
        .footer p {
            color: rgba(255,255,255,0.45);
            font-size: 12px;
            line-height: 1.7;
        }
        .footer a {
            color: #22c997;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="logo-row">
            <div class="logo-box">HD</div>
            <span class="logo-name">HelpDesk</span>
        </div>
        <div class="hero-icon">🎉</div>
        <h1>You're in! Welcome aboard.</h1>
        <p>Your free HelpDesk account is ready.</p>
    </div>

    <!-- Body -->
    <div class="body">

        <p class="greeting">
            Hi <strong>{{ $user->name }}</strong>,
        </p>

        <p class="description">
            Thank you for signing up with HelpDesk. Your account has been created with
            <strong>{{ $user->email }}</strong> and you're now logged in to your dashboard.
            We're thrilled to have you!
        </p>

        <!-- CTA -->
        <div class="cta-wrap">
            <a href="{{ route('dashboard') }}" class="cta-btn">
                Go to my Dashboard →
            </a>
        </div>

        <!-- Features -->
        <div class="features">
            <h3>What you can do now</h3>

            <div class="feature-item">
                <span class="feature-icon">🎫</span>
                <div class="feature-text">
                    <strong>Manage support tickets</strong>
                    <span>Receive, assign and resolve tickets from one place.</span>
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">👥</span>
                <div class="feature-text">
                    <strong>Invite your team</strong>
                    <span>Add agents and collaborate on customer issues.</span>
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">📊</span>
                <div class="feature-text">
                    <strong>Track performance</strong>
                    <span>Reports and analytics to improve response times.</span>
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">🔗</span>
                <div class="feature-text">
                    <strong>Connect your tools</strong>
                    <span>Integrate with Slack, email, and more.</span>
                </div>
            </div>
        </div>

        <!-- Set password notice -->
        <div class="notice">
            <strong>⚠️ Set your password</strong><br>
            Your account was created quickly without a password. To secure your account,
            please <a href="{{ route('password.request') }}" style="color:#0F766E;font-weight:600;">set a password here</a>
            or use Google Sign-in next time.
        </div>

        <div class="divider"></div>

        <p style="font-size:13px;color:#999;text-align:center;">
            If you didn't create this account, you can safely ignore this email.<br>
            Need help? Contact us at <a href="mailto:support@helpdesk.com" style="color:#0F766E;">support@helpdesk.com</a>
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            © {{ date('Y') }} HelpDesk. All rights reserved.<br>
            <a href="{{ route('home') }}">helpdesk.com</a> ·
            <a href="{{ route('login') }}">Log in</a>
        </p>
    </div>

</div>
</body>
</html>