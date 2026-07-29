<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\PurchaseBillMail;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillDetail;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseBillController extends Controller
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

        return response()->json([
            'status' => true,
            'message' => 'Purchase bill form details fetched successfully.',
            'data' => [
                'site' => $site,
            ],
        ]);
    }

    public function index($siteId)
    {
        $purchaseBills = PurchaseBill::with('details')
            ->where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Purchase bills fetched successfully.',
            'data' => $purchaseBills,
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

            $purchaseBill = PurchaseBill::create([
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

                PurchaseBillDetail::create([
                    'purchase_bill_id' => $purchaseBill->id,
                    'particular' => $particular,
                    'count' => $count,
                    'amount' => $amount,
                    'created_by' => auth('api')->id(),
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
            if ($request->filled('email')) {
                Mail::to($request->email)->send(new PurchaseBillMail($purchaseBill, $pdf->output()));
                $mailSent = true;
            }

            return response()->json([
                'status' => true,
                'message' => 'Purchase bill created successfully.',
                'data' => [
                    'purchase_bill_id' => $purchaseBill->id,
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
        $purchaseBill = PurchaseBill::with('details', 'site')->find($id);

        if (!$purchaseBill) {
            return response()->json(['status' => false, 'message' => 'Purchase bill not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Purchase bill fetched successfully.',
            'data' => $purchaseBill,
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchaseBill = PurchaseBill::find($id);

        if (!$purchaseBill) {
            return response()->json(['status' => false, 'message' => 'Purchase bill not found.'], 404);
        }

        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'subject' => 'required',
            'date' => 'required|date',
            'mobile_no' => 'nullable|numeric|digits:10',
            'location' => 'required',
            'email' => 'nullable|email',
            'terms_conditions' => 'nullable|string',
            'particular' => 'nullable|array',
            'count' => 'nullable|array',
            'amount' => 'nullable|array',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $purchaseBill->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'date' => $request->date,
            'mobile_no' => $request->mobile_no,
            'location' => $request->location,
            'email' => $request->email,
            'terms_conditions' => $request->terms_conditions,
            'updated_by' => auth('api')->id(),
        ]);

        if ($request->filled('particular')) {
            $purchaseBill->details()->delete();
            $totalAmount = 0;

            foreach ($request->particular as $index => $particular) {
                $count = $request->count[$index];
                $amount = $request->amount[$index];
                $totalAmount += (float) $amount;

                PurchaseBillDetail::create([
                    'purchase_bill_id' => $purchaseBill->id,
                    'particular' => $particular,
                    'count' => $count,
                    'amount' => $amount,
                    'created_by' => auth('api')->id(),
                ]);
            }

            $purchaseBill->update(['total_amount' => $totalAmount]);
        }

        $purchaseBill->load('details');

        return response()->json([
            'status' => true,
            'message' => 'Purchase bill updated successfully.',
            'data' => $purchaseBill,
        ]);
    }

    public function destroy($id)
    {
        $purchaseBill = PurchaseBill::find($id);

        if (!$purchaseBill) {
            return response()->json(['status' => false, 'message' => 'Purchase bill not found.'], 404);
        }

        $purchaseBill->details()->delete();
        $purchaseBill->delete();

        return response()->json([
            'status' => true,
            'message' => 'Purchase bill deleted successfully.',
        ]);
    }
}
