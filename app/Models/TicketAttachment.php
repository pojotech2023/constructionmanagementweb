<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'file_path',
    ];

    // Relationship with Ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // (Optional) Access full URL
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}


