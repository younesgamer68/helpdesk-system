<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body
    style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" cellpadding="0" cellspacing="0" style="max-width: 520px; width: 100%;">
                    <tr>
                        <td
                            style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                            <div style="height: 3px; background: #10b981;"></div>
                            <div style="padding: 32px;">
                                <!-- Logo -->
                                <div style="text-align: center; margin-bottom: 28px;">
                                    <img src="{{ asset('images/logolm.png') }}" alt="Helpdesk"
                                        style="height: 34px; width: auto; display: inline-block; vertical-align: middle;">
                                    <p
                                        style="margin: 8px 0 0 0; font-size: 12px; line-height: 1.4; color: #71717a; font-weight: 600; letter-spacing: 0.02em;">
                                        Secured by Helpdesk</p>
                                </div>

                                <!-- Heading -->
                                <h1
                                    style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #18181b; text-align: center;">
                                    Welcome Aboard!</h1>
                                <p style="margin: 0 0 24px 0; font-size: 14px; color: #a1a1aa; text-align: center;">Your
                                    HelpDesk account is ready</p>

                                <!-- Body -->
                                <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #52525b;">Hi
                                    <strong style="color: #18181b;">{{ $user->name }}</strong>,
                                </p>
                                <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.6; color: #52525b;">Thank
                                    you for signing up with HelpDesk! We're thrilled to have you. Your account has been
                                    created with <strong style="color: #18181b;">{{ $user->email }}</strong> and allows
                                    you to manage support tickets efficiently.</p>

                                <!-- CTA Button -->
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <a href="{{ route('dashboard') }}"
                                        style="display: inline-block; padding: 12px 28px; background-color: #059669; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">Go
                                        to Dashboard &rarr;</a>
                                </div>

                                <!-- Features -->
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                    style="margin-bottom: 24px;">
                                    <tr>
                                        <td style="background: #fafafa; border-radius: 8px; padding: 16px;">
                                            <p
                                                style="margin: 0 0 14px 0; font-size: 13px; font-weight: 700; color: #18181b; text-transform: uppercase; letter-spacing: 0.5px;">
                                                What you can do now</p>
                                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td
                                                        style="padding: 8px 0; vertical-align: top; width: 28px; font-size: 16px;">
                                                        &#127915;</td>
                                                    <td style="padding: 8px 0; vertical-align: top;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; font-weight: 600; color: #18181b;">
                                                            Manage Support Tickets</p>
                                                        <p style="margin: 2px 0 0 0; font-size: 13px; color: #a1a1aa;">
                                                            Receive, assign, and resolve tickets in one central place.
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 8px 0; vertical-align: top; width: 28px; font-size: 16px;">
                                                        &#128101;</td>
                                                    <td style="padding: 8px 0; vertical-align: top;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; font-weight: 600; color: #18181b;">
                                                            Invite Your Team</p>
                                                        <p style="margin: 2px 0 0 0; font-size: 13px; color: #a1a1aa;">
                                                            Add agents and collaborate on solving customer issues.</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 8px 0; vertical-align: top; width: 28px; font-size: 16px;">
                                                        &#128202;</td>
                                                    <td style="padding: 8px 0; vertical-align: top;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; font-weight: 600; color: #18181b;">
                                                            Track Performance</p>
                                                        <p style="margin: 2px 0 0 0; font-size: 13px; color: #a1a1aa;">
                                                            View reports and analytics to improve response times.</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Password Notice -->
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                    style="margin-bottom: 20px;">
                                    <tr>
                                        <td style="background: #fef3c7; border-radius: 8px; padding: 14px 16px;">
                                            <p
                                                style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: #92400e;">
                                                Action Required: Set Your Password</p>
                                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #92400e;">If
                                                you haven't set a password yet, please <a
                                                    href="{{ route('password.request') }}"
                                                    style="color: #b45309; font-weight: 700; text-decoration: underline;">set
                                                    one now</a> to secure your account.</p>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 0; font-size: 13px; color: #a1a1aa; text-align: center;">Need help?
                                    Contact us at <a href="mailto:support@helpdesk.com"
                                        style="color: #059669; text-decoration: none;">support@helpdesk.com</a></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 0; text-align: center;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #a1a1aa;">&copy; {{ date('Y') }}
                                {{ config('app.name') }}. All rights reserved.</p>
                            <p style="margin: 0; font-size: 12px;">
                                <a href="{{ route('home') }}" style="color: #a1a1aa; text-decoration: none;">Visit
                                    Website</a>
                                <span style="color: #d4d4d8; padding: 0 6px;">&middot;</span>
                                <a href="{{ route('login') }}" style="color: #a1a1aa; text-decoration: none;">Log In</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
