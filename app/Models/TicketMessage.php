<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
     protected $fillable = [
        'ticket_id',
        'site_id',
        'sender_type',
        'message',
        'attachment',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
