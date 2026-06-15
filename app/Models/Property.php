<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج العقار — يمثل عقًا مع جميع التفاصيل: المالك والموقع والسعر والنوع والوسائط والحالة
 */
class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'location',
        'price',
        'property_type',
        'rooms_count',
        'bathrooms_count',
        'status',
        'unavailable_dates',
        'latitude',
        'longitude',
        'area',
        'price_per_month',
        'deposit',
        'has_pool',
        'has_parking',
        'has_ac',
        'has_furniture',
        'rating',
        'review_count',
    ];

    /**
     * Get the owner (user) of the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the bookings for the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the maintenance requests for the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get the reviews for the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the users who favorited the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the images for the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the active maintenance prediction for the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activePrediction()
    {
        return $this->hasOne(MaintenancePrediction::class)->where('is_active', true)->latest('id');
    }

    /**
     * Serialize a date for the model.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
            'area' => 'float',
            'unavailable_dates' => 'array',
        ];
    }

    /**
     * Convert the model instance to an array with loaded images.
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['images'] = $this->relationLoaded('images')
            ? $this->images->pluck('image_path')->toArray()
            : [];
        return $data;
    }
}
