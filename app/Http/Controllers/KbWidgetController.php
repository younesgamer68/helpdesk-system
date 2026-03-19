<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KbWidgetController extends Controller
{
    public function snippet(Request $request, $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $apiUrl = route('api.kb.search', $company->slug);
        $portalUrl = route('kb.public.home', $company->slug);
        $logoUrl = $company->logo ? Storage::url($company->logo) : null;

        $js = view('kb.widget-js', compact('company', 'apiUrl', 'portalUrl', 'logoUrl'))->render();

        $cacheControl = config('app.env') === 'local'
            ? 'no-store, no-cache, must-revalidate, max-age=0'
            : 'public, max-age=300';

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => $cacheControl,
        ]);
    }
}
