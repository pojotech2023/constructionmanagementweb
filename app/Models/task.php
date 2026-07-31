<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class task extends Model
{
    use HasFactory;
    protected $fillable = [
        'checklist_id',
        'task_name',
        'order',
       ];

public function checklist()
{
    return $this->belongsTo(Checklist::class);
}
public function media()
{
    return $this->hasMany(TaskMedia::class);
}
}
