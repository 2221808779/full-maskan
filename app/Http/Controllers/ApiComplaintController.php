<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * تحكم الشكاوى (API) — إدارة الشكاوى: عرض وإرسال للمستخدمين والمسؤولين
 */
class ApiComplaintController extends Controller
{
    /**
     * قائمة الشكاوى — المشرف يرى الكل، المستخدم يرى شكاواه فقط
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $complaints = Message::with('sender', 'responder')->where('type', 'complaint');

        if ($user->user_type !== 'admin') {
            $complaints = $complaints->where('sender_id', $user->id);
        }

        $complaints = $complaints->latest('sent_at')->paginate(20);

        return response()->json([
            'data' => $complaints->items(),
            'complaints' => $complaints->items(),
            'meta' => [
                'current_page' => $complaints->currentPage(),
                'last_page' => $complaints->lastPage(),
                'total' => $complaints->total(),
            ],
        ]);
    }

    /**
     * عرض شكوى — تفاصيل شكوى محددة عبر API
     */
    public function show(Message $complaint): JsonResponse
    {
        if ($complaint->type !== 'complaint') {
            return response()->json(['message' => __('Not found')], 404);
        }

        $complaint->load('sender', 'responder');

        return response()->json(['data' => $complaint]);
    }

    /**
     * إرسال شكوى — تقديم شكوى جديدة إلى المشرف عبر API
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|string',
        ]);

        $admin = User::where('user_type', 'admin')->first();

        $complaint = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $admin?->id,
            'message_text' => $validated['title'] . "\n" . $validated['description'],
            'type' => 'complaint',
            'complaint_status' => 'pending',
            'sent_at' => now(),
        ]);

        $complaint->load('sender');

        return response()->json([
            'message' => __('Complaint submitted successfully'),
            'data' => $complaint,
        ], 201);
    }
}
