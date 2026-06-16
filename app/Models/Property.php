<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'type',
        'amount',
        'remarks',
        'image',
        'is_inactive',
        'created_by',
        'updated_by'
    ];
}
