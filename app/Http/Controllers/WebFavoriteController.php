<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تحكم المفضلة (Web) — عرض وإدارة العقارات المفضلة عبر واجهة الويب
 */
class WebFavoriteController extends Controller
{
    /**
     * Display a paginated list of favorite properties for the authenticated user.
     * GET /favorites
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $favorites = Favorite::with('property')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Toggle a property as favorite (add or remove).
     * POST /favorites/toggle
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $validated['property_id'])
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = __('Property removed from favorites');
        } else {
            Favorite::create([
                'user_id' => $request->user()->id,
                'property_id' => $validated['property_id'],
            ]);
            $message = __('Property added to favorites');
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a specific property from favorites.
     * DELETE /favorites/{favorite}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Favorite  $favorite
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Favorite $favorite): RedirectResponse
    {
        if ($favorite->user_id !== $request->user()->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        $favorite->delete();

        return back()->with('success', __('Property removed from favorites'));
    }
}
