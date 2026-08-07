<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialOrder;
use App\Models\Vendor;
use App\Models\VendorPayDetail;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{

    //Vendor Management
    //List
    public function index()
    {
        $vendors = Vendor::orderBy('id', 'desc')->get();

        return response()->json([
            'response code' => 200,
            'data' => $vendors,
            'status' => true,
            'message' => 'Vendors feteched successfully!',
        ]);
    }

    //add
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'          => 'required|string',
            'site_utilities' => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email'         => 'required|email|unique:vendors,email',
            'address'       => 'required|string',
            'gst'           => 'required'
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $vendor = Vendor::create([
            'name' => $request->name,
            'site_utilities' => $request->site_utilities,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'gst' => $request->gst,
            'created_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response code' => 200,
            'data' => $vendor,
            'status' => true,
            'message' => 'Vendor created successfully!',
        ]);
    }

    //update
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'site_utilities' => 'required|string',
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

        $vendor = Vendor::findOrFail($id);

        $vendor->update([
            'name'      => $request->name,
            'site_utilities' => $request->site_utilities,
            'mobile_no' => $request->mobile_no,
            'email'  => $request->email,
            'address'  => $request->address,
            'gst' => $request->gst,
            'updated_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response code' => 200,
            'data' => $vendor,
            'status' => true,
            'message' => 'Vendor updated successfully!',
        ]);
    }

    //delete
    public function delete($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return response()->json([
            'response code' => 200,
            'data' => $vendor,
            'status' => true,
            'message' => 'Vendor deleted successfully!',
        ]);
    }

    //search
    public function search(Request $request)
    {
        $vendors = Vendor::where('name', 'LIKE', $request->name . '%')
            ->select('id', 'name', 'mobile_no', 'address', 'gst')
            ->get();

        return response()->json([
            'response code' => 200,
            'data' => $vendors,
            'status' => true,
            'message' => 'Vendors Feteched Successfully!.'
        ]);
    }

    //Vendor Dashboard
    public function dashboard()
    {
        $vendors = Vendor::with(['vendorPayDetail'])->withSum('vendorPayment', 'payment')->get();

        $vendorData = $vendors->map(function ($vendor) {
            return [
                'vendor_name' => $vendor->name,
                'vendor_id' => $vendor->id,
                'total_amount' => optional($vendor->vendorPayDetail)->total_unit_price + optional($vendor->vendorPayDetail)->opening_balance,
                'paid_amount' => optional($vendor->vendorPayDetail)->paid_amount,
                'pending_amount' => optional($vendor->vendorPayDetail)->balance_amount,
            ];
        });
        return response()->json([
            'response code' => 200,
            'data' => $vendorData,
            'status' => true,
            'message' => 'Vendor Dashboard Feteched Successfully!.'
        ]);
    }

    //Vendor Material Order Details
    public function materialOrders($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);

        $orders = MaterialOrder::with('site')
            ->where('vendor_id', $vendorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $orderList = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'date' => $order->date ? Carbon::parse($order->date)->format('d-m-Y') : null,
                'site_name' => optional($order->site)->site_name,
                'quantity' => $order->quantity,
                'amount' => $order->price,
            ];
        });

        return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => [
                'vendor' => $vendor,
                'orders' => $orderList,
                'total_units' => $orders->sum('quantity'),
                'total_amount' => $orders->sum('price'),
            ],
            'message' => 'Vendor material order details fetched successfully!.'
        ]);
    }

    public function getPayDetailsForm($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);

        $totalUnits = MaterialOrder::where('vendor_id', $vendorId)->sum('quantity');
        $totalAmount = MaterialOrder::where('vendor_id', $vendorId)->sum('price');

        $paydetail = VendorPayDetail::where('vendor_id', $vendorId)->first();
        $paidAmount = VendorPayment::where('vendor_id', $vendorId)->sum('payment');
        $openingBalance = optional($paydetail)->opening_balance ?? 0;
        $balanceAmount = ($totalAmount + $openingBalance) - $paidAmount;

        return response()->json([
            'response code' => 200,
            'data' => [
                'total_units' => $totalUnits,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'opening_balance' => $openingBalance,
                'pay_detail' => $paydetail,
                'vendor' => $vendor
            ],
            'status' => true,
            'message' => 'Vendor Pay Detail Feteched Successfully!.'
        ]);
    }

    public function paydetailUpdate(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'vendor_id'    => 'required|exists:vendors,id',
            'opening_balance' => 'nullable',
            'total_units'   => 'required',
            'total_unit_price' => 'required',
            'balance_amount'  => 'required',
            'paid_amount'  => 'nullable'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $payDetail = VendorPayDetail::where('vendor_id', $request->vendor_id)->first();

        if ($payDetail) {

            $newOpeningBalance = $payDetail->opening_balance + $request->opening_balance;
            $newBalanceAmount = $payDetail->balance_amount + $request->opening_balance;

            $payDetail->update([
                'vendor_id' => $request->vendor_id,
                'opening_balance'    => $newOpeningBalance,
                'total_units' => $request->total_units,
                'total_unit_price' => $request->total_unit_price,
                'balance_amount'  => $newBalanceAmount,
                'paid_amount'  => $request->paid_amount,
                'updated_by' => auth('api')->id(),
            ]);
        } else {
            VendorPayDetail::create([
                'vendor_id'         => $request->vendor_id,
                'opening_balance'    => $request->opening_balance,
                'total_units'       => $request->total_units,
                'total_unit_price'   => $request->total_unit_price,
                'balance_amount'      => $request->opening_balance + $request->total_unit_price,
                'paid_amount' => $request->paid_amount,
                'created_by' => auth('api')->id(),
            ]);
        }

        return response()->json([
            'response code' => 200,
            'status' => true,
            'message' => 'Vendor pay details updated successfully!.'
        ]);
    }

    public function addPayment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
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

        $payment = VendorPayment::create([
            'vendor_id' => $request->vendor_id,
            'payment' => $request->payment,
            'date' => $date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'created_by' => auth('api')->id(),
        ]);

        $payDetail = VendorPayDetail::where('vendor_id', $request->vendor_id)->first();

        if ($payDetail) {
            $payDetail->update([
                'balance_amount' => $payDetail->balance_amount - $request->payment,
                'paid_amount' => $payDetail->paid_amount + $request->payment,
            ]);
        }

        // Get Vendor Info
        $vendor = Vendor::find($request->vendor_id);

        // $message = "Hi {$vendor->name},\nYour payment of ₹{$request->payment} on {$request->date} via {$request->payment_mode} has been recorded. Thank you!";
        // $whatsappUrl = "https://wa.me/{$vendor->mobile_no}?text=" . urlencode($message);

        return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => $payDetail,
            'message' => 'Vendor pay details updated successfully!.'
        ]);
    }

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

        $payment = VendorPayment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        $payment->update([
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'updated_by' => auth('api')->id(),
        ]);

        $vendorId = $payment->vendor_id;
        $paidAmount = VendorPayment::where('vendor_id', $vendorId)->sum('payment');
        $totalAmount = MaterialOrder::where('vendor_id', $vendorId)->sum('price');

        $payDetail = VendorPayDetail::updateOrCreate(
            ['vendor_id' => $vendorId],
            [
                'total_unit_price' => $totalAmount,
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

    public function deletePayment($id)
    {
        $payment = VendorPayment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        $vendorId = $payment->vendor_id;
        $payment->delete();

        $paidAmount = VendorPayment::where('vendor_id', $vendorId)->sum('payment');
        $totalAmount = MaterialOrder::where('vendor_id', $vendorId)->sum('price');

        VendorPayDetail::updateOrCreate(
            ['vendor_id' => $vendorId],
            [
                'total_unit_price' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('api')->id(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }


    public function paymentHistory($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);

        $histories = VendorPayment::with('vendor')
            ->where('vendor_id', $vendorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $historyList = $histories->map(function ($history) {
            return [
                'id' => $history->id,
                'vendor_id' => $history->vendor_id,
                'payment' => $history->payment,
                'date' => $history->date ? Carbon::parse($history->date)->format('d-m-Y') : null,
                'payment_mode' => $history->payment_mode,
                'remarks' => $history->remarks,
                'created_at' => $history->created_at,
                'updated_at' => $history->updated_at,
            ];
        });

        $paidAmount = $histories->sum('payment');
        $totalAmount = MaterialOrder::where('vendor_id', $vendorId)->sum('price');

        return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => [
                'vendor' => $vendor,
                'payment_historyList' => $historyList,
                'total_paidAmount' => $paidAmount,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount - $paidAmount,
            ],
            'message' => 'Vendor payment history fetched successfully!.'
        ]);
    }

    //Export Vendor Payment History (CSV)
    public function exportPaymentHistory(Request $request, $vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $query = VendorPayment::where('vendor_id', $vendorId);

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', Carbon::parse($request->from_date)->toDateString());
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', Carbon::parse($request->to_date)->toDateString());
        }

        $histories = $query->orderBy('date')->orderBy('id')->get();
        $filename = 'vendor_payment_history_' . $vendor->id . '_' . now()->format('Ymd_His') . '.csv';
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
}
