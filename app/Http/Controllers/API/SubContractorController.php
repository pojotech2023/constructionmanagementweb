<?php

namespace App\Http\Controllers\API;

use App\Exports\SubcontractorExport;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Site;
use App\Models\Subcontractor;
use App\Models\SubcontractorPayDetail;
use App\Models\SubcontractorPayment;
use App\Models\SubcontractorType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SubcontractorService;
use Maatwebsite\Excel\Facades\Excel;

class SubContractorController extends Controller
{
    //Subcontractor Management

     //List
     public function index()
    {
        $subcontractors = Subcontractor::orderBy('id', 'desc')->get();
        return response()->json([
            'response code' => 200,
            'data' => $subcontractors,
            'status' => true,
            'message' => 'SubContractors feteched successfully!',
        ]);
    }

    //add
    public function store(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'name'          => 'required|string',
            'subcontractors' => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email'         => 'required|email|unique:subcontractors,email',
            'address'       => 'required|string',
            'gst'           => 'required'
        ]);

         if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

       $subcontractor = Subcontractor::create([
            'name' => $request->name,
            'subcontractors' => $request->subcontractors,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'gst' => $request->gst,
            'created_by' => auth('api')->id(),
        ]);

         return response()->json([
            'response code' => 200,
            'data' => $subcontractor,
            'status' => true,
            'message' => 'SubContractor created successfully!',
        ]);
    }

    //update
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'subcontractors' => 'required|string',
            'mobile_no' => 'required|numeric|digits:10',
            'email'         => 'required|email',
            'address'  => 'required|string',
            'gst'           => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

       $subcontractor = Subcontractor::findOrFail($id);

       $subcontractor->update([
            'name'      => $request->name,
            'subcontractors' => $request->subcontractors,
            'mobile_no' => $request->mobile_no,
            'email'  => $request->email,
            'address'  => $request->address,
            'gst' => $request->gst,
            'updated_by'  => auth('api')->id(),
        ]);

        return response()->json([
            'response code' => 200,
            'data' => $subcontractor,
            'status' => true,
            'message' => 'SubContractor updated successfully!',
        ]);
    }

    //delete
    public function delete($id)
    {
       $subcontractor = Subcontractor::findOrFail($id);
       $subcontractor->delete();
       return response()->json([
            'response code' => 200,
            'data' => $subcontractor,
            'status' => true,
            'message' => 'SubContractor deleted successfully!',
        ]);
    }

    //search
    public function search(Request $request)
    {
       $subcontractors = Subcontractor::where('name', 'LIKE', $request->name . '%')
            ->select('id', 'name', 'mobile_no', 'address')
            ->get();

        return response()->json([
            'response code' => 200,
            'data' => $subcontractors,
            'status' => true,
            'message' => 'SubContractor Feteched Successfully!.'
        ]);
    }


    //subcontractor detail
    public function getSubcontractor($siteId)
{
    $site = Site::with('subcontractorService')->findOrFail($siteId);

    $subcontractors = $site->subcontractorService
        ->groupBy(function ($item) {
            return strtolower($item->subcontractor_type);
        })
        ->mapWithKeys(function ($group, $type) {
            return [
                $type => [
                    'totalAmounts' => $group->sum('amount')
                ]
            ];
        });

    // Remove subcontractor types (fixed or dynamically-added) that the admin has hidden from the grid
    $hidden = Setting::getHiddenSubcontractorTypes();
    $subcontractors = $subcontractors->except($hidden);

    // Include dynamically-added subcontractor types even if they have no services yet,
    // so newly-added cards show up immediately.
    $subcontractorTypes = SubcontractorType::orderBy('name')->get()
        ->reject(function ($type) use ($hidden) {
            return in_array($type->slug, $hidden, true);
        });
    foreach ($subcontractorTypes as $type) {
        if (!$subcontractors->has($type->slug)) {
            $subcontractors[$type->slug] = [
                'totalAmounts' => 0,
            ];
        }
    }

    // If empty → convert to null
    $formattedData = $subcontractors->isEmpty() ? null : $subcontractors;

    return response()->json([
        'response code' => 200,
        'data' => $formattedData,
        'subcontractor_types' => $subcontractorTypes->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'image_url' => $type->image ? asset('storage/' . $type->image) : null,
            ];
        }),
        'status' => true,
        'message' => 'SubContractor Management fetched successfully.',
    ]);
}

    //subcontractor inner detail
  public function subcontractorData(Request $request, $siteId, $subcontractorType)
{
    $monthYear = $request->input('monthYear', now()->format('Y-m'));
    $week = (int) $request->input('week');

    $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    $query = SubcontractorService::with('subcontractor')
        ->where('site_id', $siteId)
        ->where('subcontractor_type', $subcontractorType);

    // ✅ Filter by month first
    $query->whereBetween('date', [$startOfMonth, $endOfMonth]);

    // ✅ Apply week filter if given
    if ($week > 0 && $week <= 5) {
        // Use Carbon week-based division
        $daysInMonth = $startOfMonth->daysInMonth;
        $weekRanges = [];

        for ($i = 1; $i <= ceil($daysInMonth / 7); $i++) {
            $weekStart = $startOfMonth->copy()->addDays(($i - 1) * 7);
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth->endOfDay();
            }

            $weekRanges[$i] = [
                'start' => $weekStart,
                'end' => $weekEnd,
            ];
        }

        if (isset($weekRanges[$week])) {
            $query->whereBetween('date', [$weekRanges[$week]['start'], $weekRanges[$week]['end']]);
        }
    }

    $subcontractors = $query->get();

    $totalAmount = $subcontractors->sum('amount');

    $site = Site::find($siteId);
    $siteName = $site ? $site->site_name : 'Unknown Site';

    return response()->json([
        'subcontractors' => $subcontractors,
        'siteName' => $siteName,
        'totalAmount' => $totalAmount,
    ]);
}
    // subcontractor add service form
    public function subcontractorService(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'subcontractor_type' => 'required|string',
            'date' => 'required',
            'amount' => 'required|numeric',
            'no_counts'=> 'required'
        ]);

         if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');

        $subcontractor_service = SubcontractorService::create([
            'site_id' => $request->site_id,
            'subcontractor_id' => $request->subcontractor_id,
            'subcontractor_type' => $request->subcontractor_type,
            'date' => $date,
            'amount' => $request->amount,
            'no_counts' => $request->no_counts,
            'created_by'  => auth('api')->id(),
        ]);

        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $request->subcontractor_id)->first();
        if ($paydetail) {
            $newTotalAmount =   $paydetail->total_amount + $request->amount;
            $newBalanaceAmount =   $paydetail->balance_amount + $request->amount;

            $paydetail->update([
                'total_amount' => $newTotalAmount,
                'balance_amount' => $newBalanaceAmount
            ]);
        } else {
            SubcontractorPayDetail::create([
                'subcontractor_id' => $request->subcontractor_id,
                'total_amount' => $request->amount,
                'balance_amount'  => $request->amount,
                'created_by'  => auth('admin')->id(),
            ]);
        }

        $site = Site::where('id', $request->site_id)->first();
        $oldExpense = $site->expense;

        $site->update([
            'expense' => $oldExpense + $request->price
        ]);

        $site = Site::find($request->site_id);

        return response()->json([
            'response code' => 200,
            'data' => $subcontractor_service,
            'status' => true,
            'message' => 'SubContractor service added successfully.',
        ]);
    }

     public function dashboard()
    {
        $subcontractors = Subcontractor::with(['subcontractorPayDetail'])->withSum('subcontractorPayment', 'payment')->get();

        $subcontractorData = $subcontractors->map(function ($sub) {
            return [
                'subcontractor_name' => $sub->name,
                'subcontractor_id' => $sub->id,
                'total_amount' => optional($sub->subcontractorPayDetail)->total_amount + optional($sub->subcontractorPayDetail)->opening_balance,
                'paid_amount' => optional($sub->subcontractorPayDetail)->paid_amount,
                'pending_amount' => optional($sub->subcontractorPayDetail)->balance_amount,
            ];
        });

        return response()->json([
            'response code' => 200,
            'data' => $subcontractorData,
            'status' => true,
            'message' => 'SubContractor Dashboard Feteched Successfully!.'
        ]);
    }

    //Opening balc form list
     public function getPayDetailsForm($subcontractorId)
    {
        $services = SubcontractorService::where('subcontractor_id', $subcontractorId)->get();
        $totalAmount = $services->sum('amount');
        $totalUnits = $services->sum('no_counts');

        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $subcontractorId)->first();

         $subcontractor = Subcontractor::find($subcontractorId);

        return response()->json([
            'response code' => 200,
            'data' => [
                'total_amount' => $totalAmount,
                'total_units' => $totalUnits,
                'pay_detail' => $paydetail,
                'subcontractor' => $subcontractor
            ],
            'status' => true,
            'message' => 'SubContractor Pay Detail Feteched Successfully!.'
        ]);
    }

    //Opening balc update or add
    public function subcontractorpayUpdate(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'subcontractor_id'    => 'required|exists:subcontractors,id',
            'opening_balance' => 'nullable',
            'total_amount' => 'required',
            'balance_amount'  => 'required',
            'paid_amount'  => 'nullable'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $payDetail = SubcontractorPayDetail::where('subcontractor_id', $request->subcontractor_id)->first();

        if ($payDetail) {

            $newOpeningBalance = $payDetail->opening_balance + $request->opening_balance;
            $newBalanceAmount = $payDetail->balance_amount + $request->opening_balance;

            $payDetail->update([
                'subcontractor_id' => $request->subcontractor_id,
                'opening_balance'    => $newOpeningBalance,
                'total_amount' => $request->total_amount,
                'balance_amount'  => $newBalanceAmount,
                'paid_amount'  => $request->paid_amount,
                'updated_by'  => auth('admin')->id(),
            ]);
        } else {
            SubcontractorPayDetail::create([
                'subcontractor_id'         => $request->subcontractor_id,
                'opening_balance'    => $request->opening_balance,
                'total_amount'   => $request->total_amount,
                'balance_amount'      => $request->opening_balance + $request->total_amount,
                'paid_amount' => $request->paid_amount,
                'created_by'  => auth('admin')->id(),
            ]);
        }

        return response()->json([
            'response code' => 200,
            'status' => true,
            'message' => 'SubContractor pay details updated successfully!.'
        ]);
    }

     public function addPayment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'payment' => 'required|numeric',
            'date' => 'required|date',
            'payment_mode' => 'required',
            'remarks' => 'nullable|string'
        ]);

         if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

         $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');

        $payment = SubcontractorPayment::create([
            'subcontractor_id' => $request->subcontractor_id,
            'payment' => $request->payment,
            'date' => $date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'created_by'  => auth('admin')->id(),
        ]);

        $payDetail = SubcontractorPayDetail::where('subcontractor_id', $request->subcontractor_id)->first();

        if ($payDetail) {
            $payDetail->update([
                'balance_amount' => $payDetail->balance_amount - $request->payment,
                'paid_amount' => $payDetail->paid_amount + $request->payment,
            ]);
        }

        // Get Subcontractor Info
        // $subcontractor = Subcontractor::find($request->subcontractor_id);

        // $message = "Hi {$subcontractor->name},\nYour payment of ₹{$request->payment} on {$request->date} via {$request->payment_mode} has been recorded. Thank you!";
        // $whatsappUrl = "https://wa.me/{$subcontractor->mobile_no}?text=" . urlencode($message);

       return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => $payDetail,
            'message' => 'SubContractor pay details updated successfully!.'
        ]);
    }


    // Edit a payment entry (used by Petty Cash / Rental Management / regular subcontractor payment history action buttons)
    public function updatePayment(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'payment' => 'required|numeric',
            'date' => 'required|date',
            'payment_mode' => 'required',
            'remarks' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors(),
            ], 422);
        }

        $payment = SubcontractorPayment::findOrFail($id);

        $payment->update([
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'updated_by' => auth('api')->id(),
        ]);

        $paidAmount = SubcontractorPayment::where('subcontractor_id', $payment->subcontractor_id)->sum('payment');
        $totalAmount = SubcontractorService::where('subcontractor_id', $payment->subcontractor_id)->sum('amount');

        $payDetail = SubcontractorPayDetail::updateOrCreate(
            ['subcontractor_id' => $payment->subcontractor_id],
            [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('api')->id(),
            ]
        );

        return response()->json([
            'response code' => 200,
            'status' => true,
            'message' => 'Payment updated successfully.',
            'data' => [
                'payment' => $payment,
                'pay_detail' => $payDetail,
            ],
        ]);
    }

    // Delete a payment entry (used by Petty Cash / Rental Management / regular subcontractor payment history action buttons)
    public function deletePayment($id)
    {
        $payment = SubcontractorPayment::findOrFail($id);
        $subcontractorId = $payment->subcontractor_id;

        $payment->delete();

        $paidAmount = SubcontractorPayment::where('subcontractor_id', $subcontractorId)->sum('payment');
        $totalAmount = SubcontractorService::where('subcontractor_id', $subcontractorId)->sum('amount');

        $payDetail = SubcontractorPayDetail::updateOrCreate(
            ['subcontractor_id' => $subcontractorId],
            [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('api')->id(),
            ]
        );

        return response()->json([
            'response code' => 200,
            'status' => true,
            'message' => 'Payment deleted successfully.',
            'data' => [
                'pay_detail' => $payDetail,
            ],
        ]);
    }

    public function paymentHistory($subcontractorId)
    {
        $histories = SubcontractorPayment::with('subcontractor')
            ->where('subcontractor_id', $subcontractorId)
            ->get();

        $paidAmount = $histories->sum('payment');

         return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => [
                'payment_historyList' => $histories,
                'total_paidAmount' => $paidAmount
            ],
            'message' => 'SubContractor payment history fetched successfully!.'
        ]);
    }

    //Subcontractor Orders/Service Details
    public function subcontractorOrders($subcontractorId)
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);

        $services = SubcontractorService::with('site')
            ->where('subcontractor_id', $subcontractorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $orderList = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'date' => $service->date ? Carbon::parse($service->date)->format('d-m-Y') : null,
                'site_name' => optional($service->site)->site_name,
                'no_counts' => $service->no_counts,
                'amount' => $service->amount,
                'status' => $service->status,
                'remarks' => $service->remarks,
            ];
        });

        return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => [
                'subcontractor' => $subcontractor,
                'orders' => $orderList,
                'total_counts' => $services->sum('no_counts'),
                'total_amount' => $services->sum('amount'),
            ],
            'message' => 'Subcontractor order/service details fetched successfully!.'
        ]);
    }

    //Export Subcontractor Payment History (CSV)
    public function exportPaymentHistory(Request $request, $subcontractorId)
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);
        $query = SubcontractorPayment::where('subcontractor_id', $subcontractorId);

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', Carbon::parse($request->from_date)->toDateString());
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', Carbon::parse($request->to_date)->toDateString());
        }

        $histories = $query->orderBy('date')->orderBy('id')->get();
        $filename = 'subcontractor_payment_history_' . $subcontractor->id . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($histories) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.No', 'Date', 'Total Payment', 'Payment Mode', 'Remarks']);

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

    public function updateService(Request $request, $id)
    {
        $service = SubcontractorService::find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service record not found.'], 404);
        }

        $validate = Validator::make($request->all(), [
            'subcontractor_type' => 'required|string',
            'date'               => 'required',
            'amount'             => 'required|numeric',
            'no_counts'          => 'required',
            'remarks'            => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validate->errors()], 422);
        }

        try {
            $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = Carbon::parse($request->date)->toDateString();
        }

        $oldAmount = (float) $service->amount;
        $newAmount = (float) $request->amount;

        $service->update([
            'subcontractor_type' => $request->subcontractor_type,
            'date'               => $date,
            'amount'             => $newAmount,
            'no_counts'          => $request->no_counts,
            'remarks'            => $request->remarks,
            'updated_by'         => auth('api')->id(),
        ]);

        $amountDiff = $newAmount - $oldAmount;
        if ($amountDiff != 0) {
            $paydetail = SubcontractorPayDetail::where('subcontractor_id', $service->subcontractor_id)->first();
            if ($paydetail) {
                $paydetail->update([
                    'total_amount'   => $paydetail->total_amount + $amountDiff,
                    'balance_amount' => $paydetail->balance_amount + $amountDiff,
                ]);
            }
        }

        return response()->json([
            'response code' => 200,
            'status'        => true,
            'message'       => 'SubContractor service updated successfully.',
            'data'          => $service,
        ]);
    }

    public function destroyService($id)
    {
        $service = SubcontractorService::find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service record not found.'], 404);
        }

        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $service->subcontractor_id)->first();
        if ($paydetail) {
            $paydetail->update([
                'total_amount'   => max(0, $paydetail->total_amount - $service->amount),
                'balance_amount' => max(0, $paydetail->balance_amount - $service->amount),
            ]);
        }

        $service->delete();

        return response()->json([
            'response code' => 200,
            'status'        => true,
            'message'       => 'SubContractor service deleted successfully.',
        ]);
    }

    private function getOrCreateWrapperSubcontractor(Site $site, string $emailPrefix, string $label)
    {
        $email = $emailPrefix . '.site.' . $site->id . '@local.invalid';

        return Subcontractor::firstOrCreate(
            ['email' => $email],
            [
                'name' => $label . ' - ' . $site->site_name,
                'subcontractors' => $label,
                'mobile_no' => '0000000000',
                'address' => $site->address ?? $site->site_name,
                'gst' => 'N/A',
                'created_by' => auth('api')->id(),
            ]
        );
    }

    private function wrapperPaymentDetail($siteId, string $emailPrefix, string $label)
    {
        $site = Site::findOrFail($siteId);
        $subcontractor = $this->getOrCreateWrapperSubcontractor($site, $emailPrefix, $label);

        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $subcontractor->id)->first();
        $histories = SubcontractorPayment::where('subcontractor_id', $subcontractor->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $paidAmount = $histories->sum('payment');

        return response()->json([
            'response code' => 200,
            'status' => true,
            'message' => $label . ' payment detail fetched successfully.',
            'data' => [
                'subcontractor_id' => $subcontractor->id,
                'site_id' => $site->id,
                'site_name' => $site->site_name,
                'pay_detail' => $paydetail,
                'payment_history' => $histories,
                'total_paid_amount' => $paidAmount,
            ],
        ]);
    }

    private function wrapperExport(Request $request, $siteId, string $emailPrefix, string $label, string $fileSlug)
    {
        $site = Site::findOrFail($siteId);
        $email = $emailPrefix . '.site.' . $site->id . '@local.invalid';
        $subcontractor = Subcontractor::where('email', $email)->firstOrFail();

        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $historyQuery = SubcontractorPayment::where('subcontractor_id', $subcontractor->id);

        if ($fromDate) {
            $historyQuery->whereDate('date', '>=', Carbon::parse($fromDate)->toDateString());
        }

        if ($toDate) {
            $historyQuery->whereDate('date', '<=', Carbon::parse($toDate)->toDateString());
        }

        $histories = $historyQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $filename = $fileSlug . '_' . $site->id . '_' . now()->format('Ymd_His') . '.csv';
        $filePath = 'exports/' . $filename;

        $csv = "S.No,Date,Payment Mode,Remarks,Payment\n";
        foreach ($histories as $index => $history) {
            $csv .= implode(',', [
                $index + 1,
                $history->date ? Carbon::parse($history->date)->format('d-m-Y') : '',
                '"' . str_replace('"', '""', $history->payment_mode) . '"',
                '"' . str_replace('"', '""', (string) $history->remarks) . '"',
                number_format((float) $history->payment, 2, '.', ''),
            ]) . "\n";
        }

        \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $csv);

        return response()->json([
            'status' => true,
            'message' => $label . ' export generated successfully.',
            'download_url' => asset('storage/' . $filePath),
        ]);
    }

    public function pettyCashPaymentDetail($siteId)
    {
        return $this->wrapperPaymentDetail($siteId, 'pettycash', 'Petty Cash');
    }

    public function exportPettyCash(Request $request, $siteId)
    {
        return $this->wrapperExport($request, $siteId, 'pettycash', 'Petty Cash', 'petty_cash');
    }

    public function rentalManagementPaymentDetail($siteId)
    {
        return $this->wrapperPaymentDetail($siteId, 'rental', 'Rental Management');
    }

    public function exportRentalManagement(Request $request, $siteId)
    {
        return $this->wrapperExport($request, $siteId, 'rental', 'Rental Management', 'rental_management');
    }

    public function exportSubcontractor(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'month'   => 'nullable|date_format:Y-m',
        ]);

        $fileName = 'subcontractor_' . $request->site_id . '_' . time() . '.xlsx';
        $filePath = 'exports/' . $fileName;

        try {
            Excel::store(
                new SubcontractorExport($request->site_id, $request->month),
                $filePath,
                'public'
            );

            return response()->json([
                'status'       => true,
                'message'      => 'Subcontractor Excel file generated successfully.',
                'download_url' => asset('storage/' . $filePath),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

}
