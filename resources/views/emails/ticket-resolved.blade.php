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
                                    Ticket Resolved</h1>
                                <p style="margin: 0 0 24px 0; font-size: 14px; color: #a1a1aa; text-align: center;">Your
                                    support request has been completed</p>

                                <!-- Body -->
                                <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #52525b;">Hello
                                    <strong style="color: #18181b;">{{ $ticket->customer_name }}</strong>,
                                </p>
                                <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #52525b;">We're
                                    pleased to let you know that your support ticket has been marked as resolved.</p>

                                <!-- Ticket Info -->
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                    style="margin-bottom: 20px;">
                                    <tr>
                                        <td
                                            style="background: #fafafa; border-radius: 8px; border-left: 3px solid #10b981; padding: 16px;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td
                                                        style="padding: 4px 0; font-size: 14px; color: #a1a1aa; width: 120px;">
                                                        Ticket Number</td>
                                                    <td
                                                        style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #18181b; font-family: monospace;">
                                                        {{ $ticket->ticket_number }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 14px; color: #a1a1aa;">Subject
                                                    </td>
                                                    <td
                                                        style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #18181b;">
                                                        {{ $ticket->subject }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Notice -->
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                    style="margin-bottom: 24px;">
                                    <tr>
                                        <td style="background: #ecfdf5; border-radius: 8px; padding: 14px 16px;">
                                            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #065f46;">If
                                                you feel your issue hasn't been fully resolved, you can reopen this
                                                ticket by replying below.</p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- CTA Button -->
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <a href="{{ route('widget.track', ['company' => $ticket->company, 'ticketNumber' => $ticket->ticket_number, 'token' => $ticket->tracking_token]) }}"
                                        style="display: inline-block; padding: 12px 28px; background-color: #059669; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">View
                                        Ticket</a>
                                </div>

                                <p
                                    style="margin: 0; font-size: 13px; line-height: 1.5; color: #a1a1aa; text-align: center;">
                                    If you're satisfied with the resolution, no action is needed. The ticket will be
                                    automatically closed after a period of inactivity.</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 0; text-align: center;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #a1a1aa;">This is an automated message.
                                Use your tracking link above to communicate with our support team.</p>
                            <p style="margin: 0; font-size: 12px; color: #a1a1aa;">&copy; {{ date('Y') }}
                                {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
