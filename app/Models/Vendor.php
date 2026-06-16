<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'site_utilities',
        'mobile_no',
        'email',
        'address',
        'gst',
        'created_by',
        'updated_by'
    ];

    public function materialRequests()
    {
        return $this->hasMany(MaterialRequest::class);
    }

    public function materialOrders()
    {
        return $this->hasMany(MaterialOrder::class);
    }

    public function materialPayments()
    {
        return $this->hasMany(MaterialPayment::class);
    }

    public function vendorPayDetail()
    {
        return $this->hasOne(VendorPayDetail::class);
    }

    public function vendorPayment()
    {
        return $this->hasMany(VendorPayment::class);
    }
}
