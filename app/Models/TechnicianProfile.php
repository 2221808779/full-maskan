<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الملف الشخصي للفني — يمثل بيانات إضافية للفني مع الوصف والصورة الرمزية
 */
class TechnicianProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
    ];

    protected $appends = [
        'avg_rating',
        'reviews_count',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
        ];
    }

    /**
     * المستخدم — المستخدم المرتبط بهذا الملف الفني
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * التخصصات — تخصصات الفني (علاقة متعدد إلى متعدد)
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialty::class, 'technician_specializations', 'profile_id', 'specialization_id');
    }

    /**
     * متوسط التقييم — حساب متوسط تقييمات الفني
     */
    public function getAvgRatingAttribute(): float
    {
        return (float) Review::where('technician_id', $this->user_id)
            ->avg('stars') ?? 0.0;
    }

    /**
     * عدد التقييمات — إجمالي عدد تقييمات الفني
     */
    public function getReviewsCountAttribute(): int
    {
        return Review::where('technician_id', $this->user_id)->count();
    }
}