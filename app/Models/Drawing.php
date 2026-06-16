<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drawing extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'file_path',
        'file_name',
        'file_type',
    ];

     public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
