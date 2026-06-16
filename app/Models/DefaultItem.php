<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultItem extends Model
{
    use HasFactory;
     protected $fillable = [
        'particular', 'rate', 'sqFt', 'unit'
    ];
}
