<?php

namespace App\Http\Controllers\Admin;

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
            'gst'           => 'nullable|alpha_num|max:15'
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
            'gst'           => 'nullable|alpha_num|max:15'
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
            ->select('id', 'name', 'mobile_no', 'address', 'gst')
            ->get();

        return response()->json($vendors);
    }

    public function dashboard()
    {
        $vendors = Vendor::withSum('vendorPayment', 'payment')
            ->withSum('materialOrders', 'price')
            ->get();

        return view('admin.menus.vendor.vendor_dashboard', compact('vendors'));
    }

    public function getPayDetailsForm($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $orders = MaterialOrder::with('site')
            ->where('vendor_id', $vendorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $totalUnits = $orders->sum('quantity');
        $totalAmount = $orders->sum('price');

        $paydetail = VendorPayDetail::where('vendor_id', $vendorId)->first();
        $paidAmount = VendorPayment::where('vendor_id', $vendorId)->sum('payment');
        $balanceAmount = $totalAmount - $paidAmount;

        return view('admin.menus.vendor.vendor_paydetail', compact('vendor', 'orders', 'totalUnits', 'totalAmount', 'vendorId', 'paydetail', 'paidAmount', 'balanceAmount'));
    }

    public function vendorpayUpdate(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'vendor_id'    => 'required|exists:vendors,id',
            'total_units'   => 'required',
            'total_unit_price' => 'required',
            'balance_amount'  => 'required',
            'paid_amount'  => 'nullable'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $payDetail = VendorPayDetail::where('vendor_id', $request->vendor_id)->first();
        $paidAmount = VendorPayment::where('vendor_id', $request->vendor_id)->sum('payment');
        $balanceAmount = (float) $request->total_unit_price - (float) $paidAmount;

        if ($payDetail) {
            $payDetail->update([
                'vendor_id' => $request->vendor_id,
                'total_units' => $request->total_units,
                'total_unit_price' => $request->total_unit_price,
                'balance_amount'  => $balanceAmount,
                'paid_amount'  => $paidAmount,
                'updated_by'  => auth('admin')->id(),
            ]);
        } else {
            VendorPayDetail::create([
                'vendor_id'         => $request->vendor_id,
                'total_units'       => $request->total_units,
                'total_unit_price'   => $request->total_unit_price,
                'balance_amount'      => $balanceAmount,
                'paid_amount' => $paidAmount,
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
            'payment_mode' => 'required',
            'remarks' => 'nullable|string'
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
            'remarks' => $request->remarks,
            'created_by'  => auth('admin')->id(),
        ]);

        $paidAmount = VendorPayment::where('vendor_id', $request->vendor_id)->sum('payment');
        $totalUnits = MaterialOrder::where('vendor_id', $request->vendor_id)->sum('quantity');
        $totalAmount = MaterialOrder::where('vendor_id', $request->vendor_id)->sum('price');

        VendorPayDetail::updateOrCreate(
            ['vendor_id' => $request->vendor_id],
            [
                'total_units' => $totalUnits,
                'total_unit_price' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('admin')->id(),
            ]
        );

        // Get Vendor Info
        $vendor = Vendor::find($request->vendor_id);

       // $message = "Hi {$vendor->name},\nYour payment of ₹{$request->payment} on {$request->date} via {$request->payment_mode} has been recorded. Thank you!";
        //$whatsappUrl = "https://wa.me/{$vendor->mobile_no}?text=" . urlencode($message);

        return response()->json([
            'status' => 'success',
            //'whatsapp_url' => $whatsappUrl
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'payment' => 'required|numeric',
            'date' => 'required|date',
            'payment_mode' => 'required',
            'remarks' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $payment = VendorPayment::findOrFail($id);

        $payment->update([
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'updated_by' => auth('admin')->id(),
        ]);

        $paidAmount = VendorPayment::where('vendor_id', $payment->vendor_id)->sum('payment');
        $totalAmount = MaterialOrder::where('vendor_id', $payment->vendor_id)->sum('price');

        VendorPayDetail::updateOrCreate(
            ['vendor_id' => $payment->vendor_id],
            [
                'total_unit_price' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('admin')->id(),
            ]
        );

        return redirect()->back()->with('success', 'Payment updated successfully.');
    }

    public function deletePayment($id)
    {
        $payment = VendorPayment::findOrFail($id);
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
                'updated_by' => auth('admin')->id(),
            ]
        );

        return redirect()->back()->with('success', 'Payment deleted successfully.');
    }


    public function paymentHistory($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $histories = VendorPayment::with('vendor')
            ->where('vendor_id', $vendorId)
            ->get();

        $paidAmount = $histories->sum('payment');

        return view('admin.menus.vendor.payment_history', compact('vendor', 'histories', 'vendorId', 'paidAmount'));
    }

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
