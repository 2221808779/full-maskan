<?php

namespace App\Http\Controllers;

use App\Models\BlackoutDate;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Traits\SearchableProperty;

/**
 * تحكم العقارات (API) — عرض وتصفية وفلترة العقارات مع إدارة التواريخ المحجوزة
 */
class PropertyController extends Controller
{
    use SearchableProperty;

    /**
     * قائمة العقارات — عرض العقارات مع فلترة اختيارية (حسب الحالة، النوع، السعر، الموقع، البحث النصي)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::with('owner', 'images');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('rooms_count')) {
            $query->where('rooms_count', $request->rooms_count);
        }
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }
        // البحث النصي — يقسم كلمات البحث ويتجاهل حروف الجر، يبحث في العنوان فقط
        if ($request->filled('search')) {
            $search = $request->search;

            // تقسيم النص إلى كلمات فردية والبحث بها جميعاً في العنوان
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
            // ملاحظة: لا نضيف فلتر نوع أو مدينة تلقائياً — المستخدم يستخدم
            // property_type و location كبارامترات منفصلة للتصفية الإضافية
        }

        // التصفية حسب الموقع الجغرافي باستخدام معادلة Haversine
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude = (float) $request->latitude;
            $longitude = (float) $request->longitude;
            $radius = (float) ($request->radius ?? 50); // نصف القطر الافتراضي 50 كم

            // معادلة حساب المسافة بين نقطتين على الكرة الأرضية
            $haversine = "(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude))))";
            $query->whereRaw("{$haversine} <= ?", [$radius]);
        }

        return response()->json($query->latest('id')->paginate(20));
    }

    /**
     * عرض عقار — إرجاع تفاصيل عقار مع الصور والتقييمات والحجوزات
     */
    public function show(Property $property): JsonResponse
    {
        $property->load('images', 'owner', 'reviews.user', 'bookings');

        $data = $property->toArray();
        $data['images'] = $property->images->pluck('image_path')->toArray();

        return response()->json($data);
    }

    /**
     * إضافة عقار — إنشاء عقار جديد (يتطلب التوثيق)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'rooms_count' => 'required|integer|min:0',
            'bathrooms_count' => 'required|integer|min:0',
            'location' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'area' => 'nullable|integer|min:0',
        ]);

        $validated['owner_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $property = Property::create($validated);
        $property->load('images', 'owner');

        try { Cache::tags(['properties'])->flush(); } catch (\BadMethodCallException $e) {}

        return response()->json([
            'message' => __('Property created successfully'),
            'property' => $property,
        ], 201);
    }

    /**
     * تحديث عقار — تعديل بيانات عقار (فقط المالك يمكنه التحديث)
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'property_type' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'rooms_count' => 'sometimes|integer|min:0',
            'bathrooms_count' => 'sometimes|integer|min:0',
            'location' => 'sometimes|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'area' => 'nullable|integer|min:0',
        ]);

        $property->update($validated);
        $property->load('images', 'owner');

        try { Cache::tags(['properties'])->flush(); } catch (\BadMethodCallException $e) {}

        return response()->json([
            'message' => __('Property updated successfully'),
            'property' => $property,
        ]);
    }

    /**
     * حذف عقار — حذف عقار (فقط المالك أو المشرف يمكنه الحذف)
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        if ($property->owner_id !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $property->delete();

        try { Cache::tags(['properties'])->flush(); } catch (\BadMethodCallException $e) {}

        return response()->json(['message' => __('Property deleted successfully')]);
    }

    /**
     * عقاراتي — عرض عقارات المستخدم المسجل الدخول
     */
    public function myProperties(Request $request): JsonResponse
    {
        $properties = Property::with('images')
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($properties);
    }

    /**
     * تبديل الحالة — تغيير حالة العقار (متاح/محجوز/صيانة/غير متاح)
     */
    public function toggleStatus(Request $request, Property $property): JsonResponse
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:available,booked,maintenance,unavailable',
        ]);

        $property->update(['status' => $validated['status']]);

        try { Cache::tags(['properties'])->flush(); } catch (\BadMethodCallException $e) {}

        return response()->json([
            'message' => __('Property status updated'),
            'property' => $property,
        ]);
    }

    /**
     * التوفر — عرض تقويم التوفر لعقار (تواريخ محجوزة + تواريخ محظورة)
     */
    public function availability(Property $property): JsonResponse
    {
        $bookedDates = Booking::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->get(['start_date', 'end_date']);

        $blackoutDates = BlackoutDate::where('property_id', $property->id)
            ->get(['date', 'status']);

        $bookedRanges = $bookedDates->map(function ($b) {
            return [
                'start' => $b->start_date->format('Y-m-d'),
                'end' => $b->end_date->format('Y-m-d'),
            ];
        });

        $blackouts = $blackoutDates->map(function ($b) {
            return [
                'date' => $b->date->format('Y-m-d'),
                'status' => $b->status,
            ];
        });

        $flatDates = [];
        foreach ($bookedDates as $b) {
            $start = $b->start_date->format('Y-m-d');
            $end = $b->end_date->format('Y-m-d');
            $current = $start;
            while ($current <= $end) {
                $flatDates[] = ['date' => $current, 'status' => 'booked'];
                $current = date('Y-m-d', strtotime($current . ' +1 day'));
            }
        }
        foreach ($blackoutDates as $b) {
            $flatDates[] = ['date' => $b->date->format('Y-m-d'), 'status' => $b->status ?? 'unavailable'];
        }

        // Include owner-blocked dates from unavailable_dates JSON column
        $unavailable = $property->unavailable_dates ?? [];
        foreach ($unavailable as $date) {
            $flatDates[] = ['date' => $date, 'status' => 'unavailable'];
        }

        return response()->json([
            'data' => $flatDates,
            'availability' => [
                'booked_ranges' => $bookedRanges,
                'blackout_dates' => $blackouts,
            ],
        ]);
    }
}
