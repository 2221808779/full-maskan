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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
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
     * Get the user who sent the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the admin user who responded to the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

}
