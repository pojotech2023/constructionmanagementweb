<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Site;
use App\Models\Customer;
use App\Models\SitePayment;

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
        'location'       => 'required|string',
        'flat_area'      => 'required|string',
        'built_up_area'  => 'required|string',
        'duration'       => 'required|string',
        'supervisor_id'  => 'required|exists:users,id',

        'name'           => 'required|string',
        'mobile_no'      => 'required|numeric|digits:10',
        'email'          => 'required|email|unique:customers,email',
        'dob'            => 'required|date',
        'address'        => 'required|string',
        
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

  public function siteview()
{
    $sites = Site::with(['customer', 'supervisor']) // load both relations
        ->where('is_inactive', 0)
        ->get();

    return response()->json([
        'status' => true,
        'code' => 200,
        'message' => 'Active sites fetched successfully.',
        'data' => $sites
    ]);
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
