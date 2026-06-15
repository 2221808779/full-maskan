<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج التخصص — يمثل تخصص فني (مثل سباكة وكهرباء) مع الاسم فقط
 */
class Specialty extends Model
{
    protected $fillable = ['name'];
}