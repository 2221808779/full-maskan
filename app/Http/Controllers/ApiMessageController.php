<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * تحكم الرسائل (API) — إدارة المحادثات والرسائل بين المستخدمين مع دعم التحرير والحذف والقراءة
 */
class ApiMessageController extends Controller
{
    /**
     * List the authenticated user's conversations (unique chat partners with last message).
     *
     * GET /api/messages/conversations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $conversations = Message::where('type', 'message')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->where(function ($q) use ($userId) {
                $q->whereNull('deleted_for')
                  ->orWhereJsonDoesntContain('deleted_for', $userId);
            })
            ->select('sender_id', 'receiver_id', DB::raw('MAX(id) as last_message_id'))
            ->groupBy('sender_id', 'receiver_id')
            ->orderByDesc('last_message_id')
            ->get();

        $conversationList = [];
        foreach ($conversations as $conv) {
            $otherId = $conv->sender_id === $userId ? $conv->receiver_id : $conv->sender_id;
            $otherUser = User::find($otherId);
            $lastMsg = Message::find($conv->last_message_id);

            $unreadCount = Message::where('type', 'message')
                ->where('sender_id', $otherId)
                ->where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();

            $conversationList[] = [
                'id' => $otherId,
                'user_one_id' => $userId,
                'user_two_id' => $otherId,
                'property_id' => null,
                'maintenance_request_id' => null,
                'last_message_at' => $lastMsg?->sent_at?->toDateTimeString(),
                'other_user_name' => $otherUser?->full_name,
                'other_user_avatar' => $otherUser?->profile_image,
                'other_user_type' => $otherUser?->user_type,
                'last_message' => $lastMsg?->message_text,
                'unread_count' => $unreadCount,
            ];
        }

        return response()->json([
            'data' => $conversationList,
        ]);
    }

    /**
     * Get paginated messages between the authenticated user and another user.
     *
     * GET /api/messages/{user}
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function messages(Request $request, User $user): JsonResponse
    {
        $userId = $request->user()->id;
        $otherId = $user->id;

        $messages = Message::where('type', 'message')
            ->where(function ($q) use ($userId, $otherId) {
                $q->where(function ($sub) use ($userId, $otherId) {
                    $sub->where('sender_id', $userId)->where('receiver_id', $otherId);
                })->orWhere(function ($sub) use ($userId, $otherId) {
                    $sub->where('sender_id', $otherId)->where('receiver_id', $userId);
                });
            })
            ->where(function ($q) use ($userId) {
                $q->whereNull('deleted_for')
                  ->orWhereJsonDoesntContain('deleted_for', $userId);
            })
            ->orderBy('sent_at')
            ->paginate(50);

        $formatted = array_map(function ($msg) use ($userId) {
            return $this->formatMessage($msg, $userId);
        }, $messages->items());

        return response()->json([
            'data' => $formatted,
        ]);
    }

    /**
     * Send a new message to another user.
     *
     * POST /api/messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $receiverId = $validated['conversation_id'];

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $receiverId,
            'message_text' => $validated['message'],
            'type' => 'message',
            'sent_at' => now(),
        ]);

        return response()->json([
            'data' => $this->formatMessage($message, $request->user()->id),
        ], 201);
    }

    /**
     * Edit a message. Only the sender can edit.
     *
     * PUT /api/messages/{message}
     *
     * @param Request $request
     * @param Message $message
     * @return JsonResponse
     */
    public function edit(Request $request, Message $message): JsonResponse
    {
        if ($message->sender_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message->update([
            'message_text' => $validated['message'],
            'edited_at' => now(),
        ]);

        return response()->json([
            'data' => $this->formatMessage($message, $request->user()->id),
        ]);
    }

    /**
     * Soft-delete a message for the authenticated user (marks as deleted_for).
     *
     * DELETE /api/messages/{message}
     *
     * @param Request $request
     * @param Message $message
     * @return JsonResponse
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        if ($message->sender_id !== $request->user()->id && $message->receiver_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $deletedFor = $message->deleted_for ?? [];
        foreach ([$message->sender_id, $message->receiver_id] as $uid) {
            if (!in_array($uid, $deletedFor)) {
                $deletedFor[] = $uid;
            }
        }
        $message->update(['deleted_for' => $deletedFor]);

        return response()->json(['message' => __('Message deleted')]);
    }

    /**
     * Soft-delete an entire conversation (all messages with a user) for the authenticated user.
     *
     * DELETE /api/messages/conversation/{user}
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function deleteConversation(Request $request, User $user): JsonResponse
    {
        $userId = $request->user()->id;
        $otherId = $user->id;

        Message::where('type', 'message')
            ->where(function ($q) use ($userId, $otherId) {
                $q->where(function ($sub) use ($userId, $otherId) {
                    $sub->where('sender_id', $userId)->where('receiver_id', $otherId);
                })->orWhere(function ($sub) use ($userId, $otherId) {
                    $sub->where('sender_id', $otherId)->where('receiver_id', $userId);
                });
            })
            ->each(function ($message) use ($userId, $otherId) {
                $deletedFor = $message->deleted_for ?? [];
                foreach ([$userId, $otherId] as $uid) {
                    if (!in_array($uid, $deletedFor)) {
                        $deletedFor[] = $uid;
                    }
                }
                $message->update(['deleted_for' => $deletedFor]);
            });

        return response()->json(['message' => __('Conversation deleted')]);
    }

    /**
     * Mark all messages from a specific user as read.
     *
     * POST /api/messages/{user}/read
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function markAsRead(Request $request, User $user): JsonResponse
    {
        Message::where('type', 'message')
            ->where('sender_id', $user->id)
            ->where('receiver_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => __('Messages marked as read')]);
    }

    /**
     * Format a message for JSON response.
     *
     * @param Message $message
     * @param int $currentUserId
     * @return array
     */
    private function formatMessage(Message $message, int $currentUserId): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->sender_id === $currentUserId ? $message->receiver_id : $message->sender_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'message_text' => $message->message_text,
            'status' => $message->read_at ? 'read' : 'sent',
            'type' => $message->type,
            'complaint_status' => $message->complaint_status,
            'admin_response' => $message->admin_response,
            'edited_at' => $message->edited_at?->toDateTimeString(),
            'deleted_for' => $message->deleted_for,
            'sent_at' => $message->sent_at?->toDateTimeString(),
            'responded_by' => $message->responded_by,
            'resolved_at' => $message->resolved_at?->toDateTimeString(),
            'created_at' => $message->created_at?->toDateTimeString(),
            'updated_at' => $message->updated_at?->toDateTimeString(),
            'is_edited' => $message->edited_at !== null,
            'is_read' => $message->read_at !== null,
        ];
    }
}
