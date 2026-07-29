<?php

namespace App\Http\Controllers\API;

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
                $amount = $request->amount[$index];
                $totalAmount += (float) $amount;

                SalesBillDetail::create([
                    'sales_bill_id' => $salesBill->id,
                    'particular' => $particular,
                    'count' => $count,
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

        return response()->json([
            'status' => true,
            'message' => 'Sales bill fetched successfully.',
            'data' => $salesBill,
        ]);
    }
}
