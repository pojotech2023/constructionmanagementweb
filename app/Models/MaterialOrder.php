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
        'order_group',
        'material_type',
        'category_name',
        'spec',
        'date',
        'quantity',
        'unit',
        'price',
        'gst',
        'total_amount',
        'available_unit_count',
        'status',
        'created_by',
        'updated_by',
        'image_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'gst' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // All orders placed together with this one (just itself when it was ordered alone)
    public function groupedOrders()
    {
        if (!$this->order_group) {
            return collect([$this]);
        }

        return static::where('order_group', $this->order_group)->orderBy('id')->get();
    }
}
