<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'payment',
        'date',
        'payment_mode',
        'created_by',
        'updated_by'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
