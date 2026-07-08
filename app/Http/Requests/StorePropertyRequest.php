<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * طلب إضافة عقار — التحقق من بيانات العقار الجديد والصور
 */
class StorePropertyRequest extends FormRequest
{
    /**
     * التفويض — السماح للجميع بإضافة عقار (يمكن للمستخدمين فقط عبر middleware)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق — قواعد التحقق من صحة بيانات العقار الجديد
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:resort,rest_house,villa,house,building,apartment',
            'price' => 'nullable|numeric|min:0',
            'price_per_night' => 'required_without:price|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'rooms_count' => 'required|integer|min:0',
            'bathrooms_count' => 'required|integer|min:0',
            'area_sqm' => 'nullable|integer|min:0',
            'city' => ['required', 'string', 'max:255', Rule::in(config('cities.cities', []))],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|string',
            'status' => 'nullable|in:available,unavailable,pending,booked,maintenance',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    /**
     * الرسائل — رسائل الخطأ المخصصة لقواعد التحقق
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان العقار مطلوب',
            'description.required' => 'وصف العقار مطلوب',
            'type.required' => 'نوع العقار مطلوب',
            'price_per_night.required_without' => 'السعر مطلوب',
            'rooms_count.required' => 'عدد غرف النوم مطلوب',
            'bathrooms_count.required' => 'عدد دورات المياه مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'images.*.max' => 'حجم الصورة لا يتجاوز 5 ميجابايت',
            'images.*.mimes' => 'الصورة يجب أن تكون jpeg, png, jpg, gif, webp',
        ];
    }
}
