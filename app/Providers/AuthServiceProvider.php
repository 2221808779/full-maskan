<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * مزود خدمات المصادقة — تسجيل سياسات التفويض (Policies)
 */
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
