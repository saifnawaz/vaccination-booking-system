<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out a user whose account was blocked after they logged in,
 * e.g. an admin deactivated their hospital mid-session.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Re-read the hospital rather than trust an already-loaded relation:
        // its status may have changed since the user was loaded.
        if ($user?->isHospital()) {
            $user->load('hospital');
        }

        $reason = $user?->loginBlockedReason();

        if ($reason === null) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $reason]);
    }
}
