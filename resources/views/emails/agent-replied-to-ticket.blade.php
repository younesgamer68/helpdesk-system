<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); background-color: #10b981; color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
        <div style="margin-bottom: 16px;">
            <svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="40" height="40" rx="8" fill="white"/>
                <text x="20" y="26" font-family="Arial, sans-serif" font-size="15" font-weight="900" fill="#10b981" text-anchor="middle">HD</text>
                <text x="55" y="27" font-family="Arial, sans-serif" font-size="20" font-weight="700" fill="white">HelpDesk</text>
            </svg>
        </div>
        <h1 style="margin: 0; font-size: 24px;">New Reply on Your Ticket</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
        <p>Hello <strong>{{ $ticket->customer_name }}</strong>,</p>

        <p>Our support team has replied to your ticket. You can view the full conversation and reply below.</p>

        <!-- Ticket Info -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb; border-left: 4px solid #10b981;">
            <p style="margin: 0 0 10px 0;"><strong>Ticket Number:</strong> <span style="font-family: monospace; color: #10b981;">{{ $ticket->ticket_number }}</span></p>
            <p style="margin: 0 0 10px 0;"><strong>Subject:</strong> {{ $ticket->subject }}</p>
        </div>

        @if (isset($reply) && strlen(strip_tags($reply->message)) > 0)
            <div style="background: white; padding: 16px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb;">
                 <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600;">Latest reply</p>
                 <p style="margin: 0; color: #374151;">{{ Str::limit(strip_tags($reply->message), 200) }}</p>
            </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ route('widget.track', ['company' => $ticket->company, 'ticketNumber' => $ticket->ticket_number, 'token' => $ticket->tracking_token]) }}" 
               style="display: inline-block; padding: 14px 28px; background: #10b981; background-color: #10b981; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0;">
                View Ticket & Reply
            </a>
        </div>

        <p style="margin-top: 20px; font-size: 14px; color: #6b7280;">Use the link above to view the full conversation and send a reply. All updates will be sent to this email.</p>
    </div>

    <div style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated message. Use your tracking link above to communicate with our support team.</p>
        <p>&copy; {{ date('Y') }} {{ ($ticket->company && $ticket->company->name) ? $ticket->company->name : config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>