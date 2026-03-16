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
        if (! $subdomain || in_array($subdomain, ['www', 'api'])) {
            return $next($request);
        }

        // Find company by slug (subdomain)
        $company = Company::where('slug', $subdomain)->first();

        if (! $company) {
            abort(404, 'Company not found: '.$subdomain);
        }

        // Share company with all views and attach to request
        $request->merge(['company' => $company]);
        view()->share('company', $company);

        return $next($request);
    }

    private function getSubdomain(string $host): ?string
    {
        $baseDomain = config('app.domain');
        if (! $baseDomain) {
            return null;
        }
        $host = explode(':', $host)[0]; // strip port
        $suffix = '.'.$baseDomain;
        if (str_ends_with($host, $suffix)) {
            $subdomain = substr($host, 0, strlen($host) - strlen($suffix));
            if ($subdomain && ! str_contains($subdomain, '.')) {
                return $subdomain;
            }
        }

        return null;
    }
}
