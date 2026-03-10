<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Update user activity timestamp on every request.
     * Throttled to once per minute via cache to avoid DB spam.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'user-activity-' . $user->id;

            if (!Cache::has($cacheKey)) {
                $user->update([
                    'status' => 'online',
                    'last_activity' => now(),
                ]);

                Cache::put($cacheKey, true, 60);
            }
        }

        return $next($request);
    }
}
