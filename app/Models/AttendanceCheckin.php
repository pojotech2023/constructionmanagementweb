<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'date',
        'check_in_time',
        'check_in_photo',
        'check_out_time',
        'check_out_photo',
        'created_by',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
