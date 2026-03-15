# Ticket Status Flow Implementation Plan

This plan outlines the implementation of the ticket status flow with automatic status transitions, email notifications, and real-time updates in the Laravel helpdesk system.

## Key Components
1. Database migration for resolved_at column (if needed)
2. Auto-close job for resolved tickets after 48 hours
3. Email notifications for resolved and closed tickets
4. Status logic updates in Livewire components
5. Real-time updates using Livewire events

## Implementation Steps
1. **Database Changes**: Confirm resolved_at column exists; add migration if needed
2. **Email System**: Create TicketResolved and TicketClosed mailables with queued delivery
3. **Auto-Close Job**: Create scheduled job to close tickets resolved >48 hours
4. **Agent Reply Logic**: Update TicketDetails to set status to 'pending' on agent replies
5. **Client Reply Logic**: Implement reopen/block logic in TicketConversation
6. **Resolve/Close Methods**: Add email sending and proper logging
7. **Real-Time Updates**: Implement Livewire event broadcasting for status changes
8. **Scheduler Registration**: Register auto-close job in Laravel scheduler

## Assumptions
- resolved_at column exists (confirmed via schema)
- Use UTC for 48-hour calculations
- TicketResolved and TicketClosed mailables need to be created
- Widget form link will be generated using route helper

## Status
Planning phase - awaiting final clarifications on AGENTS.md contents and email mailable existence.
