<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * وسيط تعيين اللغة — ضبط لغة التطبيق بناءً على الجلسة أو الكوكي
 */
class SetLocale
{
    /**
     * Handle an incoming request and set the application locale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale', 'en');

        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } elseif ($request->has('lang') && in_array($request->lang, ['ar', 'en'])) {
            $locale = $request->lang;
            Session::put('locale', $locale);
            Cookie::queue('locale', $locale, 60 * 24 * 365);
        } elseif ($request->cookie('locale')) {
            $locale = $request->cookie('locale');
            Session::put('locale', $locale);
        } elseif ($request->header('Accept-Language')) {
            $headerLang = substr($request->header('Accept-Language'), 0, 2);
            if (in_array($headerLang, ['ar', 'en'])) {
                $locale = $headerLang;
            }
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
