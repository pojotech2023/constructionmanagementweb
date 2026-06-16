<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'category',
        'count',
        'date',
        'created_by',
        'updated_by'
    ];
}
