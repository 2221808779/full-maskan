<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * نموذج تخصصات الفني — جدول وسيط يربط بين الفنيين والتخصصات (Many-to-Many)
 */
class TechnicianSpecialization extends Pivot
{
    protected $table = 'technician_specializations';

    protected $fillable = [
        'profile_id',
        'specialization_id',
    ];
}
