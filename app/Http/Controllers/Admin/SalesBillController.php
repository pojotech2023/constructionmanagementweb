<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SalesBillMail;
use App\Models\Customer;
use App\Models\SalesBill;
use App\Models\SalesBillDetail;
use App\Models\Site;
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

        return view('admin.menus.sales_bill.sales_bill_add', compact('site', 'customer'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'name' => 'required',
            'subject' => 'required',
            'date' => 'required|date',
            'mobile_no' => 'required',
            'location' => 'required',
            'email' => 'nullable|email',
            'terms_conditions' => 'nullable|string',
            'action' => 'nullable|in:whatsapp,download,mail',
            'particular' => 'required|array',
            'count' => 'required|array',
            'amount' => 'required|array',
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
                $amount = $request->amount[$index];
                $totalAmount += (float) $amount;

                SalesBillDetail::create([
                    'sales_bill_id' => $salesBill->id,
                    'particular' => $particular,
                    'count' => $count,
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
}
