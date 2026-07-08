<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Image;
use App\Models\Notification;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Http\Controllers\Traits\SearchableProperty;

/**
 * تحكم العقارات (Web) — إدارة العقارات كاملة (عرض وإضافة وتحرير وحذف وصور) عبر واجهة الويب
 */
class WebPropertyController extends Controller
{
    use SearchableProperty;

    /**
     * قائمة العقارات — عرض العقارات مع فلترة حسب نوع العقار والحالة والبحث
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Property::with('owner');

        if ($user && $user->user_type === 'owner') {
            $query->where('owner_id', $user->id);
        } else {
            $query->where('status', 'available');
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $words = preg_split('/[\s,]+/', trim($search));
            $stopWords = ['في', 'من', 'إلى', 'الى', 'على', 'عن', 'مع', 'و', 'او', 'أو', 'لل', 'ال', 'بـ', 'ب'];
            $matched = false;
            foreach ($words as $word) {
                $word = trim($word);
                if (mb_strlen($word) < 2 || in_array($word, $stopWords)) continue;
                $query->where('title', 'like', "%{$word}%");
                $matched = true;
            }
            if (!$matched) {
                $query->where('title', 'like', "%{$search}%");
            }
        }
        $propertyType = $request->filled('property_type') ? $request->property_type : $request->type;
        if ($propertyType) {
            $query->where('property_type', $propertyType);
        }
        if ($user && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        $properties = $query->latest()->paginate(15);

        return view('properties.index', compact('properties'));
    }

    /**
     * نموذج الإضافة — عرض صفحة إضافة عقار جديد
     */
    public function create(): View
    {
        return view('properties.create');
    }

    /**
     * حفظ العقار — تخزين عقار جديد مع الصور وإشعار المشرف
     */
    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $property = Property::create([
            'owner_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'property_type' => $validated['type'],
            'price' => $validated['price'] ?? $validated['price_per_night'],
            'price_per_month' => $validated['price_per_month'] ?? null,
            'deposit' => $validated['deposit'] ?? null,
            'rooms_count' => $validated['rooms_count'],
            'bathrooms_count' => $validated['bathrooms_count'],
            'location' => $validated['city'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'area' => $validated['area_sqm'] ?? null,
            'has_pool' => $validated['has_pool'] ?? false,
            'has_parking' => $validated['has_parking'] ?? false,
            'has_ac' => $validated['has_ac'] ?? false,
            'has_furniture' => $validated['has_furniture'] ?? false,
            'status' => 'pending',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->processAndStoreImage($image);
                Image::create([
                    'property_id' => $property->id,
                    'image_path' => $path,
                    'added_at' => now(),
                ]);
            }
        }

        $admin = User::where('user_type', 'admin')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => __('New property pending review'),
                'content' => __('Property :title has been added by :user and is pending admin review.', ['title' => $property->title, 'user' => $request->user()->full_name]),
            ]);
        }

        return redirect()->route('properties.show', $property)
            ->with('success', __('Property added successfully'));
    }

    /**
     * عرض عقار — تفاصيل عقار مع الصور والحجوزات والتقييمات
     */
    public function show(Property $property): View
    {
        $property->load('images', 'owner', 'bookings.user', 'reviews.user', 'activePrediction');

        return view('properties.show', compact('property'));
    }

    /**
     * نموذج التعديل — عرض صفحة تعديل بيانات العقار
     */
    public function edit(Property $property): View
    {
        return view('properties.edit', compact('property'));
    }

    /**
     * تحديث العقار — تعديل بيانات العقار وإدارة الصور المحذوفة والجديدة
     */
    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->filled('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = Image::find($imageId);
                if ($image && $image->property_id === $property->id) {
                    $storedPath = str_replace('storage/', '', $image->image_path);
                    if (Storage::disk('public')->exists($storedPath)) {
                        Storage::disk('public')->delete($storedPath);
                    }
                    $image->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->processAndStoreImage($image);
                Image::create([
                    'property_id' => $property->id,
                    'image_path' => $path,
                    'added_at' => now(),
                ]);
            }
        }

        $updatable = array_intersect_key($validated, array_flip([
            'title', 'description', 'type', 'price',
            'price_per_night', 'price_per_month', 'deposit',
            'rooms_count', 'bathrooms_count', 'city', 'status',
            'latitude', 'longitude', 'area_sqm',
            'has_pool', 'has_parking', 'has_ac', 'has_furniture',
        ]));

        if (isset($updatable['type'])) {
            $updatable['property_type'] = $updatable['type'];
            unset($updatable['type']);
        }
        if (isset($updatable['city'])) {
            $updatable['location'] = $updatable['city'];
            unset($updatable['city']);
        }
        if (isset($updatable['price_per_night'])) {
            $updatable['price'] = $updatable['price_per_night'];
            unset($updatable['price_per_night']);
        }
        if (isset($updatable['area_sqm'])) {
            $updatable['area'] = $updatable['area_sqm'];
            unset($updatable['area_sqm']);
        }

        $property->update($updatable);

        Cache::flush();

        return redirect()->route('properties.show', $property)
            ->with('success', __('Property updated successfully'));
    }

    /**
     * معالجة الصورة — تغيير حجم الصورة وتحويلها إلى WebP وحفظها
     */
    private function processAndStoreImage($image): string
    {
        $img = \Intervention\Image\ImageManager::gd()->read($image);
        $img->resize(height: 1200);

        $filename = uniqid() . '_' . time() . '.webp';
        $path = 'properties/' . $filename;

        Storage::disk('public')->put($path, $img->toWebp(80));

        return 'storage/' . $path;
    }

    /**
     * حذف العقار — حذف عقار (فقط المالك أو المشرف)
     */
    public function destroy(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        if ($user->user_type !== 'admin' && $property->owner_id !== $user->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', __('Property deleted successfully'));
    }
}
