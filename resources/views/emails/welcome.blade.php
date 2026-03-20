<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); background-color: #10b981; color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
        <div style="margin-bottom: 16px;">
            <svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="40" height="40" rx="8" fill="white"/>
                <text x="20" y="26" font-family="Arial, sans-serif" font-size="15" font-weight="900" fill="#10b981" text-anchor="middle">HD</text>
                <text x="55" y="27" font-family="Arial, sans-serif" font-size="20" font-weight="700" fill="white">HelpDesk</text>
            </svg>
        </div>
        <div style="font-size: 40px; margin-bottom: 10px;">🎉</div>
        <h1 style="margin: 0; font-size: 24px;">Welcome Aboard!</h1>
        <p style="margin: 5px 0 0 0; opacity: 0.9;">Your HelpDesk account is ready.</p>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
        <p>Hi <strong>{{ $user->name }}</strong>,</p>

        <p>Thank you for signing up with HelpDesk! We're thrilled to have you. Your account has been created with <strong>{{ $user->email }}</strong> and allows you to manage support tickets efficiently.</p>

        <div style="text-align: center;">
            <a href="{{ route('dashboard') }}" 
               style="display: inline-block; padding: 14px 28px; background: #10b981; background-color: #10b981; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0;">
                Go to Dashboard &rarr;
            </a>
        </div>

        <!-- Features Grid -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #10b981; text-transform: uppercase;">What you can do now</h3>
            
            <div style="display: flex; align-items: start; margin-bottom: 15px;">
                <span style="font-size: 20px; margin-right: 10px;">🎫</span>
                <div>
                    <strong style="display: block; color: #111;">Manage Support Tickets</strong>
                    <span style="font-size: 14px; color: #666;">Receive, assign, and resolve tickets in one central place.</span>
                </div>
            </div>

            <div style="display: flex; align-items: start; margin-bottom: 15px;">
                <span style="font-size: 20px; margin-right: 10px;">👥</span>
                <div>
                    <strong style="display: block; color: #111;">Invite Your Team</strong>
                    <span style="font-size: 14px; color: #666;">Add agents and collaborate on solving customer issues.</span>
                </div>
            </div>

            <div style="display: flex; align-items: start;">
                <span style="font-size: 20px; margin-right: 10px;">📊</span>
                <div>
                    <strong style="display: block; color: #111;">Track Performance</strong>
                    <span style="font-size: 14px; color: #666;">View reports and analytics to improve response times.</span>
                </div>
            </div>
        </div>

        <!-- Password Notice -->
        <div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 15px; border-radius: 8px; margin: 20px 0;">
             <p style="margin: 0; color: #92400e;"><strong>⚠️ Action Required: Set Your Password</strong></p>
             <p style="margin: 5px 0 0 0; font-size: 14px; color: #92400e;">
                 If you haven't set a password yet, please <a href="{{ route('password.request') }}" style="color: #b45309; font-weight: bold; text-decoration: underline;">set one now</a> to secure your account.
             </p>
        </div>

        <p style="font-size: 14px; color: #6b7280; margin-top: 30px; text-align: center;">
            Need help? Contact us at <a href="mailto:support@helpdesk.com" style="color: #10b981;">support@helpdesk.com</a>
        </p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>
            <a href="{{ route('home') }}" style="color: #10b981; text-decoration: none;">Visit Website</a> &middot; 
            <a href="{{ route('login') }}" style="color: #10b981; text-decoration: none;">Log In</a>
        </p>
    </div>
</body>
</html>