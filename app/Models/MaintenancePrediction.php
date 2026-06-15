<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج التنبؤ بالصيانة — يمثل تنبؤًا باحتمالية طلب صيانة لعقار معين بناءً على البيانات التاريخية
 */
class MaintenancePrediction extends Model
{
    protected $fillable = [
        'property_id',
        'predicted_category',
        'predicted_category_id',
        'days_until_next',
        'predicted_date',
        'is_active',
        'model_used',
        'generated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'predicted_date' => 'date',
            'is_active' => 'boolean',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * Get the property associated with the prediction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope a query to only include active predictions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
