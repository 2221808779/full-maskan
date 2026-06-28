<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status === 'suspended') {
            if ($request->user()->banned_until && now()->greaterThan($request->user()->banned_until)) {
                $request->user()->update([
                    'status' => 'active',
                    'ban_reason' => null,
                    'banned_at' => null,
                    'banned_until' => null,
                ]);
                return $next($request);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => __('Your account has been suspended.')], 401);
            }

            return redirect()->route('login')->withErrors([
                'phone' => __('This account is not active, please contact administration'),
            ]);
        }

        return $next($request);
    }
}
