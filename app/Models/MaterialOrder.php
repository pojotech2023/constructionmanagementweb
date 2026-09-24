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
        'order_no',
        'order_group',
        'invoice_no',
        'material_type',
        'category',
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

    // All orders placed together with this one
    public function groupedOrders()
    {
        $group = $this->order_no ?: $this->order_group;
        if (!$group) {
            return collect([$this]);
        }

        return static::where(function ($q) use ($group) {
            $q->where('order_no', $group)
              ->orWhere('order_group', $group);
        })->orderBy('id')->get();
    }

    public function getCategoryDisplayAttribute(): string
    {
        if (!empty($this->category)) {
            return $this->category;
        }

        if (!empty($this->category_name)) {
            return $this->category_name;
        }

        if (!empty($this->unit)) {
            if (str_contains($this->unit, ' - ')) {
                $parts = explode(' - ', $this->unit, 2);
                return trim($parts[0]);
            }
            $genericUnits = ['kg', 'bag', 'bags', 'load', 'loads', 'nos', 'unit', 'units', 'ton', 'tons', 'sqft', 'cft'];
            if (!in_array(strtolower(trim($this->unit)), $genericUnits)) {
                return trim($this->unit);
            }
        }

        return '-';
    }

    public function getMaterialTypeDisplayAttribute(): string
    {
        return ucfirst($this->material_type ?: '-');
    }

    public function getUnitDisplayAttribute(): string
    {
        if (!empty($this->unit)) {
            if (str_contains($this->unit, ' - ')) {
                $parts = explode(' - ', $this->unit, 2);
                return trim($parts[1]);
            }
            return $this->unit;
        }
        return '-';
    }
}
