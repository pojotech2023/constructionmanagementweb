<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMedia extends Model
{
    use HasFactory;

   protected $fillable = [
    'site_id',
    'task_id',
    'image_path',
    'video_path',
    'remarks',
    'admin_remark',
    'status',
    'updated_by'
];


public function task()
{
    return $this->belongsTo(task::class);
}
}
