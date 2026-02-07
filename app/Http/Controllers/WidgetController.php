<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\WidgetSetting;
use App\Mail\TicketVerification;
use App\Mail\TicketVerified;
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
            ->with(['company', 'company.categories'])
            ->firstOrFail();

        return view('widget.form', compact('widget'));
    }

    /**
     * Submit a ticket from the widget
     */
    public function submit(Request $request, $companySlug, $key)
    {
        $company = request()->get('company');
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
            'category_id' => $widget->show_category ? 'nullable|exists:ticket_categories,id' : 'nullable',
        ];

        $validated = $request->validate($rules);

        // Generate unique ticket number
        do {
            $ticketNumber = 'TKT-' . strtoupper(Str::random(6));
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        // Generate verification token
        $verificationToken = Str::random(64);

        // Create the ticket (unverified)
        $ticket = Ticket::create([
            'company_id' => $widget->company_id,
            'ticket_number' => $ticketNumber,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'] ?? null,
            'priority' => $widget->default_priority,
            'status' => $widget->default_status,
            'assigned_to' => $widget->default_assigned_to,
            'verified' => false,
            'verification_token' => $verificationToken,
        ]);

        // Send verification email
        Mail::to($ticket->customer_email)->send(new TicketVerification($ticket));

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
            'verification_token' => null, // Clear the token
        ]);

        // Generate tracking token (different from verification token)
        $trackingToken = Str::random(64);
        $ticket->update(['verification_token' => $trackingToken]); // Reuse the column for tracking

        // Send tracking email
        Mail::to($ticket->customer_email)->send(new TicketVerified($ticket, $trackingToken));

        return view('widget.verified', compact('ticket'));
    }

    /**
     * Show ticket tracking page
     */
    public function track(Company $company, $ticketNumber, $token)
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->where('company_id', $company->id)
            ->where('verification_token', $token)
            ->where('verified', true)
            ->with(['category', 'company'])
            ->firstOrFail();

        // Get all public replies
        $replies = TicketReply::where('ticket_id', $ticket->id)
            ->where('is_internal', false)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('widget.track', compact('ticket', 'replies'));
    }

    /**
     * Submit a reply from customer
     */
    public function reply(Request $request, Company $company, $ticketNumber, $token)
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->where('company_id', $company->id)
            ->where('verification_token', $token)
            ->where('verified', true)
            ->firstOrFail();

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        // Create reply from customer
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'customer_name' => $ticket->customer_name,
            'message' => $validated['message'],
            'is_internal' => false,
        ]);

        // Update ticket status if it was closed/resolved
        if (in_array($ticket->status, ['closed', 'resolved'])) {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Your reply has been submitted!');
    }
}
