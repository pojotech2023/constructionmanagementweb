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
        'sender_id',
        'message',
        'attachment',
    ];

    protected $appends = ['sender_name', 'attachment_url'];

    // senderUser/senderCustomer are only loaded to resolve sender_name
    // (senderUser in particular would otherwise leak the user's password hash)
    protected $hidden = ['senderUser', 'senderCustomer'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // sender_id points to users.id when sender_type is admin/supervisor
    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // sender_id points to customers.id when sender_type is client
    public function senderCustomer()
    {
        return $this->belongsTo(Customer::class, 'sender_id');
    }

    // Resolves the real name of whoever sent the message, falling back to
    // the sender_type label if the linked record can't be found.
    public function getSenderNameAttribute()
    {
        if (in_array($this->sender_type, ['admin', 'supervisor'])) {
            return $this->senderUser->name ?? ucfirst($this->sender_type);
        }

        if ($this->sender_type === 'client') {
            return $this->senderCustomer->name ?? 'Client';
        }

        return ucfirst($this->sender_type ?? '');
    }

    // The raw `attachment` column only holds the storage-relative path
    // (e.g. "ticket_attachments/xxx.jpg" or "attachments/xxx.jpg" depending
    // on which endpoint created the message). Clients need an absolute URL,
    // and building it themselves is what produced the 404s — so resolve it
    // here the same way every other module in this app does.
    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }
}
