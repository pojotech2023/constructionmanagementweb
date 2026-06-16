<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TicketMessage;



class Ticket extends Model
{
    use HasFactory;
    protected $fillable = ['site_id', 'customer_id', 'ticket','file_path'];
public function site()
{
    return $this->belongsTo(\App\Models\Site::class, 'site_id');
}

public function customer()
{
    return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
}

public function messages()
{
    return $this->hasMany(TicketMessage::class);
}
public function attachments()
{
    return $this->hasMany(\App\Models\TicketAttachment::class, 'ticket_id');
}


}
