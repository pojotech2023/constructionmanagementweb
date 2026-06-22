<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_id',
        'site_id',
        'payment',
        'date',
        'payment_mode',
        'remarks',
        'created_by',
        'updated_by'
    ];

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }
}
