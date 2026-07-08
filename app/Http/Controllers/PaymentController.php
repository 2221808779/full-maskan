<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * تحكم الدفع (API) — عرض المدفوعات وإنشاء جلسة دفع ومعالجة ردود Plutu
 */
class PaymentController extends Controller
{
    /**
     * قائمة المدفوعات — عرض مدفوعات المستخدم المسجل
     */
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with('booking.property')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($payments);
    }

    /**
     * إنشاء دفعة — تسجيل عملية دفع جديدة
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:deposit,full,partial,refund',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $booking = Booking::lockForUpdate()->findOrFail($validated['booking_id']);

            if ($booking->user_id !== $request->user()->id) {
                return response()->json(['message' => __('Unauthorized')], 403);
            }

            $validated['user_id'] = $request->user()->id;
            $validated['status'] = 'pending';
            $validated['payment_type'] = $validated['type'];
            unset($validated['type']);

            $payment = Payment::create($validated);
            $payment->load('booking.property');

            return response()->json([
                'message' => __('Payment created successfully'),
                'payment' => $payment,
            ], 201);
        });
    }

    /**
     * عرض دفعة — تفاصيل عملية دفع محددة (فقط صاحب الدفعة)
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $payment->load('booking.property', 'user');

        return response()->json($payment);
    }

    /**
     * إكمال الدفع — تعيين حالة الدفع إلى مكتمل
     */
    public function complete(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        return DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            return response()->json([
                'message' => __('Payment completed successfully'),
                'payment' => $payment,
            ]);
        });
    }
}
