<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الملف الشخصي للفني — يمثل بيانات إضافية للفني مع الوصف والصورة الرمزية
 */
class TechnicianProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
    ];

    protected $appends = [
        'avg_rating',
        'reviews_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
        ];
    }

    /**
     * Get the user associated with the technician profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specializations for the technician.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialty::class, 'technician_specializations', 'profile_id', 'specialization_id');
    }

    /**
     * Get the average rating for the technician.
     *
     * @return float
     */
    public function getAvgRatingAttribute(): float
    {
        return (float) Review::where('technician_id', $this->user_id)
            ->avg('stars') ?? 0.0;
    }

    /**
     * Get the total number of reviews for the technician.
     *
     * @return int
     */
    public function getReviewsCountAttribute(): int
    {
        return Review::where('technician_id', $this->user_id)->count();
    }
}