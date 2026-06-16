<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'payment',
        'date',
        'payment_mode',
        'remarks',
        'created_by',
        'updated_by',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
