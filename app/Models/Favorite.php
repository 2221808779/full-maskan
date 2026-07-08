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
     * المستخدم — المستخدم الذي أضاف العقار للمفضلة
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العقار — العقار المضاف للمفضلة
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
