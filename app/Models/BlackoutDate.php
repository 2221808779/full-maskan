<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج تاريخ الحظر — يمثل التواريخ التي لا يمكن حجز عقار فيها (مثل أيام الصيانة أو الإشغال)
 */
class BlackoutDate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'date',
        'status',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * العقار — العقار المرتبط بتاريخ الحظر
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
