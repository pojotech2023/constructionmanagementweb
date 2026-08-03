<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

  protected $fillable = [
        'name',
        'date',
        'mobile_no',
        'subject',
        'location',
        'contractor',
        'total_amount',
        'terms_conditions',
        'created_by',
        'updated_by'
    ];

    public function quotationDetail()
    {
        return $this->hasMany(QuotationDetail::class);
    }
    
    public function details()
    {
        return $this->hasMany(QuotationDetail::class, 'quotation_id');
    }

}
