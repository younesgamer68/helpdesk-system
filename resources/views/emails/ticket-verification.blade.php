<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header with Green Gradient -->
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); background-color: #10b981; color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
        <div style="margin-bottom: 16px;">
            <svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="40" height="40" rx="8" fill="white"/>
                <text x="20" y="26" font-family="Arial, sans-serif" font-size="15" font-weight="900" fill="#10b981" text-anchor="middle">HD</text>
                <text x="55" y="27" font-family="Arial, sans-serif" font-size="20" font-weight="700" fill="white">HelpDesk</text>
            </svg>
        </div>
        <h1 style="margin: 0; font-size: 24px;">Verify Your Support Ticket</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
        <p>Hello <strong>{{ $ticket->customer_name }}</strong>,</p>

        <p>Thank you for contacting us! We've received your support ticket and need you to verify your email address to proceed.</p>

        <!-- Ticket Info Box -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb; border-left: 4px solid #10b981;">
            <p style="margin: 0 0 10px 0;"><strong>Ticket Number:</strong> <span style="font-family: monospace; color: #10b981;">{{ $ticket->ticket_number }}</span></p>
            <p style="margin: 0 0 10px 0;"><strong>Subject:</strong> {{ $ticket->subject }}</p>
            <p style="margin: 0;"><strong>Submitted:</strong> {{ $ticket->created_at->format('M d, Y g:i A') }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('widget.verify', ['company' => $ticket->company ?? $ticket->company_id, 'ticketNumber' => $ticket->ticket_number, 'token' => $ticket->verification_token]) }}" 
               style="display: inline-block; padding: 14px 28px; background: #10b981; background-color: #10b981; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0;">
                Verify Your Ticket
            </a>
        </div>

        <div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; color: #92400e;"><strong>⚠️ Important:</strong> This verification link will expire once used. After verification, you'll receive a separate tracking link to monitor your ticket.</p>
        </div>

        <p>If you didn't submit this ticket, you can safely ignore this email.</p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        &copy; {{ date('Y') }} {{ ($ticket->company && $ticket->company->name) ? $ticket->company->name : config('app.name') }}. All rights reserved.
    </div>
</body>
</html>