<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Site extends Model
{
    use HasFactory;

  protected $fillable = [
        'site_name',
        'supervisor_id',
        'site_img',
        'location',
        'budget_amount',
        'flat_area',
        'built_up_area',
        'duration',
        'status',
        'is_inactive',
        'created_by',
        'updated_by'
    ];

    protected $appends = [
        'site_image_url',
    ];

    public function getSiteImageUrlAttribute(): string
    {
        $defaultImage = asset('images/default-site.png');

        if (empty($this->site_img)) {
            return $defaultImage;
        }

        $candidates = array_values(array_unique([
            $this->site_img,
            preg_replace('/^site\//i', 'Site/', $this->site_img),
            preg_replace('/^Site\//i', 'site/', $this->site_img),
        ]));

        foreach ($candidates as $path) {
            if (!empty($path) && Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }

        return $defaultImage;
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'site_id');
    }

    public function otherUtilities()
    {
        return $this->hasMany(OtherUtilities::class, 'site_id');
    }

    public function otherUtilitiesSub()
    {
        return $this->hasMany(otherUtilitiesSub::class, 'site_id');
    }

    public function materialRequests()
    {
        return $this->hasMany(MaterialRequest::class);
    }

    public function materialOrders()
    {
        return $this->hasMany(MaterialOrder::class);
    }

    public function materialPayments()
    {
        return $this->hasMany(MaterialPayment::class);
    }

    public function wages()
    {
        return $this->hasMany(Wages::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function subcontractorService()
    {
        return $this->hasMany(SubcontractorService::class);
    }
     public function tasks()
    {
        return $this->hasMany(task::class);
    }
    public function drawings()
    {
        return $this->hasMany(Drawing::class);
    }

    public function payments()
    {
        return $this->hasMany(SitePayment::class);
    }

    public function supervisor()
{
    return $this->belongsTo(User::class, 'supervisor_id');
}
}
