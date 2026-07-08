<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * تحكم الدردشة (Web) — عرض المحادثات وإرسال الرسائل بين المالكين والمستأجرين عبر الويب
 */
class WebChatController extends Controller
{
    /**
     * قائمة المحادثات — عرض المحادثات النشطة للمستخدم
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $sent = Message::where('sender_id', $userId)
            ->where(function ($q) use ($userId) {
                $q->whereNull('deleted_for')
                  ->orWhereRaw('JSON_CONTAINS(deleted_for, ?) = 0', [json_encode($userId)]);
            })
            ->select('receiver_id as other_user_id', DB::raw('MAX(created_at) as last_time'))
            ->groupBy('receiver_id');

        $received = Message::where('receiver_id', $userId)
            ->where(function ($q) use ($userId) {
                $q->whereNull('deleted_for')
                  ->orWhereRaw('JSON_CONTAINS(deleted_for, ?) = 0', [json_encode($userId)]);
            })
            ->select('sender_id as other_user_id', DB::raw('MAX(created_at) as last_time'))
            ->groupBy('sender_id');

        $otherIds = DB::query()->fromSub($sent->union($received), 'combined')
            ->select('other_user_id')
            ->orderByDesc('last_time')
            ->pluck('other_user_id');

        $conversations = User::whereIn('id', $otherIds)->get()->sortBy(function ($user) use ($otherIds) {
            return $otherIds->search($user->id);
        });

        return view('conversations.index', compact('conversations'));
    }

    /**
     * عرض المحادثة — عرض الرسائل مع مستخدم معين وتحديدها كمقروءة
     */
    public function show(Request $request, $userId): View
    {
        $otherUser = User::findOrFail($userId);

        if ($otherUser->id === $request->user()->id) {
            abort(403);
        }

        $userId2 = $request->user()->id;

        Message::where('sender_id', $userId)
            ->where('receiver_id', $userId2)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where(function ($q) use ($userId2, $userId) {
            $q->where(function ($sub) use ($userId2, $userId) {
                $sub->where('sender_id', $userId2)->where('receiver_id', $userId);
            })->orWhere(function ($sub) use ($userId2, $userId) {
                $sub->where('sender_id', $userId)->where('receiver_id', $userId2);
            });
        })->where(function ($q) use ($userId2) {
            $q->whereNull('deleted_for')
              ->orWhereRaw('JSON_CONTAINS(deleted_for, ?) = 0', [json_encode($userId2)]);
        })->with('sender')->orderBy('created_at')->paginate(50);

        return view('conversations.show', compact('messages', 'otherUser'));
    }

    /**
     * إرسال رسالة — إرسال رسالة جديدة لمستخدم آخر
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message_text' => 'required|string',
        ]);

        if ((int) $validated['receiver_id'] === (int) $request->user()->id) {
            return back()->withErrors(['receiver_id' => __('You cannot message yourself')]);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiver_id'],
            'message_text' => $validated['message_text'],
        ]);

        $message->load('sender');
        try { event(new MessageSent($message)); } catch (\Exception $e) {}

        return redirect()->route('messages.show', $validated['receiver_id'])
            ->with('success', __('Message sent'));
    }

    /**
     * تعديل رسالة — تعديل نص رسالة مرسلة (فقط المرسل)
     */
    public function editMessage(Request $request, Message $message): RedirectResponse
    {
        if ($message->sender_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message_text' => 'required|string',
        ]);

        $message->update([
            'message_text' => $validated['message_text'],
            'edited_at' => now(),
        ]);

        return redirect()->route('messages.show', $message->receiver_id)
            ->with('success', __('Message edited'));
    }

    /**
     * حذف المحادثة — حذف المحادثة بالكامل للمستخدم
     */
    public function deleteConversation(Request $request, $userId): RedirectResponse
    {
        $authId = $request->user()->id;
        $otherUser = User::findOrFail($userId);

        $messages = Message::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $authId);
        })->get();

        foreach ($messages as $message) {
            $message->update(['deleted_for' => [$authId, $userId]]);
        }

        return redirect()->route('messages.index')
            ->with('success', __('Conversation deleted'));
    }

    /**
     * حذف رسالة — حذف رسالة واحدة للمستخدم أو للجميع
     */
    public function deleteMessage(Request $request, Message $message): RedirectResponse
    {
        $userId = $request->user()->id;

        if ($message->sender_id !== $userId && $message->receiver_id !== $userId && $request->user()->user_type !== 'admin') {
            abort(403);
        }

        $message->update(['deleted_for' => [$message->sender_id, $message->receiver_id]]);

        $redirectId = $message->sender_id === $userId ? $message->receiver_id : $message->sender_id;

        return redirect()->route('messages.show', $redirectId)
            ->with('success', __('Message deleted'));
    }
}
