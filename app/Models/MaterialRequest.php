<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'vendor_id',
        'material_type',
        'quantity',
        'unit',
        'delivery_needed_by',
        'remarks',
        'status',
        'created_by',
        'updated_by',
        'image_url',
        'items',
        'price',
        'date_of_delivery',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
