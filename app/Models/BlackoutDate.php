<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج تاريخ الحظر — يمثل التواريخ التي لا يمكن حجز عقار فيها (مثل أيام الصيانة أو الإشغال)
 */
class BlackoutDate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'date',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the property associated with the blackout date.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
