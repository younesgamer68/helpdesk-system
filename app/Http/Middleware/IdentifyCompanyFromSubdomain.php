<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyCompanyFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Extract subdomain
        $subdomain = $this->getSubdomain($host);

        // Skip for main domain or www
        if (!$subdomain || in_array($subdomain, ['www', 'api'])) {
            return $next($request);
        }

        // Find company by slug (subdomain)
        $company = Company::where('slug', $subdomain)->first();

        if (!$company) {
            abort(404, 'Company not found: ' . $subdomain);
        }

        // Share company with all views and attach to request
        $request->merge(['company' => $company]);
        view()->share('company', $company);

        return $next($request);
    }

    private function getSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];

        // For .test domains (Herd/Valet)
        if (str_contains($host, '.test')) {
            // Split by dots
            $parts = explode('.', $host);

            // If we have subdomain.helpdesk-system.test (3 parts)
            if (count($parts) === 3) {
                return $parts[0]; // Return the subdomain
            }

            // If we have just helpdesk-system.test (2 parts)
            return null; // No subdomain
        }

        return null;
    }
}
