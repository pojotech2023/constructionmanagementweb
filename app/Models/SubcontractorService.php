<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcontractorService extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'subcontractor_id',
        'subcontractor_type',
        'date',
        'amount',
        'remarks',
        'status',
        'no_counts',
        'created_by',
        'updated_by'
    ];

     public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }
}
