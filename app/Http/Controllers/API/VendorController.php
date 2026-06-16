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
            ->select('id', 'name', 'mobile_no', 'address')
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

    public function getPayDetailsForm($vendorId)
    {
        $orders = MaterialOrder::where('vendor_id', $vendorId)->get();
        $totalUnits = $orders->sum('quantity');
        $totalAmount = $orders->sum('price');

        $paydetail = VendorPayDetail::where('vendor_id', $vendorId)->first();

        $vendor = Vendor::find($vendorId);

        return response()->json([
            'response code' => 200,
            'data' => [
                'total_units' => $totalUnits,
                'total_amount' => $totalAmount,
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
            'payment_mode' => 'required'
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


    public function paymentHistory($vendorId)
    {
        $histories = VendorPayment::with('vendor')
            ->where('vendor_id', $vendorId)
            ->get();

        $paidAmount = $histories->sum('payment');

        return response()->json([
            'response code' => 200,
            'status' => true,
            'data' => [
                'payment_historyList' => $histories,
                'total_paidAmount' => $paidAmount
            ],
            'message' => 'Vendor payment history fetched successfully!.'
        ]);
    }
}
