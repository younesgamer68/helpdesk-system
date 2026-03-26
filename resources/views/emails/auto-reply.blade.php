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
                            <div style="height: 3px; background: #10b981;"></div>

                            <div style="padding: 28px 32px;">
                                <!-- Logo -->
                                <div style="text-align: center; margin-bottom: 28px;">
                                    <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                        Secured by
                                        <img src="https://iili.io/qL3UhmB.png" alt="Helpdesk"
                                            style="display: inline-block; vertical-align: middle; height: 16px;margin:0 10px; width: 16px;">
                                        <span style="font-weight: 600; color: #4b5563;">Helpdesk</span>
                                    </p>
                                </div>

                                <!-- Title -->
                                <h1
                                    style="margin: 0 0 20px 0; font-size: 20px; font-weight: 700; color: #18181b; text-align: center;">
                                    Thank You for Contacting Us</h1>

                                <!-- Body -->
                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">Dear
                                    <strong style="color: #18181b;">{{ $ticket->customer_name }}</strong>,
                                </p>
                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
                                    {{ $message }}</p>

                                <!-- Ticket info card -->
                                <div
                                    style="background: #fafafa; border-radius: 8px; padding: 16px; margin: 20px 0; border-left: 3px solid #10b981;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a; width: 110px;">
                                                Ticket</td>
                                            <td
                                                style="padding: 4px 0; font-size: 13px; color: #18181b; font-weight: 600; font-family: monospace;">
                                                #{{ $ticket->ticket_number }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Subject</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ $ticket->subject }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Priority</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ ucfirst($ticket->priority) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Status</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">We
                                    have received your request and our team is working on it. You will receive updates
                                    via email as we make progress.</p>

                                <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #52525b;">Best
                                    regards,<br>Support Team</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #a1a1aa;">This is an automated message. Please
                                do not reply directly to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
