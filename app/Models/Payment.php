<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الدفع — يمثل معاملة دفع لحجز مع طريقة الدفع (Plutu/نقدي) والحالة والمبلغ
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'amount',
        'payment_type',
        'status',
        'paid_at',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * الحجز — الحجز المرتبط بعملية الدفع
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * المستخدم — المستخدم الذي قام بعملية الدفع
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
