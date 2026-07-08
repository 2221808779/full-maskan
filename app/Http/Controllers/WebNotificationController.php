<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تحكم الإشعارات (Web) — عرض وإدارة الإشعارات عبر واجهة الويب
 */
class WebNotificationController extends Controller
{
    /**
     * قائمة الإشعارات — عرض إشعارات المستخدم المسجل
     */
    public function index(Request $request): View
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * تحديد كمقروء — تعيين إشعار واحد كمقروء
     */
    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        $notification->update(['read_at' => now()]);

        return back()->with('success', __('Notification marked as read'));
    }

    /**
     * تحديد الكل مقروء — تعيين جميع الإشعارات غير المقروءة كمقروءة
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('All notifications marked as read'));
    }

    /**
     * حذف إشعار — حذف إشعار واحد (فقط صاحب الإشعار)
     */
    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        $notification->delete();

        return back()->with('success', __('Notification deleted'));
    }
}
