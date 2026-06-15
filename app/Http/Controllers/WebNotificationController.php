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
     * Display a paginated list of notifications for the authenticated user.
     * GET /notifications
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     * POST /notifications/{notification}/read
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\RedirectResponse
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
     * Mark all unread notifications as read for the authenticated user.
     * POST /notifications/read-all
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('All notifications marked as read'));
    }

    /**
     * Delete a single notification.
     * DELETE /notifications/{notification}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\RedirectResponse
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
