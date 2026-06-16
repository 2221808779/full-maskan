<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تحكم الحجوزات (Web) — عرض وإدارة حجوزات المستخدم عبر واجهة الويب
 */
class WebBookingController extends Controller
{
    /**
     * Display a paginated list of bookings for the authenticated user.
     * GET /bookings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $query = Booking::with('property', 'property.owner', 'user', 'payment');

        if ($request->user()->user_type === 'owner') {
            $query->whereIn('property_id', Property::where('owner_id', auth()->id())->pluck('id'));
        } elseif ($request->user()->user_type === 'tenant') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Display the details of a specific booking.
     * GET /bookings/{booking}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Booking $booking): View
    {
        $user = $request->user();
        if ($user->user_type !== 'admin' && $booking->user_id !== $user->id && $booking->property->owner_id !== $user->id) {
            abort(403, __('Unauthorized action'));
        }

        $booking->load('property', 'user', 'payment');

        $review = \App\Models\Review::where('property_id', $booking->property_id)
            ->where('user_id', $booking->user_id)
            ->first();

        return view('bookings.show', compact('booking', 'review'));
    }

    /**
     * Confirm a pending booking (owner only).
     * POST /bookings/{booking}/confirm
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Booking $booking): RedirectResponse
    {
        if ($booking->property->owner_id !== auth()->id()) {
            return back()->with('error', __('Unauthorized action'));
        }

        $booking->update(['status' => 'confirmed']);
        $booking->property->update(['status' => 'booked']);

        Notification::create([
            'user_id' => $booking->user_id,
            'title' => __('Your booking confirmed'),
            'content' => __('Your booking for property :property has been confirmed.', ['property' => $booking->property->title]),
        ]);

        return back()->with('success', __('Booking confirmed successfully'));
    }

    /**
     * Start the stay for a confirmed booking (owner only).
     * POST /bookings/{booking}/checkin
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkin(Booking $booking): RedirectResponse
    {
        if ($booking->property->owner_id !== auth()->id()) {
            return back()->with('error', __('Unauthorized action'));
        }

        if ($booking->status !== 'confirmed') {
            return back()->with('error', __('Only confirmed bookings can start stay'));
        }

        if (now()->startOfDay()->lt(\Carbon\Carbon::parse($booking->start_date)->startOfDay())) {
            return back()->with('error', __('Check-in date has not arrived yet'));
        }

        $booking->update(['status' => 'in_progress']);

        Notification::create([
            'user_id' => $booking->user_id,
            'title' => __('Stay start confirmed'),
            'content' => __('Your stay at :property has started. Enjoy!', ['property' => $booking->property->title]),
        ]);

        return back()->with('success', __('Stay start confirmed'));
    }

    /**
     * Complete a booking and release the property (owner only).
     * POST /bookings/{booking}/complete
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Booking $booking): RedirectResponse
    {
        if ($booking->property->owner_id !== auth()->id()) {
            return back()->with('error', __('Unauthorized action'));
        }

        if (!in_array($booking->status, ['confirmed', 'in_progress'])) {
            return back()->with('error', __('Cannot complete this booking'));
        }

        $booking->update(['status' => 'completed']);
        $booking->property->update(['status' => 'available']);

        Notification::create([
            'user_id' => $booking->user_id,
            'title' => __('Your booking completed'),
            'content' => __('Your booking for :property is completed. Thank you.', ['property' => $booking->property->title]),
        ]);

        return back()->with('success', __('Booking completed successfully'));
    }

    /**
     * Cancel a booking (tenant or owner).
     * POST /bookings/{booking}/cancel
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== $request->user()->id && $booking->property->owner_id !== $request->user()->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        if (!in_array($booking->status, ['pending', 'confirmed', 'in_progress'])) {
            return back()->with('error', __('Cannot cancel this booking'));
        }

        $booking->update(['status' => 'cancelled']);
        if ($booking->property->status === 'booked') {
            $booking->property->update(['status' => 'available']);
        }

        $notifyUserId = $booking->user_id === $request->user()->id
            ? $booking->property->owner_id
            : $booking->user_id;

        $msg = __('Booking cancelled for property :property.', ['property' => $booking->property->title]);

        Notification::create([
            'user_id' => $notifyUserId,
            'title' => __('Booking cancelled'),
            'content' => $msg,
        ]);

        return back()->with('success', __('Booking cancelled successfully'));
    }

    /**
     * Store or update a review for the booked property (tenant only).
     * يستخدم updateOrCreate لربط تقييم واحد لكل مستخدم لكل عقار
     * POST /bookings/{booking}/review
     */
    public function storeReview(Request $request, Booking $booking): RedirectResponse
    {
        // التأكد أن المستخدم هو صاحب الحجز
        if ($booking->user_id !== $request->user()->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        // التقييم فقط للحجوزات المكتملة
        if ($booking->status !== 'completed') {
            return back()->with('error', __('You can only review completed bookings'));
        }

        $validated = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // إنشاء أو تحديث التقييم — مستخدم واحد لكل عقار
        $review = Review::updateOrCreate(
            [
                'user_id' => $booking->user_id,
                'property_id' => $booking->property_id,
            ],
            [
                'stars' => $validated['stars'],
                'comment' => $validated['comment'] ?? null,
                'booking_id' => $booking->id,
            ]
        );

        // إعادة حساب متوسط التقييمات للعقار
        $property = Property::find($booking->property_id);
        if ($property) {
            $avg = Review::where('property_id', $property->id)->avg('stars');
            $count = Review::where('property_id', $property->id)->count();
            $property->updateQuietly([
                'rating' => round($avg ?? 0, 1),
                'review_count' => $count,
            ]);
        }

        return back()->with('success', $review->wasRecentlyCreated ? __('Review created successfully') : __('Review updated successfully'));
    }
}
