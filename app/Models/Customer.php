<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

     protected $fillable = [
        'site_id',
        'name',
        'mobile_no',
        'email',
        'dob',
        'address',
        'is_inactive',
        'created_by',
        'updated_by'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}

