<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialEstimationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'particular',
        'quantity',
        'unit',
        'date',
        'created_by',
        'updated_by',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
