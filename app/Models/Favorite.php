<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج المفضلة — يمثل إضافة مستأجر لعقار إلى قائمته المفضلة
 */
class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
    ];

    /**
     * Get the user who favorited the property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
