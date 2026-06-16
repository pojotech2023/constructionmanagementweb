<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;
    protected $fillable = [
        'role_name'
    ];

     // One role can have many users
    public function users()
    {
        return $this->hasMany(User::class, 'role_mappings', 'role_id', 'user_id');
    }
    public function roles()
{
    return $this->belongsToMany(Role::class);
}

    
}
