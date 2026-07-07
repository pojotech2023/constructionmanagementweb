<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesBillDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_bill_id',
        'particular',
        'count',
        'amount',
        'created_by',
        'updated_by',
    ];

    public function salesBill()
    {
        return $this->belongsTo(SalesBill::class);
    }
}
