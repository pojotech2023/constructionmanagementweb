<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractorPayDetail extends Model
{
    use HasFactory;

     protected $fillable = [
        'subcontractor_id',
        'opening_balance',
        'total_amount',
        'balance_amount',
        'paid_amount',
        'created_by',
        'updated_by'
    ];

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }
}
