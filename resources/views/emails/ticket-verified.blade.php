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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .button:hover {
            background: #059669;
        }
        .success-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 15px 0;
        }
        .ticket-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .info-box {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
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
        <h1 style="margin: 0; font-size: 24px;">✓ Ticket Verified Successfully!</h1>
        <div class="success-badge">Verified</div>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $ticket->customer_name }}</strong>,</p>

        <p>Great news! Your support ticket has been verified and is now in our queue. Our team will review it and respond as soon as possible.</p>

        <div class="ticket-info">
            <p style="margin: 0 0 10px 0;"><strong>Ticket Number:</strong> <span style="font-family: monospace; color: #10b981;">{{ $ticket->ticket_number }}</span></p>
            <p style="margin: 0 0 10px 0;"><strong>Subject:</strong> {{ $ticket->subject }}</p>
            <p style="margin: 0 0 10px 0;"><strong>Status:</strong> <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $ticket->status) }}</span></p>
            <p style="margin: 0;"><strong>Priority:</strong> <span style="text-transform: capitalize;">{{ $ticket->priority }}</span></p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('widget.track', ['ticketNumber' => $ticket->ticket_number, 'token' => $trackingToken]) }}" class="button">
                Track Your Ticket
            </a>
        </div>

        <div class="info-box">
            <p style="margin: 0 0 10px 0; color: #1e40af;"><strong>📌 What happens next?</strong></p>
            <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                <li>Our support team will review your ticket</li>
                <li>You'll receive updates via email</li>
                <li>You can reply directly through the tracking link</li>
                <li>All conversation history is saved in one place</li>
            </ul>
        </div>

        <p><strong>Important:</strong> Save the tracking link above to view your ticket status and communicate with our team. You can also bookmark it for easy access.</p>

        <p>If you have any questions, simply reply through your tracking page and our team will get back to you.</p>
    </div>

    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
        <p>Use your tracking link above to communicate with our support team.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>