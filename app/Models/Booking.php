<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الحجز — يمثل حجز مستأجر لعقار مع تواريخ البداية والنهاية والمبلغ الإجمالي وحالة الدفع
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'guests',
        'notes',
        'completed_at',
        'archived_at',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_price' => 'float',
            'archived_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * المستخدم — المستأجر الذي قام بالحجز
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العقار — العقار المرتبط بهذا الحجز
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * الدفع — عملية الدفع المرتبطة بهذا الحجز
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
