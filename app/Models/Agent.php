<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'mobile_no',
        'is_inactive',
        'created_by',
        'updated_by'
    ];
}
