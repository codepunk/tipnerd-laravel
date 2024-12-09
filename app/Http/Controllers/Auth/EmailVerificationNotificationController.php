<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'auth.verification_link_sent',
                'message' => __('auth.verification_link_sent')
            ], Response::HTTP_OK);

        } else {
            return back()->with('status', 'verification-link-sent');
        }
    }
}
