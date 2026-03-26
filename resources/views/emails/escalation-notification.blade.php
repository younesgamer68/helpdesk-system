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
                            <div style="height: 3px; background: #ef4444;"></div>

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
                                    Ticket Escalation Alert</h1>

                                <!-- Alert box -->
                                <div
                                    style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 16px; margin: 0 0 20px 0; font-size: 13px; color: #991b1b;">
                                    <strong>Attention Required!</strong> A ticket has been escalated due to inactivity.
                                    The automation rule &ldquo;{{ $rule->name }}&rdquo; was triggered.
                                </div>

                                <!-- Ticket info card -->
                                <div
                                    style="background: #fafafa; border-radius: 8px; padding: 16px; margin: 20px 0; border-left: 3px solid #ef4444;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a; width: 120px;">
                                                Ticket</td>
                                            <td
                                                style="padding: 4px 0; font-size: 13px; color: #18181b; font-weight: 600; font-family: monospace;">
                                                #{{ $ticket->ticket_number }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Customer</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ $ticket->customer_name }} ({{ $ticket->customer_email }})</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Subject</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ $ticket->subject }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Priority</td>
                                            <td
                                                style="padding: 4px 0; font-size: 13px; color: #18181b; font-weight: 600;">
                                                {{ ucfirst($ticket->priority) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Status</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Created</td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Last Updated
                                            </td>
                                            <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                {{ $ticket->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        @if ($ticket->assigned_to)
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Assigned To
                                                </td>
                                                <td style="padding: 4px 0; font-size: 13px; color: #18181b;">
                                                    {{ $ticket->assignedTo?->name ?? 'Unknown' }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13px; color: #71717a;">Assigned To
                                                </td>
                                                <td
                                                    style="padding: 4px 0; font-size: 13px; color: #ef4444; font-weight: 600;">
                                                    Unassigned</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>

                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
                                    <strong style="color: #18181b;">Time Since Last Activity:</strong>
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </p>
                                <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #52525b;">Please review
                                    and take appropriate action on this ticket.</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #a1a1aa;">This is an automated escalation
                                notification from the HelpDesk System.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
