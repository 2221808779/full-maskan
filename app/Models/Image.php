<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * نموذج الصورة — يمثل صورة عقار مع المسار والنوع (رئيسية/فرعية) ومعرّف العقار المرتبط
 */
class Image extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'image_path',
        'added_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }

    /**
     * العقار — العقار المرتبط بهذه الصورة
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
