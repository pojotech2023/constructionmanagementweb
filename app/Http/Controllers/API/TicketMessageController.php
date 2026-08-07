<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
class TicketMessageController extends Controller
{
public function storeMessage(Request $request)
{
    $request->validate([
        'ticket_id'   => 'required|integer|exists:tickets,id',
        'site_id'     => 'nullable|integer',
        'sender_type' => 'required|string|in:admin,client,supervisor',
        // make message optional now
        'message'     => 'nullable|string|max:1000',
        'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:2048',
    ]);

    // ❗ At least one of message or attachment required
    if (!$request->filled('message') && !$request->hasFile('attachment')) {
        return response()->json([
            'status'  => false,
            'message' => 'Please provide either a message or an attachment.',
        ], 422);
    }

    $filePath = null;

    // 🔹 Handle file upload
    if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
        $file = $request->file('attachment');
        $filePath = $file->store('ticket_attachments', 'public');
    }

    // 🔹 Find the ticket
    $ticket = Ticket::findOrFail($request->ticket_id);

    // 🔹 Use site_id from request or fallback to ticket’s site_id
    $siteId = $request->input('site_id', $ticket->site_id);

    $senderType = strtolower($request->sender_type);

    // 🔹 Resolve who actually sent the message so the API can return
    //    their real name instead of just the role label.
    //    - admin/supervisor come from the authenticated users table
    //    - client is tied to the ticket's customer
    $senderId = in_array($senderType, ['admin', 'supervisor'])
        ? auth('api')->id()
        : $ticket->customer_id;

    // 🔹 Create the message entry
    TicketMessage::create([
        'ticket_id'   => $ticket->id,
        'site_id'     => $siteId,
        'sender_type' => $senderType,
        'sender_id'   => $senderId,
        'message'     => $request->message,
        'attachment'  => $filePath,
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Message stored successfully',
    ]);
}


    public function getMessages($id)
    {
        $ticket = Ticket::with('messages')->findOrFail($id);
        return response()->json($ticket->messages);
    }


  public function storeSupervisorMessage(Request $request, $id)
{
    $request->validate([
        'message' => 'required|string',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:2048'
    ]);

    $ticket = Ticket::findOrFail($id);

    $filePath = null;

    if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
        $file = $request->file('attachment');
        $filePath = $file->store('ticket_attachments', 'public'); // stored in storage/app/public/ticket_attachments
    }

    TicketMessage::create([
        'ticket_id'   => $ticket->id,
        'sender_type' => 'supervisor',
        'sender_id'   => auth('api')->id(),
        'message'     => $request->message,
        'attachment'  => $filePath,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Supervisor message sent with attachment (if any)',
    ]);
}
}
