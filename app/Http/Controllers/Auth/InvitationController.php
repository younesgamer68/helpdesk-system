<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InviteExpiredMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            $expiresAt = $request->query('expires');
            $isExpiredLink = is_numeric($expiresAt) && now()->timestamp > (int) $expiresAt;

            if ($isExpiredLink && $user->isPendingInvite() && is_null($user->invite_expired_notified_at)) {
                Mail::to($user->email)->queue(new InviteExpiredMail($user));

                $user->update([
                    'invite_expired_notified_at' => now(),
                ]);
            }

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
