<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcontractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subcontractors',
        'mobile_no',
        'email',
        'address',
        'gst',
        'created_by',
        'updated_by'
    ];

     public function subcontractorService()
    {
        return $this->hasMany(SubcontractorService::class);
    }

    public function subcontractorPayDetail()
    {
        return $this->hasOne(SubcontractorPayDetail::class);
    }

    public function subcontractorPayment()
    {
        return $this->hasMany(SubcontractorPayment::class);
    }
}
