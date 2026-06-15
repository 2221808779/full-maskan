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
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
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
            'city' => ['required', 'string', 'max:255', Rule::in($this->libyanCities())],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|string',
            'status' => 'nullable|in:available,unavailable,pending,booked,maintenance',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    /**
     * Get the list of valid Libyan city names.
     */
    public function libyanCities(): array
    {
        return [
            'طرابلس', 'بنغازي', 'مصراتة', 'الخمس', 'زليتن',
            'صبراتة', 'صرمان', 'العجيلات', 'الجميل', 'ركدالين',
            'الزاوية', 'غريان', 'يفرن', 'الأصابعة', 'ككلة',
            'الرجبان', 'الحشان', 'مزدة', 'نالوت', 'غدامس',
            'تاجوراء', 'جنزور', 'قصر بن غشير', 'درنة', 'طبرق',
            'البيضاء', 'شحات', 'المرج', 'القبة', 'الكفرة',
            'أجدابيا', 'سرت', 'رأس لانوف', 'سبها', 'مرزق',
            'أوباري', 'غات', 'ترهونة', 'بني وليد', 'زوارة',
            'العزيزية', 'هون', 'مردوم', 'تازربو', 'الجغبوب',
            'أوجلة', 'السائح', 'وادي الآجال', 'القطرون', 'الوشكة',
            'سلطان', 'التميمي', 'إمساعد', 'جخرة', 'البريقة',
            'توكرة', 'سوسة', 'الأبرق', 'وادي الشاطئ', 'السدرة',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
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
