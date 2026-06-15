<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * وسيط فرض HTTPS — إعادة توجيه الطلبات إلى HTTPS في بيئة الإنتاج
 */
class ForceHttps
{
    /**
     * Handle an incoming request and force HTTPS in production.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->secure() && config('app.env') === 'production') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
