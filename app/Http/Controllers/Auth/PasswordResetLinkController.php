<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($request->expectsJson()) {
            return $this->statusResponse($status);
        } else {
            return $status == Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        }
    }

    private function statusCode($status): int
    {
        switch ($status) {
            case Password::INVALID_USER:
                return Response::HTTP_UNPROCESSABLE_ENTITY;
            case Password::INVALID_TOKEN:
                return Response::HTTP_UNAUTHORIZED;
            case Password::RESET_THROTTLED:
                return Response::HTTP_TOO_MANY_REQUESTS;
            default:
                return Response::HTTP_OK;
        }
    }

    private function statusResponse($status): Response
    {
        $formatted = collect([
            'status' => $status,
            'message' => __($status)
        ]);
        switch ($status) {
            case Password::INVALID_USER:
            case Password::INVALID_TOKEN:
                $formatted->put(
                    'errors', [
                        'email' => __($status)
                    ]
                );
                break;
        }
        return response()->json(
            $formatted->toArray(),
            $this->statusCode($status)
        );
    }

}
