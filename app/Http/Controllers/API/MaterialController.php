<?php

namespace App\Http\Controllers\API;

use App\Exports\MaterialExport;
use App\Http\Controllers\Controller;
use App\Models\MaterialOrder;
use App\Models\MaterialPayment;
use App\Models\MaterialRequest;
use App\Models\MaterialType;
use App\Models\Setting;
use App\Models\Site;
use App\Models\Vendor;
use App\Models\VendorPayDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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

        // Remove material types (fixed or dynamically-added) that the admin has hidden from the grid
        $hidden = Setting::getHiddenMaterialTypes();
        $materials = $materials->except($hidden);

        // Include dynamically-added material types (App\Models\MaterialType) even if
        // they have no orders yet, so newly-added cards show up immediately.
        $materialTypes = MaterialType::orderBy('name')->get()
            ->reject(function ($type) use ($hidden) {
                return in_array($type->slug, $hidden, true);
            });
        foreach ($materialTypes as $type) {
            if (!$materials->has($type->slug)) {
                $materials[$type->slug] = [
                    'units' => 0,
                    'values' => 0,
                ];
            }
        }

        return response()->json([
            'response code' => 200,
            'data' => $materials,
            'material_types' => $materialTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'image_url' => $type->image ? asset('storage/' . $type->image) : null,
                ];
            }),
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
    $role = auth('api')->user()->roles->first()->role_name ?? null;  // Admin / Supervisor

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
        'source'             => strtolower((string) $role) === 'supervisor'
            ? MaterialRequest::SOURCE_SUPERVISOR
            : MaterialRequest::SOURCE_ADMIN,
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

    // Admin: view all supervisor-submitted material requests for a site
    public function requestList($siteId)
    {
        Site::findOrFail($siteId);

        $requests = MaterialRequest::with('vendor')
            ->where('site_id', $siteId)
            ->where('source', MaterialRequest::SOURCE_SUPERVISOR)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'data' => $requests,
        ]);
    }

    // Admin: approve a supervisor-submitted material request
    public function approveRequest($id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);

        $materialRequest->update([
            'status' => MaterialRequest::STATUS_APPROVED,
            'admin_remark' => null,
            'reviewed_at' => now(),
            'updated_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Request approved successfully.',
            'data' => $materialRequest,
        ]);
    }

    // Admin: reject a supervisor-submitted material request
    public function rejectRequest(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'admin_remark' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors(),
            ], 422);
        }

        $materialRequest = MaterialRequest::findOrFail($id);

        $materialRequest->update([
            'status' => MaterialRequest::STATUS_REJECTED,
            'admin_remark' => $request->admin_remark,
            'reviewed_at' => now(),
            'updated_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Request rejected successfully.',
            'data' => $materialRequest,
        ]);
    }

    // Supervisor: view the status of material requests they submitted for a site
    public function myRequests($siteId)
    {
        $requests = MaterialRequest::where('site_id', $siteId)
            ->where('created_by', auth('api')->id())
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'material_type' => $req->material_type,
                    'items' => $req->items,
                    'quantity' => $req->quantity,
                    'unit' => $req->unit,
                    'date_of_delivery' => $req->date_of_delivery,
                    'remarks' => $req->remarks,
                    'status' => $req->status,
                    'status_label' => $req->status_label,
                    'admin_remark' => $req->admin_remark,
                    'reviewed_at' => $req->reviewed_at,
                    'created_at' => $req->created_at,
                ];
            });

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'data' => $requests,
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
        'gst' => 'nullable|numeric',
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
        'gst' => $request->gst,
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
public function exportMaterial(Request $request)
    {
        $request->validate([
            'site_id'       => 'required|exists:sites,id',
            'material_type' => 'nullable|string',
            'month'         => 'nullable|date_format:Y-m',
        ]);

        $fileName = 'material_' . $request->site_id . '_' . time() . '.xlsx';
        $filePath = 'exports/' . $fileName;

        try {
            Excel::store(
                new MaterialExport($request->site_id, $request->material_type, $request->month),
                $filePath,
                'public'
            );

            return response()->json([
                'status'       => true,
                'message'      => 'Material Excel file generated successfully.',
                'download_url' => asset('storage/' . $filePath),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateMaterial(Request $request, $id)
    {
        $order = MaterialOrder::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Material order not found.'], 404);
        }

        $validate = Validator::make($request->all(), [
            'material_type' => 'required|string',
            'date'          => 'required',
            'quantity'      => 'required|numeric',
            'price'         => 'required|numeric',
            'unit'          => 'nullable|string',
            'remarks'       => 'nullable|string',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validate->errors()], 422);
        }

        try {
            $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = Carbon::parse($request->date)->toDateString();
        }

        $oldPrice = (float) $order->price;
        $newPrice = (float) $request->price;

        if ($request->hasFile('attachment')) {
            $order->image_url = asset('storage/' . $request->file('attachment')->store('material_attachments', 'public'));
        }

        $order->update([
            'material_type' => $request->material_type,
            'date'          => $date,
            'quantity'      => $request->quantity,
            'price'         => $newPrice,
            'unit'          => $request->unit,
            'updated_by'    => auth('api')->id(),
        ]);

        // Adjust vendor pay detail for price difference
        $priceDiff = $newPrice - $oldPrice;
        if ($priceDiff != 0 && $order->vendor_id) {
            $paydetail = VendorPayDetail::where('vendor_id', $order->vendor_id)->first();
            if ($paydetail) {
                $paydetail->update([
                    'total_unit_price' => $paydetail->total_unit_price + $priceDiff,
                    'balance_amount'   => $paydetail->balance_amount + $priceDiff,
                ]);
            }
        }

        return response()->json([
            'response_code' => 200,
            'status'        => true,
            'message'       => 'Material order updated successfully.',
            'data'          => $order,
        ]);
    }

    public function destroyMaterial($id)
    {
        $order = MaterialOrder::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Material order not found.'], 404);
        }

        // Reverse vendor pay detail
        if ($order->vendor_id) {
            $paydetail = VendorPayDetail::where('vendor_id', $order->vendor_id)->first();
            if ($paydetail) {
                $paydetail->update([
                    'total_units'      => max(0, $paydetail->total_units - $order->quantity),
                    'total_unit_price' => max(0, $paydetail->total_unit_price - $order->price),
                    'balance_amount'   => max(0, $paydetail->balance_amount - $order->price),
                ]);
            }
        }

        $order->delete();

        return response()->json([
            'response_code' => 200,
            'status'        => true,
            'message'       => 'Material order deleted successfully.',
        ]);
    }

    public function orderPdf($id)
    {
        $order = MaterialOrder::with('vendor')->find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Material order not found.'], 404);
        }

        $pdf = Pdf::loadView('admin.helper.material_order_pdf', compact('order'));
        $pdfPath = 'material_orders/material_order_' . $order->id . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Purchase invoice generated successfully.',
            'data' => [
                'pdf_url' => asset('storage/' . $pdfPath),
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
