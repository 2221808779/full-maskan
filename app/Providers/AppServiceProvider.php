<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Notification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * مزود الخدمات الأساسي — تسجيل الخدمات وربط البيانات المشتركة مع جميع طرق العرض
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view) {
            $unreadCount = 0;
            $unreadMsgCount = 0;
            if (auth()->check()) {
                $unreadCount = Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
                $unreadMsgCount = Message::where('receiver_id', auth()->id())
                    ->whereNull('read_at')
                    ->where('type', 'message')
                    ->count();
            }
            $view->with('unreadNotificationsCount', $unreadCount);
            $view->with('unreadMessagesCount', $unreadMsgCount);
        });
    }
}
