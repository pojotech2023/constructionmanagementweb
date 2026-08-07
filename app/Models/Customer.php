<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory, HasApiTokens;

     protected $fillable = [
        'site_id',
        'name',
        'mobile_no',
        'email',
        'dob',
        'marriage_date',
        'address',
        'is_inactive',
        'created_by',
        'updated_by'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class, 'customer_id');
    }
}

