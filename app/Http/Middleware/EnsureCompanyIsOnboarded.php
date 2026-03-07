<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsOnboarded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->company && is_null($request->user()->company->onboarding_completed_at)) {
            // Check if they are already trying to access an onboarding route to avoid redirect loops
            if (! $request->routeIs('onboarding.*')) {
                return redirect()->route('onboarding.wizard', ['company' => $request->user()->company->slug]);
            }
        }

        return $next($request);
    }
}
