<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * تحكم الحجوزات (API) — إنشاء وعرض وإلغاء الحجوزات مع التحقق من التوفر والصراع
 */
class BookingController extends Controller
{
    /**
     * قائمة الحجوزات — عرض حجوزات المستخدم المسجل
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with('property.images', 'payment')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($bookings);
    }

    /**
     * إنشاء حجز — تقديم طلب حجز جديد مع التحقق من التوفر
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'guests' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $property = Property::lockForUpdate()->findOrFail($validated['property_id']);

            if (!in_array($property->status, ['available', 'pending'])) {
                return response()->json(['message' => __('Property is not available')], 400);
            }

            $days = max(1, now()->parse($validated['start_date'])->diffInDays(now()->parse($validated['end_date'])));
            $totalPrice = $days * $property->price;
            $validated['status'] = 'pending';
            $validated['user_id'] = $request->user()->id;
            $validated['total_price'] = $totalPrice;

            $booking = Booking::create($validated);
            $booking->load('property');

            $owner = User::find($property->owner_id);
            if ($owner) {
                Notification::create([
                    'user_id' => $owner->id,
                    'title' => __('New booking request'),
                    'content' => __('You have a new booking request for property :property from :user.', ['property' => $property->title, 'user' => $request->user()->full_name]),
                ]);
            }

            return response()->json([
                'message' => __('Booking request sent successfully'),
                'booking' => $booking,
            ], 201);
        });
    }

    /**
     * عرض حجز — تفاصيل حجز محدد مع العقار والدفع
     */
    public function show(Booking $booking): JsonResponse
    {
        $booking->load('property', 'payment', 'user');

        return response()->json($booking);
    }

    /**
     * إلغاء حجز — إلغاء حجز (فقط المستأجر صاحب الحجز يمكنه الإلغاء)
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => __('Booking cannot be cancelled')], 400);
        }

        return DB::transaction(function () use ($request, $booking) {
            $booking->update(['status' => 'cancelled']);

            Notification::create([
                'user_id' => $booking->property->owner_id,
                'title' => __('Booking cancelled'),
                'content' => __('Booking cancelled for property :property by the tenant.', ['property' => $booking->property->title]),
            ]);

            return response()->json([
                'message' => __('Booking cancelled successfully'),
                'booking' => $booking,
            ]);
        });
    }

    /**
     * تأكيد حجز — تأكيد حجز معلق (فقط مالك العقار يمكنه التأكيد)
     */
    public function confirm(Request $request, Booking $booking): JsonResponse
    {
        $property = $booking->property;

        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => __('Booking cannot be confirmed')], 400);
        }

        return DB::transaction(function () use ($booking, $property) {
            $booking->update(['status' => 'confirmed']);
            $property->update(['status' => 'booked']);

            Notification::create([
                'user_id' => $booking->user_id,
                'title' => __('Your booking confirmed'),
                'content' => __('Your booking for property :property has been confirmed.', ['property' => $property->title]),
            ]);

            return response()->json([
                'message' => __('Booking confirmed successfully'),
                'booking' => $booking,
            ]);
        });
    }

    /**
     * إكمال حجز — إنهاء حجز مؤكد (فقط المالك يمكنه الإكمال)
     */
    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $property = $booking->property;

        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => __('Booking cannot be completed')], 400);
        }

        return DB::transaction(function () use ($booking, $property) {
            $booking->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $property->update(['status' => 'available']);

            Notification::create([
                'user_id' => $booking->user_id,
                'title' => __('Your booking completed'),
                'content' => __('Your booking for property :property has been completed. Thank you.', ['property' => $property->title]),
            ]);

            return response()->json([
                'message' => __('Booking completed successfully'),
                'booking' => $booking,
            ]);
        });
    }

    /**
     * حجوزات العقارات — عرض حجوزات عقارات المالك المسجل
     */
    public function propertyBookings(Request $request): JsonResponse
    {
        $bookings = Booking::with('user', 'payment')
            ->whereHas('property', fn($q) => $q->where('owner_id', $request->user()->id))
            ->latest()
            ->paginate(20);

        return response()->json($bookings);
    }
}
