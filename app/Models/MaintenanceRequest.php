<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج طلب الصيانة — يمثل طلب صيانة مقدم من مستأجر لفني مع فئة المشكلة والحالة والتقييم
 */
class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'technician_id',
        'problem_description',
        'ai_category',
        'ai_accuracy',
        'category',
        'priority',
        'category_id',
        'status',
        'technician_notes',
        'completed_at',
    ];

    protected $appends = [
        'technician_name',
        'technician_phone',
        'property_title',
        'tenant_name',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'ai_accuracy' => 'float',
            'category_id' => 'integer',
        ];
    }

    /**
     * العقار — العقار المرتبط بطلب الصيانة
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * المستأجر — المستخدم الذي قدم طلب الصيانة
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * الفني — الفني المسند إليه طلب الصيانة
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * اسم الفني — إرجاع الاسم الكامل للفني المسند
     */
    public function getTechnicianNameAttribute(): ?string
    {
        return $this->technician?->full_name;
    }

    /**
     * هاتف الفني — إرجاع رقم هاتف الفني المسند
     */
    public function getTechnicianPhoneAttribute(): ?string
    {
        return $this->technician?->phone;
    }

    /**
     * عنوان العقار — إرجاع عنوان العقار المرتبط بطلب الصيانة
     */
    public function getPropertyTitleAttribute(): ?string
    {
        return $this->property?->title;
    }

    /**
     * اسم المستأجر — إرجاع الاسم الكامل لمقدم طلب الصيانة
     */
    public function getTenantNameAttribute(): ?string
    {
        return $this->tenant?->full_name;
    }
}