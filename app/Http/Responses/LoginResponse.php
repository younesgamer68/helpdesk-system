<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user && $user->company) {
            if ($user->isAdmin()) {
                return redirect()->route('agent.dashboard', ['company' => $user->company->slug]);
            }

            if ($user->isOperator()) {
                return redirect()->route('agent.dashboard', ['company' => $user->company->slug]);
            }

            return redirect()->route('dashboard', ['company' => $user->company->slug]);
        }

        return redirect('/');
    }
}
