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
     * قائمة المفضلة — عرض العقارات المفضلة للمستخدم
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
     * تبديل المفضلة — إضافة أو إزالة عقار من المفضلة
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
     * حذف مفضلة — إزالة عقار من المفضلة (فقط المستخدم صاحبها)
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
     * التحقق من المفضلة — معرفة ما إذا كان العقار في مفضلة المستخدم
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
