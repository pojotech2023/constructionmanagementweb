<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorPermission extends Model
{
    protected $fillable = ['supervisor_id', 'module', 'action', 'is_allowed'];

    protected $casts = ['is_allowed' => 'boolean'];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
