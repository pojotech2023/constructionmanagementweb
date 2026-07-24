<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcontractorType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'created_by',
        'updated_by',
    ];
}
