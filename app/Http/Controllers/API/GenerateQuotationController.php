<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\DefaultItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationMail;

class GenerateQuotationController extends Controller
{
    /**
     * List all quotations.
     */
    public function index()
    {
        $quotations = Quotation::orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Quotations fetched successfully.',
            'data' => $quotations,
        ]);
    }

    /**
     * Quotation history search by mobile number.
     */
    public function history(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'mobile_no' => 'required|digits:10',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $quotations = Quotation::with('details')
            ->where('mobile_no', $request->mobile_no)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Quotation history fetched successfully.',
            'mobile_no' => $request->mobile_no,
            'data' => $quotations,
        ]);
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
        'email' => 'nullable|email',

        'data' => 'required|array|min:1',
        'data.*.particular' => 'required|string',
        'data.*.rate' => 'required|numeric',
        'data.*.sqFt' => 'required|numeric',
        'data.*.unit' => 'required|string',
    ]);

    if ($validate->fails()) {
        return response()->json(['errors' => $validate->errors()], 422);
    }

    try {
        $quotation = Quotation::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'date' => $request->date,
            'mobile_no' => $request->mobile_no,
            'location' => $request->location,
            'contractor' => $request->contractor,
            'created_by' => auth('admin')->id(),
        ]);

        $totalAmount = 0;

        foreach ($request->data as $item) {
            $total = $item['rate'] * $item['sqFt'];
            $totalAmount += $total;

            QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'particular' => $item['particular'],
                'rate' => $item['rate'],
                'sqFt' => $item['sqFt'],
                'unit' => $item['unit'],
                'total_cost' => $total,
                'created_by' => auth('admin')->id(),
            ]);
        }

        $quotation->update(['total_amount' => $totalAmount]);
        $quotation->load('details');

        $pdf = Pdf::loadView('admin.helper.pdf_quotation', ['data' => $quotation]);
        $pdfPath = 'quotations/quotation_' . $quotation->id . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $pdfUrl = asset('storage/' . $pdfPath);
        $message = urlencode("Hi {$quotation->name}, your quotation is ready. Download here: $pdfUrl");
        $whatsappLink = "https://wa.me/91{$quotation->mobile_no}?text=$message";

        $mailSent = false;
        if ($request->filled('email')) {
            Mail::to($request->email)->send(new QuotationMail($quotation, $pdf->output()));
            $mailSent = true;
        }

       return response()->json([
    'status' => true,
    'message' => 'Quotation created successfully with default + user items',
    'quotation_id' => $quotation->id,
    'pdf_url' => $pdfUrl,
    'whatsapp_url' => $whatsappLink,
    'total_amount' => $totalAmount,
    'mail_sent' => $mailSent
], 200);


    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * ✅ 2️⃣ View quotation details (like web view)
     */
    public function show($id)
    {
        $quotation = Quotation::with('details')->find($id);

        if (!$quotation) {
            return response()->json(['status' => false, 'message' => 'Quotation not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Quotation details fetched successfully',
            'data' => $quotation,
        ]);
    }

    /**
     * ✅ 3️⃣ Remove one default item (before PDF)
     */
public function removeDetail($id)
{
    $detail = QuotationDetail::find($id);

    if (!$detail) {
        return response()->json([
            'status' => false,
            'message' => 'Item not found'
        ], 404);
    }

    // Allow removing only default items (is_default = 1)
   
        $detail->delete();
        return response()->json([
            'status' => true,
            'message' => 'Default item removed successfully'
        ]);
  

    return response()->json([
        'status' => false,
        'message' => 'Only default items can be removed'
    ], 403);
}

    /**
     * ✅ 4️⃣ Generate final PDF (after removing unwanted defaults)
     */
    public function generatePdf(Request $request, $id)
    {
       $quotation = Quotation::with('details')->find($id);

if (!$quotation) {
    return response()->json(['status' => false, 'message' => 'Quotation not found'], 404);
}

// If details is null, make it empty collection
if ($quotation->details === null) {
    $quotation->setRelation('details', collect());
}

$totalAmount = $quotation->details->sum('total_cost');
$quotation->update(['total_amount' => $totalAmount]);

$pdf = PDF::loadView('admin.helper.pdf_quotation', ['data' => $quotation]);
$pdfPath = 'quotations/quotation_' . $quotation->id . '.pdf';
Storage::disk('public')->put($pdfPath, $pdf->output());

$pdfUrl = asset('storage/' . $pdfPath);
$message = urlencode("Hi {$quotation->name}, your quotation is ready. Download here: $pdfUrl");
$whatsappLink = "https://wa.me/91{$quotation->mobile_no}?text=$message";

$mailSent = false;
$email = $request->input('email');
if (!empty($email)) {
    Mail::to($email)->send(new QuotationMail($quotation, $pdf->output()));
    $mailSent = true;
}

return response()->json([
    'status' => true,
    'message' => 'PDF generated successfully',
    'pdf_url' => $pdfUrl,
    'whatsapp_url' => $whatsappLink,
    'total_amount' => $totalAmount,
    'mail_sent' => $mailSent,
]);
    }



    public function defaultItemList()
{
    $items = DefaultItem::all();

    return response()->json([
        'status' => true,
        'data' => $items
    ]);
}
}
