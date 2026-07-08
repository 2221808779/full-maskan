<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج التقييم — يمثل تقييمًا لمنشأة (عقار/مالك/فني) مع عدد النجوم والتعليق
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'technician_id',
        'owner_id',
        'booking_id',
        'stars',
        'comment',
    ];

    /**
     * المستخدم — المستخدم الذي كتب التقييم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العقار — العقار الذي تم تقييمه
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * الفني — الفني الذي تم تقييمه
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * المالك — مالك العقار الذي تم تقييمه
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
