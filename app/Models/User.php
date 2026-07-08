<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * نموذج المستخدم — يمثل جميع أنواع المستخدمين (مستأجر وفني ومالك ومسؤول) مع أدوار ومصادقة
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'password',
        'phone',
        'profile_image',
        'birth_date',
        'gender',
        'user_type',
        'status',
        'ban_reason',
        'banned_at',
        'banned_until',
        'phone_verified_at',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * تحويل الأنواع — تحويل الحقول إلى أنواعها المناسبة عند القراءة
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'phone_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'banned_until' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * العقارات — جميع العقارات التي يملكها المستخدم
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    /**
     * الحجوزات — جميع حجوزات المستخدم
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * طلبات الصيانة — طلبات الصيانة المسندة لهذا الفني
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'technician_id');
    }

    /**
     * التقييمات — جميع التقييمات التي كتبها المستخدم
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * المفضلة — العقارات التي أضافها المستخدم إلى مفضلته
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * الإشعارات — جميع الإشعارات المرسلة للمستخدم
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * الملف الفني — الملف الشخصي للفني المرتبط بهذا المستخدم
     */
    public function technicianProfile()
    {
        return $this->hasOne(TechnicianProfile::class);
    }
}
