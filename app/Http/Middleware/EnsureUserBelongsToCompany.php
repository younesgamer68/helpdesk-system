<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get company from request (set by IdentifyCompanyFromSubdomain middleware)
        $company = $request->get('company');

        if (!$company) {
            abort(404, 'Company not found');
        }

        // Check if user belongs to this company
        if ($user->company_id !== $company->id) {
            abort(403, 'You do not have access to this company');
        }

        return $next($request);
    }
}
