<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'opening_balance',
        'total_units',
        'total_unit_price',
        'balance_amount',
        'paid_amount',
        'created_by',
        'updated_by'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
