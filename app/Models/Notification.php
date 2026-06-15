<?php

namespace App\Models;

use App\Events\NotificationCreated;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الإشعار — يمثل إشعارًا مرسلاً لمستخدم مع النوع والرسالة وحالة القراءة
 */
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'type',
        'content',
        'action_url',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and register event listeners.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            try { event(new NotificationCreated($notification)); } catch (\Exception $e) {}
        });
    }

    /**
     * Get the user who owns the notification.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the translated title attribute.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getTitleAttribute(?string $value): ?string
    {
        if ($value === null) return null;
        $translated = __($value);
        // If translation didn't change the value (key not found), return original
        // If it did change (key was found), return translated
        return $translated !== $value ? $translated : $value;
    }

    /**
     * Get the translated content attribute.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getContentAttribute(?string $value): ?string
    {
        if ($value === null) return null;
        return __($value);
    }

    /**
     * Store the original English text so __() can translate it on read.
     * We keep the English text in DB to allow dynamic per-user-locale translation.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setTitleAttribute(?string $value): void
    {
        // Store the English key (reverse-translate if Arabic was saved)
        $this->attributes['title'] = $value;
    }

    /**
     * Set the content attribute.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = $value;
    }
}
