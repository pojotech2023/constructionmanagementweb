<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBillDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_bill_id',
        'particular',
        'count',
        'unit',
        'amount',
        'created_by',
        'updated_by',
    ];

    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class);
    }
}
