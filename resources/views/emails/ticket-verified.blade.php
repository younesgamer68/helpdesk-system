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
                                    <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                        Secured by
                                        <img src="https://iili.io/qL3UhmB.png" alt="Helpdesk"
                                            style="display: inline-block; vertical-align: middle; height: 16px;margin:0 10px; width: 16px;">
                                        <span style="font-weight: 600; color: #4b5563;">Helpdesk</span>
                                    </p>
                                </div>

                                <!-- Heading -->
                                <h1
                                    style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #18181b; text-align: center;">
                                    Ticket Verified Successfully</h1>
                                <p style="margin: 0 0 24px 0; font-size: 14px; color: #a1a1aa; text-align: center;">Your
                                    ticket is now in our queue</p>

                                <!-- Body -->
                                <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #52525b;">Hello
                                    <strong style="color: #18181b;">{{ $ticket->customer_name }}</strong>,
                                </p>
                                <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #52525b;">Great
                                    news! Your support ticket has been verified and is now in our queue. Our team will
                                    review it and respond as soon as possible.</p>

                                <!-- Ticket Info -->
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                    style="margin-bottom: 24px;">
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
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 14px; color: #a1a1aa;">Status
                                                    </td>
                                                    <td
                                                        style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #18181b; text-transform: capitalize;">
                                                        {{ str_replace('_', ' ', $ticket->status) }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 14px; color: #a1a1aa;">
                                                        Priority</td>
                                                    <td
                                                        style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #18181b; text-transform: capitalize;">
                                                        {{ $ticket->priority }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- CTA Button -->
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <a href="{{ route('widget.track', ['company' => $ticket->company, 'ticketNumber' => $ticket->ticket_number, 'token' => $trackingToken]) }}"
                                        style="display: inline-block; padding: 12px 28px; background-color: #059669; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">Track
                                        Your Ticket</a>
                                </div>

                                <!-- What happens next -->
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                                    style="margin-bottom: 20px;">
                                    <tr>
                                        <td style="background: #fafafa; border-radius: 8px; padding: 16px;">
                                            <p
                                                style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #18181b;">
                                                What happens next?</p>
                                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td
                                                        style="padding: 3px 0; font-size: 13px; color: #52525b; vertical-align: top; width: 16px;">
                                                        &#8226;</td>
                                                    <td style="padding: 3px 0; font-size: 13px; color: #52525b;">Our
                                                        support team will review your ticket</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 3px 0; font-size: 13px; color: #52525b; vertical-align: top;">
                                                        &#8226;</td>
                                                    <td style="padding: 3px 0; font-size: 13px; color: #52525b;">You'll
                                                        receive updates via email</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 3px 0; font-size: 13px; color: #52525b; vertical-align: top;">
                                                        &#8226;</td>
                                                    <td style="padding: 3px 0; font-size: 13px; color: #52525b;">You can
                                                        reply directly through the tracking link</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 3px 0; font-size: 13px; color: #52525b; vertical-align: top;">
                                                        &#8226;</td>
                                                    <td style="padding: 3px 0; font-size: 13px; color: #52525b;">All
                                                        conversation history is saved in one place</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 0 0 8px 0; font-size: 13px; line-height: 1.5; color: #52525b;"><strong
                                        style="color: #18181b;">Important:</strong> Save the tracking link above to view
                                    your ticket status and communicate with our team.</p>
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #a1a1aa;">If you have any
                                    questions, simply reply through your tracking page and our team will get back to
                                    you.</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 0; text-align: center;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #a1a1aa;">This is an automated message.
                                Use your tracking link to communicate with our support team.</p>
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
