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
        'admin_remark',
        'reviewed_at',
        'source',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_SUPERVISOR = 'supervisor';

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? 'Pending';
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
