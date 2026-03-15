<?php

namespace App\Http\Middleware;

use App\Services\TicketAssignmentService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function __construct(protected TicketAssignmentService $assignmentService)
    {
    }

    /**
     * Update user activity timestamp on every request.
     * Throttled to once per minute via cache to avoid DB spam.
     * When an operator transitions to online, assign pending tickets.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'user-activity-' . $user->id;

            if (!Cache::has($cacheKey)) {
                $wasOffline = $user->status !== 'online';

                $user->update([
                    'status' => 'online',
                    'last_activity' => now(),
                ]);

                Cache::put($cacheKey, true, 5);

                // If the operator just came online, assign any pending unassigned tickets
                if ($wasOffline && $user->isOperator()) {
                    $this->assignmentService->assignPendingTickets($user->company_id);
                }
            }
        }

        return $next($request);
    }
}
