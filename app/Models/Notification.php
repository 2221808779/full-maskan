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
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * بوت النموذج — تسجيل مستمعي الأحداث (بث الإشعار عند الإنشاء)
     */
    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            try { event(new NotificationCreated($notification)); } catch (\Exception $e) {}
        });
    }

    /**
     * المستخدم — المستخدم المستهدف بهذا الإشعار
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * عنوان مترجم — ترجمة عنوان الإشعار تلقائياً حسب لغة المستخدم
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
     * محتوى مترجم — ترجمة محتوى الإشعار تلقائياً حسب لغة المستخدم
     */
    public function getContentAttribute(?string $value): ?string
    {
        if ($value === null) return null;
        return __($value);
    }

    /**
     * تخزين العنوان — يُخزن النص الإنجليزي الأصلي ليُترجم عند القراءة حسب لغة كل مستخدم
     */
    public function setTitleAttribute(?string $value): void
    {
        // Store the English key (reverse-translate if Arabic was saved)
        $this->attributes['title'] = $value;
    }

    /**
     * تخزين المحتوى — يُخزن نص المحتوى الأصلي ليُترجم عند القراءة
     */
    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = $value;
    }
}
