<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationMail;

class QuotationController extends Controller
{
    public function getForm()
    {
        return view('admin.menus.quotation.quotation_add');
    }

    public function history(Request $request)
    {
        $mobileNo = $request->query('mobile_no');
        $quotations = collect();
        $searched = false;

        if ($mobileNo) {
            $validate = Validator::make(['mobile_no' => $mobileNo], [
                'mobile_no' => 'required|digits:10',
            ]);

            if ($validate->fails()) {
                return redirect()->route('quotation.history')
                    ->withErrors($validate)
                    ->withInput();
            }

            $searched = true;
            $quotations = Quotation::where('mobile_no', $mobileNo)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('admin.menus.quotation.quotation_history', compact('quotations', 'mobileNo', 'searched'));
    }


    public function store(Request $request)
{
    $validate = Validator::make($request->all(), [
        'name' => 'required',
        'subject' => 'required',
        'date' => 'required|date',
        'mobile_no' => 'nullable|numeric|digits:10',
        'location' => 'required',
        'contractor' => 'required',
        'particular' => 'required|array',
        'rate' => 'required|array',
        'sqFt' => 'required|array',
        'unit' => 'required|array',
        'email' => 'nullable|email',
    ]);

    if ($validate->fails()) {
        return response()->json(['errors' => $validate->errors()], 422);
    }

    try {
        // Create quotation
        $quotation = Quotation::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'date' => $request->date,
            'mobile_no' => $request->mobile_no,
            'location' => $request->location,
            'contractor' => $request->contractor,
            'created_by'  => auth('admin')->id(),
        ]);

        $totalAmount = 0;

        // Create quotation details
        foreach ($request->particular as $index => $part) {
            $rate = $request->rate[$index];
            $sqFt = $request->sqFt[$index];
            $unit = $request->unit[$index];
            $total = $rate * $sqFt;
            $totalAmount += $total;

            QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'particular' => $part,
                'rate' => $rate,
                'sqFt' => $sqFt,  // ✅ Use 'sqFt' - matches database column
                'unit' => $unit,
                'total_cost' => $total,
                'created_by'  => auth('admin')->id(),
            ]);
        }

        // Update total amount
        $quotation->update(['total_amount' => $totalAmount]);

        // ✅ IMPORTANT: Load the relationship before generating PDF
        $quotation->load('details');

        // Generate PDF
        $pdf = Pdf::loadView('admin.helper.pdf_quotation', ['data' => $quotation]);

        // Save PDF
        $pdfPath = 'quotations/quotation_' . $quotation->id . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // WhatsApp message
        $pdfUrl = asset('storage/' . $pdfPath);
        $message = urlencode("Hi {$quotation->name}, your quotation is ready. Download here: $pdfUrl");
        $whatsappLink = "https://wa.me/91{$quotation->mobile_no}?text=$message";

        $mailSent = false;
        if ($request->input('action') === 'mail' && $request->filled('email')) {
            Mail::to($request->email)->send(new QuotationMail($quotation, $pdf->output()));
            $mailSent = true;
        }

        return response()->json([
            'status' => 'success',
            'whatsapp_url' => $whatsappLink,
            'pdf_url' => $pdfUrl,
            'mail_sent' => $mailSent,
        ]);

    } catch (\Exception $e) {
        \Log::error('Quotation Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

}
