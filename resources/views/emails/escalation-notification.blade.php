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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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

        .alert-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
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
            color: #ef4444;
        }

        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .priority-urgent {
            background: #fef2f2;
            color: #dc2626;
        }

        .priority-high {
            background: #fff7ed;
            color: #ea580c;
        }

        .priority-medium {
            background: #fefce8;
            color: #ca8a04;
        }

        .priority-low {
            background: #f0fdf4;
            color: #16a34a;
        }

        .button {
            display: inline-block;
            padding: 14px 28px;
            background: #ef4444;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }

        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 style="margin: 0;">⚠️ Ticket Escalation Alert</h1>
    </div>

    <div class="content">
        <div class="alert-box">
            <strong>Attention Required!</strong>
            <p>A ticket has been escalated due to inactivity. The automation rule "{{ $rule->name }}" was triggered.</p>
        </div>

        <div class="ticket-info">
            <p><strong>Ticket Number:</strong> <span class="ticket-number">#{{ $ticket->ticket_number }}</span></p>
            <p><strong>Customer:</strong> {{ $ticket->customer_name }} ({{ $ticket->customer_email }})</p>
            <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
            <p><strong>Priority:</strong>
                <span class="priority-badge priority-{{ $ticket->priority }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</p>
            <p><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $ticket->updated_at->format('M d, Y H:i') }}</p>
            @if($ticket->assigned_to)
                <p><strong>Assigned To:</strong> {{ $ticket->assignedTo?->name ?? 'Unknown' }}</p>
            @else
                <p><strong>Assigned To:</strong> <span style="color: #ef4444;">Unassigned</span></p>
            @endif
        </div>

        <p><strong>Time Since Last Activity:</strong> {{ $ticket->updated_at->diffForHumans() }}</p>

        <p>Please review and take appropriate action on this ticket.</p>
    </div>

    <div class="footer">
        <p>This is an automated escalation notification from the HelpDesk System.</p>
    </div>
</body>

</html>
