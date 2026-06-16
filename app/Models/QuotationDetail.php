<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'quotation_id',
        'particular',
        'rate',
        'sqFt',  // ✅ Keep as 'sqFt' - matches your database column
        'unit',
        'total_cost',
        'created_by',
        'updated_by'
    ];
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
    
}

