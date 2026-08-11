<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PurchaseBillMail;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillDetail;
use App\Models\Site;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseBillController extends Controller
{
    public function getForm($siteId)
    {
        $site = Site::findOrFail($siteId);
        $units = Unit::orderBy('name')->get();

        return view('admin.menus.purchase_bill.purchase_bill_add', compact('site', 'units'));
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

            $purchaseBill = PurchaseBill::create([
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

                PurchaseBillDetail::create([
                    'purchase_bill_id' => $purchaseBill->id,
                    'particular' => $particular,
                    'count' => $count,
                    'unit' => $unit,
                    'amount' => $amount,
                    'created_by' => auth('admin')->id(),
                ]);
            }

            $purchaseBill->update(['total_amount' => $totalAmount]);
            $purchaseBill->load('details');

            $pdf = Pdf::loadView('admin.helper.pdf_purchase_bill', ['data' => $purchaseBill, 'site' => $site]);

            $pdfPath = 'purchase_bills/purchase_bill_' . $purchaseBill->id . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $pdfUrl = asset('storage/' . $pdfPath);
            $message = urlencode("Hi {$purchaseBill->name}, your purchase bill is ready. Download here: $pdfUrl");
            $whatsappLink = "https://wa.me/91{$purchaseBill->mobile_no}?text=$message";

            $mailSent = false;
            if ($request->input('action') === 'mail' && $request->filled('email')) {
                Mail::to($request->email)->send(new PurchaseBillMail($purchaseBill, $pdf->output()));
                $mailSent = true;
            }

            return response()->json([
                'status' => 'success',
                'whatsapp_url' => $whatsappLink,
                'pdf_url' => $pdfUrl,
                'mail_sent' => $mailSent,
            ]);
        } catch (\Exception $e) {
            \Log::error('Purchase Bill Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // List every purchase bill generated for a site, most recent first.
    public function history($siteId)
    {
        $site = Site::findOrFail($siteId);

        $bills = PurchaseBill::where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.menus.purchase_bill.purchase_bill_history', compact('site', 'bills'));
    }

    public function destroy($id)
    {
        $purchaseBill = PurchaseBill::findOrFail($id);
        $siteId = $purchaseBill->site_id;

        $pdfPath = 'purchase_bills/purchase_bill_' . $purchaseBill->id . '.pdf';
        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        $purchaseBill->details()->delete();
        $purchaseBill->delete();

        return redirect()->route('purchaseBill.history', $siteId)->with('success', 'Purchase bill deleted successfully.');
    }
}
