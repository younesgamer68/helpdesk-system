<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired invitation link.');
        }

        // If the user already has a password, they aren't pending. Route them to login.
        if ($user->password !== null) {
            return redirect()->route('login')->with('status', 'You have already accepted your invitation. Please log in.');
        }

        // Drop their email into the volatile session to unlock the SetPassword view
        session()->put('pending_user_email', $user->email);

        return redirect()->route('set-password');
    }
}
