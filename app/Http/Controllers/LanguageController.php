<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

/**
 * تحكم اللغة — تبديل لغة التطبيق بين العربية والإنجليزية مع حفظ التفضيل
 */
class LanguageController extends Controller
{
    /**
     * تبديل اللغة — التبديل بين العربية والإنجليزية وحفظ التفضيل في الجلسة والكوكي
     */
    public function switch($locale)
    {
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'ar';
        }

        App::setLocale($locale);
        Session::put('locale', $locale);
        Cookie::queue('locale', $locale, 60 * 24 * 365);

        return redirect()->back();
    }
}
