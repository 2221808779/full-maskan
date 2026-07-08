<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج العقار — يمثل عقًا مع جميع التفاصيل: المالك والموقع والسعر والنوع والوسائط والحالة
 */
class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'location',
        'price',
        'property_type',
        'rooms_count',
        'bathrooms_count',
        'status',
        'unavailable_dates',
        'latitude',
        'longitude',
        'area',
        'price_per_month',
        'deposit',
        'has_pool',
        'has_parking',
        'has_ac',
        'has_furniture',
        'rating',
        'review_count',
    ];

    /**
     * المالك — المستخدم الذي يملك هذا العقار
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * الحجوزات — جميع حجوزات هذا العقار
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * طلبات الصيانة — جميع طلبات الصيانة الخاصة بهذا العقار
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * التقييمات — جميع تقييمات هذا العقار
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * المفضلة — المستخدمون الذين أضافوا هذا العقار إلى مفضلتهم
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * الصور — جميع صور هذا العقار
     */
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    /**
     * التنبؤ النشط — أحدث تنبؤ صيانة نشط لهذا العقار
     */
    public function activePrediction()
    {
        return $this->hasOne(MaintenancePrediction::class)->where('is_active', true)->latest('id');
    }

    /**
     * تنسيق التاريخ — تحويل التاريخ إلى نص قبل التخزين
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
            'area' => 'float',
            'unavailable_dates' => 'array',
        ];
    }

    /**
     * تحويل إلى مصفوفة — إرجاع بيانات العقار مع الصور المحملة
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['images'] = $this->relationLoaded('images')
            ? $this->images->pluck('image_path')->toArray()
            : [];
        return $data;
    }
}
