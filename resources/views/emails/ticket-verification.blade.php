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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: #3b82f6;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background: #2563eb;
        }
        .ticket-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;">Verify Your Support Ticket</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $ticket->customer_name }}</strong>,</p>

        <p>Thank you for contacting us! We've received your support ticket and need you to verify your email address to proceed.</p>

        <div class="ticket-info">
            <p style="margin: 0 0 10px 0;"><strong>Ticket Number:</strong> <span style="font-family: monospace; color: #3b82f6;">{{ $ticket->ticket_number }}</span></p>
            <p style="margin: 0 0 10px 0;"><strong>Subject:</strong> {{ $ticket->subject }}</p>
            <p style="margin: 0;"><strong>Submitted:</strong> {{ $ticket->created_at->format('M d, Y g:i A') }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('widget.verify', ['ticketNumber' => $ticket->ticket_number, 'token' => $ticket->verification_token]) }}" class="button">
                Verify Your Ticket
            </a>
        </div>

        <div class="warning">
            <p style="margin: 0; color: #92400e;"><strong>⚠️ Important:</strong> This verification link will expire once used. After verification, you'll receive a separate tracking link to monitor your ticket.</p>
        </div>

        <p>If you didn't submit this ticket, you can safely ignore this email.</p>
    </div>

    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>