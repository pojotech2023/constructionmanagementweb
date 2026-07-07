<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'vendor_id',
        'material_type',
        'date',
        'quantity',
        'unit',
        'price',
        'gst',
        'available_unit_count',
        'status',
        'created_by',
        'updated_by',
        'image_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'gst' => 'decimal:2',
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
