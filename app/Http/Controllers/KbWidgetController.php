<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class KbWidgetController extends Controller
{
    public function snippet(Request $request, $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $apiUrl = route('api.kb.search', $company->slug);
        $portalUrl = route('kb.public.home', $company->slug);

        $js = view('kb.widget-js', compact('company', 'apiUrl', 'portalUrl'))->render();

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
