<?php

namespace App\Http\Controllers\Admin;

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
    public function getForm($siteId)
    {
        $site = Site::findOrFail($siteId);
        $customer = Customer::where('site_id', $siteId)
            ->where('is_inactive', 0)
            ->orderBy('id')
            ->first();
        $units = Unit::orderBy('name')->get();

        return view('admin.menus.sales_bill.sales_bill_add', compact('site', 'customer', 'units'));
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
            'action' => 'nullable|in:whatsapp,download,mail',
            'particular' => 'required|array',
            'particular.*' => 'required|string',
            'count' => 'required|array',
            'count.*' => 'required|numeric',
            'unit' => 'nullable|array',
            'unit.*' => 'nullable|string',
            'amount' => 'required|array',
            'amount.*' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors()], 422);
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
                'created_by' => auth('admin')->id(),
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
                    'created_by' => auth('admin')->id(),
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
            if ($request->input('action') === 'mail' && $request->filled('email')) {
                Mail::to($request->email)->send(new SalesBillMail($salesBill, $pdf->output()));
                $mailSent = true;
            }

            return response()->json([
                'status' => 'success',
                'whatsapp_url' => $whatsappLink,
                'pdf_url' => $pdfUrl,
                'mail_sent' => $mailSent,
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales Bill Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // List every sales bill generated for a site, most recent first.
    public function history($siteId)
    {
        $site = Site::findOrFail($siteId);

        $bills = SalesBill::where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.menus.sales_bill.sales_bill_history', compact('site', 'bills'));
    }

    public function destroy($id)
    {
        $salesBill = SalesBill::findOrFail($id);
        $siteId = $salesBill->site_id;

        $pdfPath = 'sales_bills/sales_bill_' . $salesBill->id . '.pdf';
        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        $salesBill->details()->delete();
        $salesBill->delete();

        return redirect()->route('salesBill.history', $siteId)->with('success', 'Sales bill deleted successfully.');
    }
}
