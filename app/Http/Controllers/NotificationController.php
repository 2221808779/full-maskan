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
     * List the authenticated user's notifications.
     *
     * GET /api/notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Mark a single notification as read.
     *
     * POST /api/notifications/{notification}/read
     *
     * @param Request $request
     * @param Notification $notification
     * @return JsonResponse
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
     * Mark all of the authenticated user's notifications as read.
     *
     * POST /api/notifications/read-all
     *
     * @param Request $request
     * @return JsonResponse
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
     * Get the count of unread notifications for the authenticated user.
     *
     * GET /api/notifications/unread-count
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count, 'unread_count' => $count]);
    }
}
