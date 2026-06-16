<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Subcontractor;
use App\Models\SubcontractorPayDetail;
use App\Models\SubcontractorPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SubcontractorService;

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

    // If empty → convert to null
    $formattedData = $subcontractors->isEmpty() ? null : $subcontractors;

    return response()->json([
        'response code' => 200,
        'data' => $formattedData,
        'status' => true,
        'message' => 'SubContractor Management fetched successfully.',
    ]);
}

    //subcontractor inner detail
  public function subcontractorData(Request $request, $siteId, $subcontractorType)
{
    $monthYear = $request->input('monthYear', now()->format('Y-m'));
    $week = (int) $request->input('week');

    $startOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
    $endOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->endOfMonth();

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

        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $subcontractorId)->first();
        
         $subcontractor = Subcontractor::find($subcontractorId);

        return response()->json([
            'response code' => 200,
            'data' => [
                'total_amount' => $totalAmount,
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
            'payment_mode' => 'required'
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


}
