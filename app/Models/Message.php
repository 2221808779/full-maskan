<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الرسالة — يمثل رسالة بين مستخدمين (مرسل ومستقبل) ضمن محادثة خاصة
 */
class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message_text',
        'sent_at',
        'type',
        'complaint_status',
        'admin_response',
        'responded_by',
        'resolved_at',
        'edited_at',
        'deleted_for',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'resolved_at' => 'datetime',
            'edited_at' => 'datetime',
            'read_at' => 'datetime',
            'deleted_for' => 'array',
        ];
    }

    /**
     * المرسل — المستخدم الذي أرسل الرسالة
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * المستقبل — المستخدم الذي استلم الرسالة
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * المستجيب — المسؤول الذي رد على هذه الرسالة (للشكاوى)
     */
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

}
