<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * طلب تحديث عقار — التحقق من بيانات تحديث العقار والصور الجديدة
 */
class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $property = $this->route('property');
        return $property && $property->owner_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:resort,rest_house,villa,house,building,apartment',
            'price' => 'sometimes|required|numeric|min:0',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'rooms_count' => 'sometimes|required|integer|min:0',
            'bathrooms_count' => 'sometimes|required|integer|min:0',
            'area_sqm' => 'nullable|integer|min:0',
            'city' => ['sometimes', 'required', 'string', 'max:255', Rule::in(config('cities.cities', []))],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|string',
            'status' => 'nullable|in:available,unavailable,pending,booked,maintenance',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer',
        ];
    }

}
