<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialOrder;
use App\Models\Vendor;
use App\Models\VendorPayDetail;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('id', 'desc')->get();
        return view('admin.menus.vendor.vendor_managment', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'          => 'required|string',
            'site_utilities' => 'nullable|string',
            'mobile_no'     => 'nullable|numeric|digits:10',
            'email'         => 'nullable|email|unique:vendors,email',
            'address'       => 'nullable|string',
            'gst'           => 'nullable'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $vendor = Vendor::create([
            'name' => $request->name,
            'site_utilities' => $request->filled('site_utilities') ? $request->site_utilities : null,
            'mobile_no' => $request->filled('mobile_no') ? $request->mobile_no : null,
            'email' => $request->filled('email') ? $request->email : null,
            'address' => $request->filled('address') ? $request->address : null,
            'gst' => $request->filled('gst') ? $request->gst : null,
            'created_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Vendor created successfully!');
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'site_utilities' => 'nullable|string',
            'mobile_no' => 'nullable|numeric|digits:10',
            'email'         => 'nullable|email',
            'address'  => 'nullable|string',
            'gst'           => 'nullable'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $vendor = Vendor::findOrFail($request->vendor_id);

        $vendor->update([
            'name'      => $request->name,
            'site_utilities' => $request->filled('site_utilities') ? $request->site_utilities : null,
            'mobile_no' => $request->filled('mobile_no') ? $request->mobile_no : null,
            'email'  => $request->filled('email') ? $request->email : null,
            'address'  => $request->filled('address') ? $request->address : null,
            'gst' => $request->filled('gst') ? $request->gst : null,
            'updated_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Vendor updated successfully!');
    }

    public function delete($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
        return back()->with('success', 'Vendor Deleted Successfully!');
    }

    public function search(Request $request)
    {
        $vendors = Vendor::where('name', 'LIKE', $request->name . '%')
            ->select('id', 'name', 'mobile_no', 'address')
            ->get();

        return response()->json($vendors);
    }

    public function dashboard()
    {
        $vendors = Vendor::with(['vendorPayDetail'])->withSum('vendorPayment', 'payment')->get();

        return view('admin.menus.vendor.vendor_dashboard', compact('vendors'));
    }

    public function getPayDetailsForm($vendorId)
    {
        $orders = MaterialOrder::where('vendor_id', $vendorId)->get();
        $totalUnits = $orders->sum('quantity');
        $totalAmount = $orders->sum('price');

        $paydetail = VendorPayDetail::where('vendor_id', $vendorId)->first();

        return view('admin.menus.vendor.vendor_paydetail', compact('totalUnits', 'totalAmount', 'vendorId', 'paydetail'));
    }

    public function vendorpayUpdate(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'vendor_id'    => 'required|exists:vendors,id',
            'opening_balance' => 'nullable',
            'total_units'   => 'required',
            'total_unit_price' => 'required',
            'balance_amount'  => 'required',
            'paid_amount'  => 'nullable'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
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
                'updated_by'  => auth('admin')->id(),
            ]);
        } else {
            VendorPayDetail::create([
                'vendor_id'         => $request->vendor_id,
                'opening_balance'    => $request->opening_balance,
                'total_units'       => $request->total_units,
                'total_unit_price'   => $request->total_unit_price,
                'balance_amount'      => $request->opening_balance + $request->total_unit_price,
                'paid_amount' => $request->paid_amount,
                'created_by'  => auth('admin')->id(),
            ]);
        }

        return redirect()->back()->with('success', 'Vendor pay details updated successfully!');
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

        $payment = VendorPayment::create([
            'vendor_id' => $request->vendor_id,
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'created_by'  => auth('admin')->id(),
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
        //$whatsappUrl = "https://wa.me/{$vendor->mobile_no}?text=" . urlencode($message);

        return response()->json([
            'status' => 'success',
            //'whatsapp_url' => $whatsappUrl
        ]);
    }


    public function paymentHistory($vendorId)
    {
        $histories = VendorPayment::with('vendor')
            ->where('vendor_id', $vendorId)
            ->get();

        $paidAmount = $histories->sum('payment');

        return view('admin.menus.vendor.payment_history', compact('histories', 'vendorId', 'paidAmount'));
    }
}
