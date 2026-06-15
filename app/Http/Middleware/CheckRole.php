<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * وسيط التحقق من الدور — يمنع أو يسمح بالوصول بناءً على دور المستخدم
 */
class CheckRole
{
    /**
     * Handle an incoming request and check user role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->user_type, $roles)) {
            return response()->json(['message' => 'Unauthorized. Required role: ' . implode(', ', $roles)], 403);
        }

        return $next($request);
    }
}
