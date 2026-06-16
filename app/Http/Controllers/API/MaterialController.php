<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialOrder;
use App\Models\MaterialPayment;
use App\Models\MaterialRequest;
use App\Models\Site;
use App\Models\Vendor;
use App\Models\VendorPayDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{

    public function getMaterial($siteId)
    {
        $site = Site::with('materialOrders')->findOrFail($siteId);

        // Group material orders by lowercase material_type
        $materials = $site->materialOrders->groupBy(function ($item) {
            return strtolower($item->material_type);
        })->mapWithKeys(function ($group, $type) {
            return [
                $type => [
                    'units' => $group->sum('quantity'),
                    'values' => $group->sum('price'),
                ]
            ];
        });

        return response()->json([
            'response code' => 200,
            'data' => $materials,
            'status' => true,
            'message' => 'Material Management fetched successfully.',
        ]);
    }
public function materialData(Request $request, $siteId, $materialType)
{
    $monthYear = $request->input('monthYear', now()->format('Y-m'));
    $week = $request->input('week', 0);

    // Convert month-year to Carbon object
    $month = Carbon::parse($monthYear)->month;
    $year = Carbon::parse($monthYear)->year;

    // Base query
    $query = MaterialOrder::with('vendor')
        ->where('site_id', $siteId)
        ->whereRaw('LOWER(TRIM(material_type)) = ?', [strtolower(trim($materialType))])
        ->whereMonth('date', $month)
        ->whereYear('date', $year);

    // Apply week filter if provided
    if ($week > 0 && $week <= 5) {
        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $weekStart = $startOfMonth->copy()->addDays(($week - 1) * 7);
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
        $query->whereBetween('date', [$weekStart, $weekEnd]);
    }

    $materials = $query->get();

    // Debug info (you can remove later)
    if ($materials->isEmpty()) {
        \Log::info('No materials found', [
            'site_id' => $siteId,
            'material_type' => $materialType,
            'month' => $month,
            'year' => $year,
        ]);
    }

    $totalUnits = $materials->sum('quantity');
    $totalAmount = $materials->sum('price');

    // Payments
    $paymentQuery = MaterialPayment::where('site_id', $siteId)
        ->whereRaw('LOWER(TRIM(material_type)) = ?', [strtolower(trim($materialType))])
        ->whereMonth('date', $month)
        ->whereYear('date', $year);

    if ($week > 0 && $week <= 5) {
        $paymentQuery->whereBetween('date', [$weekStart, $weekEnd]);
    }

    $settledAmount = $paymentQuery->sum('settled_amount');
    $pendingAmount = $paymentQuery->sum('pending_amount');

    $site = Site::find($siteId);
    $siteName = $site ? $site->site_name : 'Unknown Site';

    return response()->json([
        'siteName' => $siteName,
        'materials' => $materials,
        'totalUnits' => $totalUnits,
        'totalAmount' => $totalAmount,
        'settledAmount' => $settledAmount,
        'pendingAmount' => $pendingAmount,
    ]);
}

public function materialRequest(Request $request)
{
    $role = auth('api')->user()->role_name ?? null;  // admin / supervisor

    // Dynamic validation rule
    $vendorRule = ($role === 'Admin') ? 'required|exists:vendors,id' : 'nullable|exists:vendors,id';

    $validate = Validator::make($request->all(), [
        'site_id'            => 'required|exists:sites,id',
        'vendor_id'          => $vendorRule,
        'items'              => 'nullable|string',
        'material_type'      => 'nullable|string',
        'quantity'           => 'required|numeric',
        'unit'               => 'nullable|string',
        'date_of_delivery'   => 'nullable|string',
        'delivery_needed_by' => 'nullable|string',
        'price'              => 'nullable|numeric',
        'amount'             => 'nullable|numeric',
        'remarks'            => 'nullable|string',
        'attachment'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'category_name'      => 'nullable|string',
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors(),
        ], 422);
    }

    // File upload (optional)
    $imageUrl = null;
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $path = $file->store('material_attachments', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    // normalize fields
    $items = $request->input('items', $request->input('material_type'));
    $rawDate = $request->input('date_of_delivery', $request->input('delivery_needed_by'));
    $price = $request->input('price', $request->input('amount'));

    // parse date in flexible formats (29/03/2026, 29-03-2026, 2026-03-29)
    $dateOfDelivery = null;
    if ($rawDate) {
        try {
            $dateOfDelivery = Carbon::parse(str_replace('/', '-', $rawDate))->toDateString();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'errors' => ['delivery_needed_by' => ['The delivery needed by field must be a valid date.']],
            ], 422);
        }
    }

    // vendor_id safety (for non-admin supervisors, allow missing/invalid by clearing)
    $vendorId = $request->input('vendor_id');
    if ($vendorId && !Vendor::find($vendorId)) {
        $vendorId = null;
    }

    // Create material request
    $materialRequest = MaterialRequest::create([
        'site_id'            => $request->site_id,
        'vendor_id'          => $vendorId,
        'items'              => $items,
        'material_type'      => $request->material_type,
        'quantity'           => $request->quantity,
        'unit'               => $request->unit,
        'date_of_delivery'   => $dateOfDelivery,
        'delivery_needed_by' => $rawDate,
        'price'              => $price,
        'remarks'            => $request->remarks,
        'image_url'          => $imageUrl,
        'created_by'         => auth('api')->id(),
    ]);

    // Site details
    $site = Site::with('supervisor')
        ->select('id', 'site_name', 'location', 'supervisor_id')
        ->find($request->site_id);

    // Vendor details (only if vendor_id present)
    $vendor = null;
    if ($request->vendor_id) {
        $vendor = Vendor::select('id', 'name', 'mobile_no', 'address', 'email')
            ->find($request->vendor_id);
    }

    // Response
    return response()->json([
        'response_code' => 200,
        'status' => true,
        'message' => 'Request has been sent successfully.',
        'data' => [
            'material_request' => $materialRequest,
            'site_details' => [
                'site_id' => $site->id,
                'site_name' => $site->site_name,
                'category_name' => $request->category_name ?? null,
                'location' => $site->location,
                'supervisor' => $site->supervisor,
            ],
            'vendor_details' => $vendor,  // null for supervisor
        ],
    ]);
}


  public function materialOrder(Request $request)
{
    $validate = Validator::make($request->all(), [
        'site_id' => 'required|exists:sites,id',
        'vendor_id' => 'required|exists:vendors,id',
        'material_type' => 'required|string',
        'date' => 'required',
        'quantity' => 'required|numeric',
        'price' => 'required|numeric',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // ✅ optional file
        'category_name' => 'nullable|string', // ✅ not stored but shown in response
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors(),
        ], 422);
    }

    // ✅ Convert date safely
    try {
        $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid date format. Please use d-m-Y format.',
        ], 422);
    }

    // ✅ Handle optional file upload
    $imageUrl = null;
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $path = $file->store('material_attachments', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    // ✅ Create material order record
    $materialOrder = MaterialOrder::create([
        'site_id' => $request->site_id,
        'vendor_id' => $request->vendor_id,
        'material_type' => $request->material_type,
        'date' => $date,
        'quantity' => $request->quantity,
        'unit' => $request->unit,
        'price' => $request->price,
        'available_unit_count' => $request->available_unit_count,
        'image_url' => $imageUrl, // ✅ store image URL
        'created_by' => auth('api')->id(),
    ]);

    // ✅ Update vendor payment details
    $paydetail = VendorPayDetail::where('vendor_id', $request->vendor_id)->first();
    if ($paydetail) {
        $paydetail->update([
            'total_units' => $paydetail->total_units + $request->quantity,
            'total_unit_price' => $paydetail->total_unit_price + $request->price,
            'balance_amount' => $paydetail->balance_amount + $request->price,
        ]);
    } else {
        VendorPayDetail::create([
            'vendor_id' => $request->vendor_id,
            'total_units' => $request->quantity,
            'total_unit_price' => $request->price,
            'balance_amount' => $request->price,
            'created_by' => auth('api')->id(),
        ]);
    }

    // ✅ Fetch site details (with supervisor)
    $site = Site::with('supervisor')
        ->select('id', 'site_name', 'location', 'supervisor_id')
        ->find($request->site_id);

    // ✅ Fetch vendor details
    $vendor = Vendor::select('id', 'name', 'mobile_no', 'address', 'email')
        ->find($request->vendor_id);

    // ✅ Return API response
    return response()->json([
        'response_code' => 200,
        'status' => true,
        'message' => 'Material order added successfully.',
        'data' => [
            'material_order' => $materialOrder,
            'site_details' => [
                'site_id' => $site->id,
                'site_name' => $site->site_name,
                'category_name' => $request->category_name ?? null,
                'location' => $site->location,
                'supervisor' => $site->supervisor,
            ],
            'vendor_details' => $vendor,
             // ✅ shown only in response
        ],
    ]);
}
public function index()
    {
        $materials = [
            [
                'id' => 1,
                'material_type' => 'bricks',
                'category_name' => ['Red Brick', 'Fly Ash Bricks', 'AAC block', 'Solid block'],
                'unit' => ['Units', 'Kg', 'Ton', 'Size','Inch','Dia','No'],
                'attachment' => false,
            ],
            [
                'id' => 2,
                'material_type' => 'steel',
                'unit' => ['Units', 'Kg', 'Tone', 'Size', 'Inch', 'Dia','No'],
                'attachment' => false,
            ],
            [
                'id' => 3,
                'material_type' => 'aggregate',
                'unit' => ['Unit', 'Tone', 'Cft', 'M sand', 'P sand', '20mm', '40mm'],
                'attachment' => false,
            ],
            [
                'id' => 4,
                'material_type' => 'cement',
                'unit' => ['In Bags'],
                'attachment' => false,
            ],
            [
                'id' => 5,
                'material_type' => 'electricalwire',
                'unit' => null,
                'attachment' => true,
            ],
            [
                'id' => 6,
                'material_type' => 'plumber',
                'unit' => null,
                'attachment' => true,
            ],
            [
                'id' => 7,
                'material_type' => 'rmc',
                'unit' => ['m cube'],
                'attachment' => false,
            ],
            [
                'id' => 8,
                'material_type' => 'painting',
                'unit' => null,
                'attachment' => true,
            ],
            [
                'id' => 9,
                'material_type' => 'default',
                // 'unit' => ['Load', 'Pack', 'Ltr', 'Kg', 'Pieces', 'M cube', 'CFT', 'Unit', 'Bag', 'Tone', 'Numbers'],
                'unit' => ['Units', 'Kg', 'Ton', 'Size', 'Inch', 'Dia', 'NO'],
                'attachment' => false,
            ],
        ];

        return response()->json([
            'status' => 'true',
            'data' => $materials
        ]);
    }

}
