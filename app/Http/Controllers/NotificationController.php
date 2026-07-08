<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * تحكم الإشعارات (API) — عرض الإشعارات وتحديث حالة القراءة وحذفها
 */
class NotificationController extends Controller
{
    /**
     * قائمة الإشعارات — عرض إشعارات المستخدم المسجل عبر API
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * تحديد إشعار مقروء — تعيين إشعار واحد كمقروء عبر API
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'message' => __('Notification marked as read'),
            'notification' => $notification,
        ]);
    }

    /**
     * تحديد الكل مقروء — تعيين جميع الإشعارات كمقروءة عبر API
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => __('All notifications marked as read'),
            'updated' => $count,
        ]);
    }

    /**
     * عدد غير المقروء — إرجاع عدد الإشعارات غير المقروءة
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count, 'unread_count' => $count]);
    }
}
