<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * مزود خدمات البث — تسجيل قنوات البث الخاصة (Broadcasting)
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any broadcast services.
     *
     * @return void
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => 'auth']);

        require base_path('routes/channels.php');
    }
}
