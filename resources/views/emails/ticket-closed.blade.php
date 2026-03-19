<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        .button {
            display: inline-block;
            padding: 14px 28px;
            background: #10b981;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }

        .ticket-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #6b7280;
        }

        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="margin-bottom: 16px;">
            <svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="40" height="40" rx="8" fill="white" />
                <text x="20" y="26" font-family="Arial, sans-serif" font-size="15" font-weight="900" fill="#6b7280"
                    text-anchor="middle">HD</text>
                <text x="55" y="27" font-family="Arial, sans-serif" font-size="20" font-weight="700"
                    fill="white">HelpDesk</text>
            </svg>
        </div>
        <h1 style="margin: 0; font-size: 24px;">Your Ticket Has Been Closed</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $ticket->customer_name }}</strong>,</p>

        <p>Your support ticket has been closed.</p>

        <div class="ticket-info">
            <p style="margin: 0 0 10px 0;"><strong>Ticket Number:</strong> <span
                    style="font-family: monospace; color: #6b7280;">{{ $ticket->ticket_number }}</span></p>
            <p style="margin: 0 0 10px 0;">
                <strong>Subject:</strong> {{ $ticket->subject }}
            </p>
            <p style="margin: 0;">
                <strong>Reason:</strong>
                @if ($closeReason === 'auto_closed')
                    Automatically closed after no activity
                @elseif ($closeReason === 'manual')
                    Closed by support agent
                @else
                    {{ $closeReason }}
                @endif
            </p>
        </div>

        <p>Need further assistance? You can view your ticket history or submit a new support request using the link
            below.</p>

        <div style="text-align: center;">
            <a href="{{ route('widget.track', ['company' => $ticket->company, 'ticketNumber' => $ticket->ticket_number, 'token' => $ticket->tracking_token]) }}"
                class="button">
                View Ticket
            </a>
        </div>

        <p style="margin-top: 20px; font-size: 14px; color: #6b7280;">Thank you for contacting us. We hope your issue
            was resolved to your satisfaction.</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Use your tracking link above to communicate with our support team.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>

</html>
