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
                    <!-- Card -->
                    <tr>
                        <td
                            style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                            <!-- Accent bar -->
                            <div style="height: 3px; background: #f59e0b;"></div>

                            <div style="padding: 28px 32px;">
                                <!-- Logo -->
                                <div style="text-align: center; margin-bottom: 28px;">
                                    <img src="{{ asset('images/logolm.png') }}" alt="Helpdesk"
                                        style="height: 34px; width: auto; display: inline-block; vertical-align: middle;">
                                    <p
                                        style="margin: 8px 0 0 0; font-size: 12px; line-height: 1.4; color: #71717a; font-weight: 600; letter-spacing: 0.02em;">
                                        Secured by Helpdesk</p>
                                </div>

                                <!-- Title -->
                                <h1
                                    style="margin: 0 0 20px 0; font-size: 20px; font-weight: 700; color: #18181b; text-align: center;">
                                    Your Ticket Will Close Soon</h1>

                                <!-- Body -->
                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">Hello
                                    <strong style="color: #18181b;">{{ $ticket->customer_name }}</strong>,
                                </p>
                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">This
                                    is a reminder that your support ticket will be automatically closed in <strong
                                        style="color: #18181b;">{{ $remainingHours }}
                                        {{ Str::plural('hour', $remainingHours) }}</strong> if there is no further
                                    activity.</p>

                                <!-- Ticket info card -->
                                <div
                                    style="background: #fafafa; border-radius: 8px; padding: 16px; margin: 20px 0; border-left: 3px solid #f59e0b;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a; width: 110px;">
                                                Ticket</td>
                                            <td
                                                style="padding: 4px 0; font-size: 13px; color: #18181b; font-weight: 600; font-family: monospace;">
                                                {{ $ticket->ticket_number }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Subject</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ $ticket->subject }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Warning box -->
                                <div
                                    style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 16px; margin: 20px 0; font-size: 13px; color: #92400e;">
                                    If you still need help, please reply to your ticket before it is closed. Once
                                    closed, you may need to submit a new request.
                                </div>

                                <!-- CTA Button -->
                                <div style="text-align: center; margin: 24px 0 0 0;">
                                    <a href="{{ route('widget.track', ['company' => $ticket->company, 'ticketNumber' => $ticket->ticket_number, 'token' => $ticket->tracking_token]) }}"
                                        style="display: inline-block; padding: 12px 28px; background-color: #059669; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">Reply
                                        to Ticket</a>
                                </div>

                                <p style="margin: 20px 0 0 0; font-size: 12px; color: #a1a1aa; text-align: center;">If
                                    you're satisfied with the resolution, no action is needed and the ticket will close
                                    automatically.</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 0; text-align: center;">
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
