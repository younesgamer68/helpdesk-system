<?php

namespace App\Http\Controllers;

use App\Mail\TicketVerification;
use App\Mail\TicketVerified;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Notifications\ClientReplied;
use App\Notifications\TicketSubmitted;
use App\Notifications\TicketUnassigned;
use App\Scopes\CompanyScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Show the widget form
     */
    public function show($companySlug, $key)
    {
        // Get company from request (set by middleware)
        $company = request()->get('company');

        $widget = WidgetSetting::where('widget_key', $key)
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->with(['company', 'company.categories' => fn ($q) => $q->whereNull('parent_id')->with(['children' => fn ($c) => $c->orderBy('name')])->orderBy('name')])
            ->firstOrFail();

        return view('portal.form', compact('widget'));
    }

    /**
     * Submit a ticket from the widget
     */
    public function submit(Request $request, $companySlug, $key)
    {
        $company = $request->attributes->get('company') ?? $request->get('company');
        $widget = WidgetSetting::where('widget_key', $key)
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->firstOrFail();

        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => $widget->require_phone ? 'required|string|max:20' : 'nullable|string|max:20',
            'subject' => 'required|string|max:500',
            'description' => 'required|string',
            'category_id' => $widget->show_category ? 'nullable|integer|exists:ticket_categories,id,company_id,'.$company->id : 'nullable',
        ];

        $validated = $request->validate($rules);

        // Generate unique ticket number
        do {
            $ticketNumber = 'TKT-'.strtoupper(Str::random(6));
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        // Keep customer profile in sync with the latest submitted widget identity.
        $customer = Customer::firstOrNew([
            'company_id' => $widget->company_id,
            'email' => $validated['customer_email'],
        ]);

        $customer->name = $validated['customer_name'];
        $customer->phone = $validated['customer_phone'] ?? null;

        if (! $customer->exists) {
            $customer->is_active = true;
        }

        $customer->save();

        if (! $customer->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. You cannot submit new tickets.',
            ], 403);
        }

        $requireVerification = $widget->company->require_client_verification ?? true;

        if ($requireVerification) {
            // Generate verification token
            $verificationToken = Str::random(64);

            // Create the ticket (unverified)
            $ticket = Ticket::create([
                'company_id' => $widget->company_id,
                'customer_id' => $customer->id,
                'ticket_number' => $ticketNumber,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'] ?? null,
                'priority' => $widget->default_priority,
                'status' => $widget->default_status,
                'assigned_to' => $widget->default_assigned_to,
                'verified' => false,
                'verification_token' => $verificationToken,
                'source' => 'widget',
            ]);

            // Send verification email
            Mail::to($ticket->customer_email)->send(new TicketVerification($ticket));
        } else {
            // Create the ticket (auto-verified)
            $trackingToken = Str::random(64);

            $ticket = Ticket::create([
                'company_id' => $widget->company_id,
                'customer_id' => $customer->id,
                'ticket_number' => $ticketNumber,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'] ?? null,
                'priority' => $widget->default_priority,
                'status' => $widget->default_status,
                'assigned_to' => $widget->default_assigned_to,
                'verified' => true,
                'tracking_token' => $trackingToken,
                'source' => 'widget',
            ]);

            // Send tracking email directly
            Mail::to($ticket->customer_email)->send(new TicketVerified($ticket, $trackingToken));
        }

        // Notify admins about the new ticket submission
        $admins = User::where('company_id', $ticket->company_id)
            ->whereIn('role', ['admin', 'super_admin'])
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new TicketSubmitted($ticket));
        }

        // If ticket is unassigned (automation or default), notify admins
        if (! $ticket->assigned_to) {
            foreach ($admins as $admin) {
                $admin->notify(new TicketUnassigned($ticket));
            }
        }

        return response()->json([
            'success' => true,
            'message' => $widget->success_message,
            'ticket_number' => $ticketNumber,
        ]);
    }

    /**
     * Verify a ticket via email link
     */
    public function verify(Company $company, $ticketNumber, $token)
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->where('company_id', $company->id)
            ->where('verification_token', $token)
            ->where('verified', false)
            ->firstOrFail();

        // Mark as verified
        $ticket->update([
            'verified' => true,
            'verification_token' => null, // Clear the verification token
        ]);

        // Generate tracking token in its own dedicated column
        $trackingToken = Str::random(64);
        $ticket->update(['tracking_token' => $trackingToken]);

        // Send tracking email
        Mail::to($ticket->customer_email)->send(new TicketVerified($ticket, $trackingToken));

        return view('portal.verified', compact('ticket'));
    }

    /**
     * Show ticket tracking page
     */
    public function track(Company $company, $ticketNumber, $token)
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->where('company_id', $company->id)
            ->where('tracking_token', $token)
            ->where('verified', true)
            ->with(['category', 'company'])
            ->firstOrFail();

        // Get all public replies
        $replies = TicketReply::where('ticket_id', $ticket->id)
            ->where('is_internal', false)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('portal.track', compact('ticket', 'replies'));
    }

    /**
     * Submit a reply from customer
     */
    public function reply(Request $request, Company $company, $ticketNumber, $token)
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->where('company_id', $company->id)
            ->where('tracking_token', $token)
            ->where('verified', true)
            ->firstOrFail();

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $policy = SlaPolicy::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $company->id)
            ->first();

        if ($ticket->status === 'closed') {
            $linkedTicketDays = $policy?->linked_ticket_days ?? 7;
            $closedAt = $ticket->closed_at;

            if (! $closedAt || now()->diffInDays($closedAt, false) < -$linkedTicketDays) {
                return back()->withErrors(['message' => 'This ticket is permanently closed. Please submit a new support request.']);
            }

            // Create a linked follow-up ticket
            do {
                $linkedTicketNumber = 'TKT-'.strtoupper(Str::random(6));
            } while (Ticket::where('ticket_number', $linkedTicketNumber)->exists());

            $linkedTicket = Ticket::create([
                'company_id' => $ticket->company_id,
                'customer_id' => $ticket->customer_id,
                'assigned_to' => $ticket->assigned_to,
                'category_id' => $ticket->category_id,
                'subject' => 'Follow-up: '.$ticket->subject,
                'description' => $validated['message'],
                'status' => 'open',
                'priority' => $ticket->priority,
                'parent_ticket_id' => $ticket->id,
                'ticket_number' => $linkedTicketNumber,
                'tracking_token' => Str::random(32),
                'verified' => true,
                'source' => $ticket->source ?? 'widget',
            ]);

            if ($linkedTicket->assignedTo) {
                $linkedTicket->assignedTo->notify(new ClientReplied($linkedTicket));
            }

            return back()->with('success', 'A new follow-up ticket (#'.$linkedTicket->ticket_number.') has been created!');
        }

        if ($ticket->status === 'resolved') {
            $reopenHours = $policy?->reopen_hours ?? 48;
            $resolvedAt = $ticket->resolved_at;

            if (! $resolvedAt || now()->diffInHours($resolvedAt, false) < -$reopenHours) {
                return back()->withErrors(['message' => 'The reopen window has passed. Please submit a new support request.']);
            }
        }

        // Create reply from customer
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'customer_name' => $ticket->customer_name,
            'message' => \Mews\Purifier\Facades\Purifier::clean($validated['message']),
            'is_internal' => false,
        ]);

        if ($ticket->status === 'resolved') {
            $ticket->update([
                'status' => 'open',
                'resolved_at' => null,
                'warning_sent_at' => null,
            ]);
        } elseif ($ticket->status === 'pending') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Notify assigned agent about client reply
        if ($ticket->assigned_to && $ticket->user) {
            $ticket->user->notify(new ClientReplied($ticket));
        }

        return back()->with('success', 'Your reply has been submitted!');
    }
}
