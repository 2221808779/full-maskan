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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ai_accuracy' => 'float',
            'category_id' => 'integer',
        ];
    }

    /**
     * Get the property associated with the maintenance request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the tenant who submitted the maintenance request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the technician assigned to the maintenance request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the full name of the assigned technician.
     *
     * @return string|null
     */
    public function getTechnicianNameAttribute(): ?string
    {
        return $this->technician?->full_name;
    }

    /**
     * Get the phone number of the assigned technician.
     *
     * @return string|null
     */
    public function getTechnicianPhoneAttribute(): ?string
    {
        return $this->technician?->phone;
    }

    /**
     * Get the title of the associated property.
     *
     * @return string|null
     */
    public function getPropertyTitleAttribute(): ?string
    {
        return $this->property?->title;
    }

    /**
     * Get the full name of the tenant.
     *
     * @return string|null
     */
    public function getTenantNameAttribute(): ?string
    {
        return $this->tenant?->full_name;
    }
}