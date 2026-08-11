<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SalesBillMail;
use App\Models\Customer;
use App\Models\SalesBill;
use App\Models\SalesBillDetail;
use App\Models\Site;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesBillController extends Controller
{
    public function getDetails($siteId)
    {
        $site = Site::find($siteId);

        if (!$site) {
            return response()->json([
                'status' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $customer = Customer::where('site_id', $siteId)
            ->where('is_inactive', 0)
            ->orderBy('id')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Sales bill form details fetched successfully.',
            'data' => [
                'site' => $site,
                'customer' => $customer,
                'units' => Unit::orderBy('name')->pluck('name'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'name' => 'required',
            'subject' => 'required',
            'date' => 'required|date',
            'mobile_no' => 'nullable|numeric|digits:10',
            'location' => 'required',
            'email' => 'nullable|email',
            'terms_conditions' => 'nullable|string',
            'particular' => 'required|array',
            'count' => 'required|array',
            'unit' => 'nullable|array',
            'unit.*' => 'nullable|string',
            'amount' => 'required|array',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        try {
            $site = Site::findOrFail($request->site_id);

            $salesBill = SalesBill::create([
                'site_id' => $site->id,
                'name' => $request->name,
                'subject' => $request->subject,
                'date' => $request->date,
                'mobile_no' => $request->mobile_no,
                'location' => $request->location,
                'email' => $request->email,
                'terms_conditions' => $request->terms_conditions,
                'created_by' => auth('api')->id(),
            ]);

            $totalAmount = 0;

            foreach ($request->particular as $index => $particular) {
                $count = $request->count[$index];
                $unit = $request->unit[$index] ?? null;
                $amount = $request->amount[$index];
                // Amount is the rate per unit — the line total (and grand total) is count × rate.
                $totalAmount += (float) $amount * ((float) $count > 0 ? (float) $count : 1);

                SalesBillDetail::create([
                    'sales_bill_id' => $salesBill->id,
                    'particular' => $particular,
                    'count' => $count,
                    'unit' => $unit,
                    'amount' => $amount,
                    'created_by' => auth('api')->id(),
                ]);
            }

            $salesBill->update(['total_amount' => $totalAmount]);
            $salesBill->load('details');

            $pdf = Pdf::loadView('admin.helper.pdf_sales_bill', ['data' => $salesBill, 'site' => $site]);

            $pdfPath = 'sales_bills/sales_bill_' . $salesBill->id . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $pdfUrl = asset('storage/' . $pdfPath);
            $message = urlencode("Hi {$salesBill->name}, your sales bill is ready. Download here: $pdfUrl");
            $whatsappLink = "https://wa.me/91{$salesBill->mobile_no}?text=$message";

            $mailSent = false;
            if ($request->filled('email')) {
                Mail::to($request->email)->send(new SalesBillMail($salesBill, $pdf->output()));
                $mailSent = true;
            }

            return response()->json([
                'status' => true,
                'message' => 'Sales bill created successfully.',
                'data' => [
                    'sales_bill_id' => $salesBill->id,
                    'pdf_url' => $pdfUrl,
                    'whatsapp_url' => $whatsappLink,
                    'total_amount' => $totalAmount,
                    'mail_sent' => $mailSent,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $salesBill = SalesBill::with('details', 'site')->find($id);

        if (!$salesBill) {
            return response()->json(['status' => false, 'message' => 'Sales bill not found.'], 404);
        }

        $this->attachLinks($salesBill);

        return response()->json([
            'status' => true,
            'message' => 'Sales bill fetched successfully.',
            'data' => $salesBill,
        ]);
    }

    // History: every sales bill generated for a site, most recent first.
    // Each bill carries pdf_url (View) and whatsapp_url (WhatsApp) so the app
    // doesn't need to know/rebuild the storage path itself; Delete uses the
    // existing DELETE /sales-bill-delete/{id} endpoint below.
    public function index($siteId)
    {
        $salesBills = SalesBill::with('details')
            ->where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->each(fn ($bill) => $this->attachLinks($bill));

        return response()->json([
            'status' => true,
            'message' => 'Sales bills fetched successfully.',
            'data' => $salesBills,
        ]);
    }

    // Attach the PDF and WhatsApp links to a bill for View/WhatsApp actions in the app.
    private function attachLinks(SalesBill $bill): void
    {
        $pdfPath = 'sales_bills/sales_bill_' . $bill->id . '.pdf';
        $pdfUrl = asset('storage/' . $pdfPath);
        $message = urlencode("Hi {$bill->name}, your sales bill is ready. Download here: $pdfUrl");

        $bill->pdf_url = $pdfUrl;
        $bill->whatsapp_url = "https://wa.me/91{$bill->mobile_no}?text=$message";
    }

    public function destroy($id)
    {
        $salesBill = SalesBill::find($id);

        if (!$salesBill) {
            return response()->json(['status' => false, 'message' => 'Sales bill not found.'], 404);
        }

        $pdfPath = 'sales_bills/sales_bill_' . $salesBill->id . '.pdf';
        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        $salesBill->details()->delete();
        $salesBill->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sales bill deleted successfully.',
        ]);
    }
}
