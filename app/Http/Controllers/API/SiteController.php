<?php

namespace App\Http\Controllers\API;

use App\Exports\SiteReportExport;
use App\Http\Controllers\Controller;
use App\Services\SiteExpenseCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Site;
use App\Models\Customer;
use App\Models\SitePayment;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SitePaymentMail;
use Carbon\Carbon;

class SiteController extends Controller
{
    //site management list
    public function index(Request $request)
    {
        $query = Site::with('customer')
            ->where('is_inactive', 0);

            if($request->has('status') && $request->status !== 'All'){
                $query->where('status', $request->status);
            }

            $sites = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'response code' => 200,
            'data' => $sites,
            'status' => true,
            'message' => 'Sites Feteched Successfully!.'
        ]);
    }

    //site details
    public function siteDetail($id)
    {
        $site = Site::with('materialOrders')->where('id', $id)->first();

        // Group material orders by material_type and format each item
        $materials = $site->materialOrders->groupBy('material_type')->map(function ($group, $type) {
            return [
                'material_type' => $type,
                $type . ' units' => $group->sum('quantity'),
                $type . ' total units values' => $group->sum('price'),
            ];
        })->values();

        return response()->json([
            'response code' => 200,
            'data' => $materials,
            'status' => true,
            'message' => 'Site fetched successfully!',
        ]);
    }



     public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'site_name'      => 'required|string',
        'site_img'       => 'nullable|image|mimes:jpg,jpeg,png,webp',
        'location'       => 'nullable|string',
        'flat_area'      => 'nullable|string',
        'built_up_area'  => 'nullable|string',
        'duration'       => 'nullable|string',
        'supervisor_id'  => 'nullable|exists:users,id',

        'name'           => 'nullable|string',
        'mobile_no'      => 'nullable|numeric|digits:10',
        'email'          => 'nullable|email',
        'dob'            => 'nullable|date',
        'address'        => 'nullable|string',

    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

   $site_img = null;
if ($request->hasFile('site_img')) {
    $file = $request->file('site_img');
    $site_img = $file->store('site', 'public'); // store in storage/app/public/site
}

    $site = Site::create([
        'site_name'     => $request->site_name,
        'supervisor_id' => $request->supervisor_id,
        'site_img'      => $site_img,
        'location'      => $request->location,
        'flat_area'     => $request->flat_area,
        'built_up_area' => $request->built_up_area,
        'duration'      => $request->duration,
        'status'        => 'Ongoing'
    ]);

    $customer = Customer::create([
        'site_id'    => $site->id,
        'name'       => $request->name,
        'mobile_no'  => $request->mobile_no,
        'email'      => $request->email,
        'dob'        => $request->dob,
        'address'    => $request->address,
    ]);

    return response()->json([
        'status'   => true,
        'message'  => 'Site & Customer Created Successfully',
        'site'     => $site,
        'customer' => $customer
    ], 201);
}

public function edit($id)
{
    $site = Site::with('customer')->find($id);

    if (!$site) {
        return response()->json([
            'status' => false,
            'message' => 'Site not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'site' => $site
    ]);
}
public function update(Request $request)
{
    // Get site ID from form-data
    $siteId = $request->input('site_id');

    if (!$siteId) {
        return response()->json([
            'status' => false,
            'message' => 'Site ID is required',
        ], 400);
    }

    $site = Site::find($siteId);

    if (!$site) {
        return response()->json([
            'status' => false,
            'message' => 'Site not found',
        ], 404);
    }

    // Validate inputs
    $validator = Validator::make($request->all(), [
        'site_name'      => 'required|string',
        'site_img'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'location'       => 'required|string',
        'flat_area'      => 'required|string',
        'built_up_area'  => 'required|string',
        'duration'       => 'required|string',
        'supervisor_id'  => 'required|exists:users,id',
        'status'         => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Handle image upload
    if ($request->hasFile('site_img')) {
        // delete old image if exists
        if ($site->site_img && file_exists(storage_path('app/public/' . $site->site_img))) {
            unlink(storage_path('app/public/' . $site->site_img));
        }

        $site->site_img = $request->file('site_img')->store('site', 'public');
    }

    // Update site fields
    $site->update([
        'site_name'     => $request->site_name,
        'supervisor_id' => $request->supervisor_id,
        'location'      => $request->location,
        'flat_area'     => $request->flat_area,
        'built_up_area' => $request->built_up_area,
        'duration'      => $request->duration,
        'status'        => $request->status,
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Site Updated Successfully',
        'site'    => $site,
    ]);
}

public function destroy($id)
{
    $site = Site::findOrFail($id);

    // Delete related customers if any
    if ($site->customer) {
        if ($site->customer instanceof \Illuminate\Database\Eloquent\Collection) {
            foreach ($site->customer as $customer) {
                $customer->delete();
            }
        } else {
            $site->customer->delete();
        }
    }

    $site->delete();

    return response()->json(['status' => true,'message' => 'Deleted Successfully!']);
}

  public function siteview(SiteExpenseCalculator $expenseCalculator)
{
    $sites = Site::with(['customer', 'supervisor'])
        ->where('is_inactive', 0)
        ->get();

    $sites->each(function ($site) use ($expenseCalculator) {
        $site->expense = $expenseCalculator->total((int) $site->id);
    });

    return response()->json([
        'status' => true,
        'code' => 200,
        'message' => 'Active sites fetched successfully.',
        'data' => $sites
    ]);
}

public function exportFullReport(SiteExpenseCalculator $expenseCalculator, $id)
{
    $site = Site::findOrFail($id);
    $filename = Str::slug($site->site_name) . '-full-report-' . now()->format('Y-m-d') . '.xlsx';

    return Excel::download(new SiteReportExport($site), $filename);
}

public function addPayment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'site_id' => 'required|exists:sites,id',
        'payment' => 'required|numeric|min:0',
        'date' => 'required|date',
        'payment_mode' => 'required|string',
        'remarks' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $payment = SitePayment::create([
        'site_id' => $request->site_id,
        'payment' => $request->payment,
        'date' => $request->date,
        'payment_mode' => $request->payment_mode,
        'remarks' => $request->remarks,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Site payment added successfully.',
        'data' => $payment,
    ]);
}

public function paymentHistory($siteId)
{
    $site = Site::find($siteId);

    if (!$site) {
        return response()->json([
            'status' => false,
            'message' => 'Site not found.',
        ], 404);
    }

    $histories = SitePayment::where('site_id', $siteId)
        ->orderBy('date')
        ->orderBy('id')
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Site payment history fetched successfully!.',
        'data' => $histories,
    ]);
}

public function exportPaymentHistory(Request $request, $siteId)
{
    $site = Site::findOrFail($siteId);
    $query = SitePayment::where('site_id', $siteId);

    if ($request->filled('from_date')) {
        $query->whereDate('date', '>=', Carbon::parse($request->from_date)->toDateString());
    }

    if ($request->filled('to_date')) {
        $query->whereDate('date', '<=', Carbon::parse($request->to_date)->toDateString());
    }

    $histories = $query->orderBy('date')->orderBy('id')->get();
    $filename = 'site_payment_history_' . $site->id . '_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function () use ($histories) {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['S.No', 'Date', 'Payment', 'Payment Mode', 'Remarks']);

        foreach ($histories as $index => $history) {
            fputcsv($file, [
                $index + 1,
                $history->date ? Carbon::parse($history->date)->format('d-m-Y') : '',
                number_format((float) $history->payment, 2, '.', ''),
                $history->payment_mode,
                $history->remarks,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

private function buildPaymentPdf(SitePayment $payment)
{
    $site = $payment->site;
    $budgetAmount = (float) (optional($site)->budget_amount ?? 0);
    $totalPaid = (float) SitePayment::where('site_id', $payment->site_id)->sum('payment');
    $balanceAmount = max($budgetAmount - $totalPaid, 0);

    $pdf = Pdf::loadView('admin.helper.site_payment_pdf', compact(
        'payment',
        'budgetAmount',
        'totalPaid',
        'balanceAmount'
    ));

    $pdfPath = 'site_payments/site_payment_' . $payment->id . '.pdf';
    Storage::disk('public')->put($pdfPath, $pdf->output());

    return [$pdf, asset('storage/' . $pdfPath)];
}

public function downloadPaymentPdf($id)
{
    $payment = SitePayment::with('site')->find($id);

    if (!$payment) {
        return response()->json([
            'status' => false,
            'message' => 'Payment not found.',
        ], 404);
    }

    [, $pdfUrl] = $this->buildPaymentPdf($payment);

    return response()->json([
        'status' => true,
        'message' => 'Site payment PDF generated successfully.',
        'data' => [
            'pdf_url' => $pdfUrl,
        ],
    ]);
}

public function sendPaymentWhatsapp($id)
{
    $payment = SitePayment::with('site')->find($id);

    if (!$payment) {
        return response()->json([
            'status' => false,
            'message' => 'Payment not found.',
        ], 404);
    }

    $siteName = optional($payment->site)->site_name ?? 'Site';
    [, $pdfUrl] = $this->buildPaymentPdf($payment);

    $customer = Customer::where('site_id', $payment->site_id)
        ->where('is_inactive', 0)
        ->orderBy('id')
        ->first();
    $customerMobile = $customer ? preg_replace('/\D+/', '', $customer->mobile_no) : '';

    if (strlen($customerMobile) === 10) {
        $customerMobile = '91' . $customerMobile;
    }

    $message = "Site Payment History\n"
        . "Site: {$siteName}\n"
        . "Date: " . Carbon::parse($payment->date)->format('d-m-Y') . "\n"
        . "Payment: " . number_format((float) $payment->payment, 2) . "\n"
        . "Payment Mode: {$payment->payment_mode}\n"
        . "PDF: {$pdfUrl}";

    $whatsappUrl = $customerMobile
        ? 'https://wa.me/' . $customerMobile . '?text=' . urlencode($message)
        : 'https://wa.me/?text=' . urlencode($message);

    return response()->json([
        'status' => true,
        'message' => 'WhatsApp link generated successfully.',
        'data' => [
            'whatsapp_url' => $whatsappUrl,
            'pdf_url' => $pdfUrl,
        ],
    ]);
}

public function sendPaymentMail($id)
{
    $payment = SitePayment::with('site')->find($id);

    if (!$payment) {
        return response()->json([
            'status' => false,
            'message' => 'Payment not found.',
        ], 404);
    }

    $customer = Customer::where('site_id', $payment->site_id)
        ->where('is_inactive', 0)
        ->orderBy('id')
        ->first();

    if (!$customer || !$customer->email) {
        return response()->json([
            'status' => false,
            'message' => 'No customer email found for this site.',
        ], 422);
    }

    [$pdf, $pdfUrl] = $this->buildPaymentPdf($payment);

    Mail::to($customer->email)->send(new SitePaymentMail($payment, $pdf->output()));

    return response()->json([
        'status' => true,
        'message' => 'Payment receipt emailed successfully.',
        'data' => [
            'pdf_url' => $pdfUrl,
            'mail_sent' => true,
        ],
    ]);
}

public function paymentSummary($siteId)
{
    $site = Site::with('payments')->find($siteId);

    if (!$site) {
        return response()->json([
            'status' => false,
            'message' => 'Site not found.',
        ], 404);
    }

    $budgetAmount = (float) ($site->budget_amount ?? 0);
    $paidAmount = (float) $site->payments->sum('payment');
    $balanceAmount = max($budgetAmount - $paidAmount, 0);

    return response()->json([
        'status' => true,
        'message' => 'Site payment summary fetched successfully.',
        'data' => [
            'site_id' => $site->id,
            'budget_amount' => $budgetAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
        ],
    ]);
}

}
