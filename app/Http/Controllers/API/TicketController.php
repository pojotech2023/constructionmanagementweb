<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Session;  
use Illuminate\Support\Facades\Storage;
class TicketController extends Controller
{
 public function store(Request $request)
{
    \Log::info('All request data: ' . json_encode($request->all()));

    $request->validate([
        'site_id' => 'required|integer',
        'customer_id' => 'required|integer',
        'ticket' => 'required|string',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
    ]);

    $filePath = null;

    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filePath = $file->store('uploads', 'public');
        \Log::info('Stored file path: ' . $filePath);
    }

    $ticket = Ticket::create([
        'site_id' => $request->site_id,
        'customer_id' => $request->customer_id,
        'ticket' => $request->ticket,
        'file_path' => $filePath,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Ticket created successfully',
        'data' => $ticket
    ]);
}





public function viewTicketsBySite($siteId)
{
    $tickets = Ticket::with(['site', 'customer'])->where('site_id', $siteId)->get();

    return view('admin.menus.ticket.ticket_view', compact('tickets'));
}

// Show chat page

public function showChat($ticketId)
{
    $ticket = Ticket::with(['messages'])->findOrFail($ticketId);
    return view('admin.menus.ticket.ticket_chat', compact('ticket'));
}

// Store admin reply
public function adminReply(Request $request, $id)
{
    $request->validate([
        'admin_reply' => 'required|string|max:1000',
    ]);

    $ticket = Ticket::findOrFail($id);
    $ticket->admin_reply = $request->admin_reply;
    $ticket->save();

    return redirect()->route('tickets.chat', $id)->with('success', 'Reply submitted!');
}

public function storeAdminMessage(Request $request, $id)
{
    $request->validate([
        'message' => 'nullable|string|max:1000',
        'attachment' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
    ]);

    // ✅ Get role_name from session and convert to lowercase
    $senderType = strtolower(Session::get('role_name'));   // 'admin' or 'supervisor'

    $data = [
        'ticket_id' => $id,
        'sender_type' => $senderType,
        'message' => $request->message,
    ];

    // ✅ Handle attachment
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('attachments', $filename, 'public');
        $data['attachment'] = $path;
    }

    TicketMessage::create($data);

    return redirect()->route('tickets.chat', $id)->with('success', 'Message sent.');
}


// ticket view using id
public function getTicketsBySite($site_id)
{
    try {
        $tickets = Ticket::where('site_id', $site_id)->get(); // removed 'with(customer)'

        return response()->json([
            'status' => true,
            'message' => 'Tickets fetched successfully.',
            'data' => $tickets
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Server Error',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getMessages($id)
{
    try {
        // Get the ticket with messages
        $ticket = Ticket::with(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }])->find($id);

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket not found.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Messages fetched successfully.',
            'ticket_name' => $ticket->ticket, // assuming your column name is 'ticket'
            'data' => $ticket->messages
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Server Error',
            'error' => $e->getMessage(),
        ], 500);
    }
}






}
