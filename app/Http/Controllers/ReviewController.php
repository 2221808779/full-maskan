<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * تحكم التقييمات (API) — عرض وتقديم التقييمات للعقارات والمالكين والفنيين
 */
class ReviewController extends Controller
{
    /**
     * List reviews with optional filters (by property, technician, or the authenticated user).
     *
     * Admins see all reviews.
     *
     * GET /api/reviews
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with('user', 'property');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->user()->user_type === 'admin') {
            // admins see all
        } elseif ($request->filled('my')) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->whereHas('property', fn($q) => $q->where('status', '!=', 'pending'));
        }

        $reviews = $query->latest()->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Get details of a single review.
     *
     * GET /api/reviews/{review}
     *
     * @param Review $review
     * @return JsonResponse
     */
    public function show(Review $review): JsonResponse
    {
        $review->load('user', 'property');

        return response()->json($review);
    }

    /**
     * Create a new review for a property, technician, or owner.
     *
     * POST /api/reviews
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required_without_all:technician_id,owner_id|exists:properties,id',
            'technician_id' => 'sometimes|exists:users,id',
            'owner_id' => 'sometimes|exists:users,id',
            'booking_id' => 'sometimes|exists:bookings,id',
            'stars' => 'required_without:rating|integer|min:1|max:5',
            'rating' => 'required_without:stars|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['rating']) && empty($validated['stars'])) {
            $validated['stars'] = $validated['rating'];
        }
        unset($validated['rating']);

        if (isset($validated['owner_id'])) {
            $existing = Review::where('user_id', $request->user()->id)
                ->where('owner_id', $validated['owner_id'])
                ->first();
            if ($existing) {
                return response()->json(['message' => __('You already reviewed this owner')], 409);
            }
        } elseif (isset($validated['property_id'])) {
            $existing = Review::where('user_id', $request->user()->id)
                ->where('property_id', $validated['property_id'])
                ->first();
            if ($existing) {
                return response()->json(['message' => __('You already reviewed this property')], 409);
            }
        }

        if (isset($validated['booking_id'])) {
            $booking = Booking::findOrFail($validated['booking_id']);
            if ($booking->user_id !== $request->user()->id || $booking->status !== 'completed') {
                return response()->json(['message' => __('You can only review completed bookings')], 403);
            }
        }

        unset($validated['booking_id']);
        $validated['user_id'] = $request->user()->id;
        $review = Review::create($validated);
        $review->load('user', 'property');

        if (isset($validated['property_id'])) {
            $this->updatePropertyRating($validated['property_id']);
        }

        return response()->json([
            'message' => __('Review created successfully'),
            'review' => $review,
        ], 201);
    }

    /**
     * Update a review. Only the review author can update.
     *
     * PUT /api/reviews/{review}
     *
     * @param Request $request
     * @param Review $review
     * @return JsonResponse
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['rating'])) {
            $validated['stars'] = $validated['rating'];
        }
        unset($validated['rating']);

        $review->update($validated);
        $review->load('user', 'property');

        $this->updatePropertyRating($review->property_id);

        return response()->json([
            'message' => __('Review updated successfully'),
            'review' => $review,
        ]);
    }

    /**
     * Delete a review. Only the review author or an admin can delete.
     *
     * DELETE /api/reviews/{review}
     *
     * @param Request $request
     * @param Review $review
     * @return JsonResponse
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $propertyId = $review->property_id;
        $review->delete();

        $this->updatePropertyRating($propertyId);

        return response()->json(['message' => __('Review deleted successfully')]);
    }

    /**
     * List all reviews for a specific property.
     *
     * GET /api/properties/{property}/reviews
     *
     * @param Property $property
     * @return JsonResponse
     */
    public function propertyReviews(Property $property): JsonResponse
    {
        $reviews = Review::with('user')
            ->where('property_id', $property->id)
            ->latest()
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Recalculate and update the average rating and review count for a property.
     *
     * @param int $propertyId
     * @return void
     */
    private function updatePropertyRating(int $propertyId): void
    {
        $property = Property::find($propertyId);
        if (!$property) {
            return;
        }

        $avg = Review::where('property_id', $propertyId)->avg('stars');
        $count = Review::where('property_id', $propertyId)->count();

        $property->updateQuietly([
            'rating' => round($avg ?? 0, 1),
            'review_count' => $count,
        ]);
    }
}
