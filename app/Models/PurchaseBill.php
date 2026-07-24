<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'mobile_no',
        'email',
        'subject',
        'date',
        'location',
        'total_amount',
        'terms_conditions',
        'created_by',
        'updated_by',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseBillDetail::class, 'purchase_bill_id');
    }
}
