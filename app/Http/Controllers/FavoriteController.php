<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * تحكم المفضلة (API) — إضافة وإزالة وعرض العقارات المفضلة للمستخدم
 */
class FavoriteController extends Controller
{
    /**
     * List the authenticated user's favorite properties.
     *
     * GET /api/favorites
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::with('property')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($favorites);
    }

    /**
     * Toggle a property as favorite (add or remove).
     *
     * POST /api/favorites/toggle
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $validated['property_id'])
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'message' => __('Removed from favorites'),
                'is_favorited' => false,
            ]);
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'property_id' => $validated['property_id'],
        ]);

        return response()->json([
            'message' => __('Added to favorites'),
            'is_favorited' => true,
            'favorite' => $favorite,
        ], 201);
    }

    /**
     * Remove a specific favorite entry. Only the user who added it can remove.
     *
     * DELETE /api/favorites/{favorite}
     *
     * @param Favorite $favorite
     * @return JsonResponse
     */
    public function destroy(Favorite $favorite): JsonResponse
    {
        if ($favorite->user_id !== request()->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $favorite->delete();

        return response()->json(['message' => __('Removed from favorites')]);
    }

    /**
     * Check if a property is in the authenticated user's favorites.
     *
     * POST /api/favorites/check
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $isFavorited = Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $validated['property_id'])
            ->exists();

        return response()->json(['is_favorited' => $isFavorited]);
    }
}
