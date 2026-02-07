<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If no user is authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Get company slug from route parameter
        $companySlug = $request->route('company');

        // If no company in route, proceed (might be on a different page)
        if (!$companySlug) {
            return $next($request);
        }

        // Check if user's company slug matches the route slug
        if ($user->company && $user->company->slug !== $companySlug) {
            // User is trying to access another company - redirect to their own
            abort(403, 'Unauthorized access to this company.');
        }

        // If user has no company, something is wrong
        if (!$user->company) {
            abort(403, 'No company assigned to your account.');
        }

        return $next($request);
    }
}
