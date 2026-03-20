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

        .ticket-info {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .ticket-info p {
            margin: 10px 0;
        }

        .ticket-number {
            font-weight: bold;
            color: #10b981;
        }

        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }

        .footer a {
            color: #34d399;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 style="margin: 0;">Thank You for Contacting Us</h1>
    </div>

    <div class="content">
        <p>Dear {{ $ticket->customer_name }},</p>

        <p>{{ $message }}</p>

        <div class="ticket-info">
            <p><strong>Ticket Number:</strong> <span class="ticket-number">#{{ $ticket->ticket_number }}</span></p>
            <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
            <p><strong>Priority:</strong> {{ ucfirst($ticket->priority) }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</p>
        </div>

        <p>We have received your request and our team is working on it. You will receive updates via email as we make progress.</p>

        <p>Best regards,<br>Support Team</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply directly to this email.</p>
    </div>
</body>

</html>
