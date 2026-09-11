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
use App\Services\FirebaseService;

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
            // Slugs of default/fixed material cards the admin has hidden (via material-type-hide/{slug}),
            // persisted in the settings table so this list survives app restarts/refreshes.
            'hidden_materials' => array_values($hidden),
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
        'category_name'      => $request->category_name ?? null,
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

    if (strtolower((string) $role) === 'supervisor') {
        try {
            $requester = auth('api')->user();
            app(FirebaseService::class)->notifyAdminsOfMaterialRequest($materialRequest, $requester);
        } catch (\Exception $e) {
            \Log::error('FCM failed for new material request submission', [
                'id' => $materialRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

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
        $materialRequest = MaterialRequest::with('site')->findOrFail($id);

        $materialRequest->update([
            'status' => MaterialRequest::STATUS_APPROVED,
            'admin_remark' => null,
            'reviewed_at' => now(),
            'updated_by' => auth('api')->id(),
        ]);

        try {
            app(FirebaseService::class)->notifyMaterialRequestDecision($materialRequest, 'approved');
        } catch (\Exception $e) {
            \Log::error('FCM failed for material request approval', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
        }

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

        $materialRequest = MaterialRequest::with('site')->findOrFail($id);

        $materialRequest->update([
            'status' => MaterialRequest::STATUS_REJECTED,
            'admin_remark' => $request->admin_remark,
            'reviewed_at' => now(),
            'updated_by' => auth('api')->id(),
        ]);

        try {
            app(FirebaseService::class)->notifyMaterialRequestDecision($materialRequest, 'rejected', $request->admin_remark);
        } catch (\Exception $e) {
            \Log::error('FCM failed for material request rejection', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
        }

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
        'category_name' => $request->category_name ?? null,
        'spec' => $request->spec ?? null,
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
public function materialPayment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'vendor_id' => 'required|exists:vendors,id',
            'material_type' => 'required|string',
            'quantity' => 'required',
            'date'  => 'required',
            'total_amount' => 'required|numeric',
            'settled_amount' => 'required|numeric',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors(),
            ], 422);
        }

        $totalAmount = (float) $request->total_amount;
        $settledAmount = (float) $request->settled_amount;
        $pendingAmount = max($totalAmount - $settledAmount, 0);

        $materialPayment = MaterialPayment::create([
            'site_id' => $request->site_id,
            'vendor_id' => $request->vendor_id,
            'material_type' => $request->material_type,
            'date' => $request->date,
            'quantity' => $request->quantity,
            'total_amount' => $totalAmount,
            'settled_amount' => $settledAmount,
            'pending_amount' => $pendingAmount,
            'remarks' => $request->remarks,
            'created_by'  => auth('api')->id(),
        ]);

        Storage::makeDirectory('public/whatsapp_pdfs');
        $pdf = Pdf::loadView('admin.helper.pdf_request', [
            'request' => $materialPayment,
            'vendor_name' => $request->vendor_name,
        ]);
        $filename = 'material_request_' . \Illuminate\Support\Str::slug($request->vendor_name ?? 'vendor') . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::put("public/whatsapp_pdfs/$filename", $pdf->output());
        $publicLink = asset("storage/whatsapp_pdfs/$filename");

        $whatsappUrl = $request->vendor_mobile
            ? "https://wa.me/{$request->vendor_mobile}?text=" . urlencode("\n$publicLink\n\n")
            : null;

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Material payment recorded successfully.',
            'data' => $materialPayment,
            'whatsapp_url' => $whatsappUrl,
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
                'name' => 'Bricks & Blocks',
                'category_name' => ['Red Clay Brick', 'Fly Ash Bricks', 'AAC Blocks', 'Solid Concrete Blocks', 'Hollow Concrete Blocks', 'Wire Cut Bricks', 'Clay Paver Bricks', 'Stone'],
                'spec_label' => 'Size / Specification',
                'spec_options' => ['9" x 4" x 3"', '4" AAC Block', '6" AAC Block', '8" AAC Block', '4" Solid Block', '6" Solid Block', '9" Solid Block'],
                'unit' => ['Nos', 'Pieces', 'Load', 'Thousand'],
                'attachment' => false,
            ],
            [
                'id' => 2,
                'material_type' => 'sand',
                'name' => 'Sand',
                'category_name' => ['River Sand', 'M-Sand (Manufactured Sand - Concreting)', 'P-Sand (Plastering Sand)', 'Filling Sand / Pit Sand'],
                'spec_label' => 'Zone / Source',
                'spec_options' => ['Zone II Concreting Sand', 'Double Washed M-Sand', 'Fine Plastering P-Sand', 'Pit Sand / Earth Filling'],
                'unit' => ['CFT', 'Ton', 'Brass', 'Units', 'Load'],
                'attachment' => false,
            ],
            [
                'id' => 3,
                'material_type' => 'cement',
                'name' => 'Cement',
                'category_name' => ['OPC 53 Grade', 'OPC 43 Grade', 'PPC (Portland Pozzolana)', 'PSC (Portland Slag Cement)', 'White Cement', 'Waterproof Cement'],
                'spec_label' => 'Brand / Manufacturer',
                'spec_options' => ['UltraTech', 'ACC', 'Ambuja', 'Dalmia', 'Ramco', 'Chettinad', 'Birla A1', 'Priya Cement', 'Maha Cement', 'Zuari', 'Coromandel'],
                'unit' => ['Bags', 'Ton', 'Kg'],
                'attachment' => false,
            ],
            [
                'id' => 4,
                'material_type' => 'aggregate',
                'name' => 'Aggregate',
                'category_name' => ['6mm Aggregate', '10mm Aggregate', '12mm Aggregate', '20mm Aggregate', '40mm Aggregate', 'GSB (Granular Sub Base)', 'WMM (Wet Mix Macadam)', 'Quarry Dust / Stone Dust'],
                'spec_label' => 'Stone Type / Spec',
                'spec_options' => ['Blue Metal Crushed Stone', 'Hard Granite Aggregate', 'Washed Aggregate', 'Quarry Dust'],
                'unit' => ['CFT', 'Ton', 'Brass', 'Units', 'Load'],
                'attachment' => false,
            ],
            [
                'id' => 5,
                'material_type' => 'jally',
                'name' => 'Jally',
                'category_name' => ['6mm Jally', '12mm Jally', '20mm Jally', '40mm Jally', 'Stone Dust'],
                'spec_label' => 'Type',
                'spec_options' => ['Blue Metal Jally', 'Hand Broken Jally', 'Crusher Run'],
                'unit' => ['CFT', 'Ton', 'Brass', 'Units', 'Load'],
                'attachment' => false,
            ],
            [
                'id' => 6,
                'material_type' => 'gravel',
                'name' => 'Gravel & Earth',
                'category_name' => ['Gravel', 'Red Earth / Soil', 'Moorum', 'Quarry Dust', 'Filling Soil'],
                'spec_label' => 'Application',
                'spec_options' => ['Foundation Filling', 'Plinth Filling', 'Road Base Compaction', 'Landscaping'],
                'unit' => ['CFT', 'Ton', 'Brass', 'Load', 'Trips'],
                'attachment' => false,
            ],
            [
                'id' => 7,
                'material_type' => 'steel',
                'name' => 'Steel & TMT',
                'category_name' => ['TMT Steel Bars', 'Binding Wire', 'Mild Steel (MS) Rods', 'GI Wire', 'Steel Angles', 'Steel Channels', 'Steel Plates', 'MS Pipes', 'GI Pipes', 'Stainless Steel (SS)'],
                'spec_label' => 'Diameter / Grade',
                'spec_options' => ['8mm TMT Bar', '10mm TMT Bar', '12mm TMT Bar', '16mm TMT Bar', '20mm TMT Bar', '25mm TMT Bar', '32mm TMT Bar', 'Fe 500D', 'Fe 550D'],
                'unit' => ['Ton', 'Tons', 'Kg', 'Bundles', 'Nos'],
                'attachment' => false,
            ],
            [
                'id' => 8,
                'material_type' => 'rmc',
                'name' => 'Ready-Mix Concrete (RMC)',
                'category_name' => ['M10', 'M15', 'M20', 'M25', 'M30', 'M35', 'M40', 'M45', 'M50', 'Ready-Mix Concrete (RMC)', 'Precast Concrete'],
                'spec_label' => 'Pour Structure / Slump',
                'spec_options' => ['Footing / Raft Foundation', 'Columns', 'Plinth Beams', 'Roof Slab & Beams', 'Retaining Wall'],
                'unit' => ['M Cube', 'Load'],
                'attachment' => false,
            ],
            [
                'id' => 9,
                'material_type' => 'rcconcrete',
                'name' => 'RC Concrete',
                'category_name' => ['M15', 'M20', 'M25', 'M30', 'M35'],
                'spec_label' => 'Structure Element',
                'spec_options' => ['Columns', 'Beams', 'Roof Slab', 'Lintel & Sunshade', 'Foundation'],
                'unit' => ['M Cube', 'CFT'],
                'attachment' => false,
            ],
            [
                'id' => 10,
                'material_type' => 'woodcarpentry',
                'name' => 'Wood & Carpentry',
                'category_name' => ['Plywood (Commercial MR)', 'Waterproof Plywood (BWP/BWR)', 'Marine Plywood', 'MDF Board', 'HDF Board', 'Particle Board', 'Teak Wood', 'Sal Wood', 'Neem Wood', 'Pine Wood', 'Wooden Frames', 'Wooden Doors', 'Laminates (Mica)', 'Veneers'],
                'spec_label' => 'Thickness / Size',
                'spec_options' => ['6mm (8x4 ft)', '8mm (8x4 ft)', '12mm (8x4 ft)', '16mm (8x4 ft)', '18mm (8x4 ft)', '1mm Laminate'],
                'unit' => ['Sqft', 'CFT', 'Sheets', 'Rft', 'Nos'],
                'attachment' => true,
            ],
            [
                'id' => 11,
                'material_type' => 'doorswindows',
                'name' => 'Doors & Windows',
                'category_name' => ['Main Entrance Wooden Door', 'Bedroom Flush Door', 'UPVC Sliding Windows', 'UPVC Casement Windows', 'Aluminium Sliding Windows', 'Aluminium Doors', 'Glass Partition', 'Toughened Glass', 'Door Frames', 'Window Frames', 'Mosquito Mesh Doors', 'Bathroom PVC Doors'],
                'spec_label' => 'Dimension / Spec',
                'spec_options' => ['7\' x 3\'6"', '7\' x 3\'', '4\' x 4\'', '5\' x 4\'', '6\' x 4\'', '5mm Glass', '8mm Toughened Glass'],
                'unit' => ['Nos', 'Sets', 'Sqft'],
                'attachment' => true,
            ],
            [
                'id' => 12,
                'material_type' => 'tiles',
                'name' => 'Tiles',
                'category_name' => ['Vitrified Floor Tiles', 'Ceramic Wall Tiles', 'Anti-skid Bathroom Tiles', 'Parking Paver Tiles', 'Kitchen Glazed Tiles', 'Subway / Decorative Tiles', 'Step & Riser Tiles', 'Interlocking Paver Blocks'],
                'spec_label' => 'Size / Finish',
                'spec_options' => ['2x2 ft (600x600mm)', '4x2 ft (1200x600mm)', '2x1 ft (600x300mm)', '1x1 ft (300x300mm)', '800x1600mm', 'Glossy Finish', 'Matt Finish'],
                'unit' => ['Boxes', 'Sqft', 'Pieces'],
                'attachment' => true,
            ],
            [
                'id' => 13,
                'material_type' => 'granite',
                'name' => 'Granite & Marble',
                'category_name' => ['Granite', 'Black Galaxy Granite', 'Jet Black Granite', 'Steel Grey Granite', 'Tan Brown Granite', 'Indian White Marble', 'Italian Marble', 'Kota Stone', 'Kadappa Stone'],
                'spec_label' => 'Thickness / Application',
                'spec_options' => ['18mm Polished Slab', '20mm Polished Slab', 'Kitchen Platform Slab', 'Staircase Treads & Risers'],
                'unit' => ['Sqft', 'Slabs', 'Rft'],
                'attachment' => true,
            ],
            [
                'id' => 14,
                'material_type' => 'painting',
                'name' => 'Painting & Wall Finishes',
                'category_name' => ['Wall Putty', 'Interior Primer', 'Exterior Primer', 'Interior Acrylic Emulsion', 'Exterior Weatherproof Emulsion', 'Enamel Paint (Oil Based)', 'PU / Wood Polish', 'Waterproof Paint', 'Texture Paint', 'Thinner'],
                'spec_label' => 'Brand / Shade',
                'spec_options' => ['Asian Paints Royale', 'Asian Paints Apex Ultima', 'Asian Paints Tractor', 'Berger WeatherCoat', 'Nerolac Beauty', 'Birla White Putty'],
                'unit' => ['Litres', 'Ltr', 'Kg', 'Pack', 'Buckets', 'Tins'],
                'attachment' => true,
            ],
            [
                'id' => 15,
                'material_type' => 'plumber',
                'name' => 'Plumbing & Pipes',
                'category_name' => ['CPVC Pipes', 'CPVC Fittings (Elbow/Tee/Coupler)', 'UPVC Pipes', 'UPVC Fittings', 'PVC SWR Drainage Pipes', 'HDPE Pipes', 'GI Pipes', 'Ball Valves / Concealed Valves', 'Overhead Water Tanks', 'Wash Basins', 'Toilets (EWC/IWC)', 'Faucets / Taps'],
                'spec_label' => 'Diameter / Size',
                'spec_options' => ['1/2" (15mm)', '3/4" (20mm)', '1" (25mm)', '1.25" (32mm)', '1.5" (40mm)', '2" (50mm)', '3" (75mm)', '4" (110mm)', '500 Litre Tank', '1000 Litre Tank'],
                'unit' => ['Nos', 'Meter', 'Bundles', 'Pieces', 'Sets'],
                'attachment' => true,
            ],
            [
                'id' => 16,
                'material_type' => 'electricalwire',
                'name' => 'Electrical & Wiring',
                'category_name' => ['House Wires (FR/FRLS)', 'Armoured Power Cables', 'PVC Conduits & Accessories', 'Modular Switches & Sockets', 'Distribution Boards (DB)', 'Miniature Circuit Breakers (MCB)', 'RCCB / ELCB', 'LED Ceiling Lights', 'Ceiling Fans', 'Exhaust Fans', 'Junction Boxes'],
                'spec_label' => 'Spec / Gauge / Rating',
                'spec_options' => ['0.75 sq mm Wire', '1.0 sq mm Wire', '1.5 sq mm Wire', '2.5 sq mm Wire', '4.0 sq mm Wire', '6.0 sq mm Wire', '10 sq mm Wire', '6A Switch', '16A Socket', '32A DP MCB'],
                'unit' => ['Meter', 'Nos', 'Roll', 'Coil', 'Pack', 'Boxes', 'Sets'],
                'attachment' => true,
            ],
            [
                'id' => 17,
                'material_type' => 'hardware',
                'name' => 'Hardware & Fasteners',
                'category_name' => ['Drywall Screws', 'Wood Screws', 'Self-Tapping Screws', 'Wire Nails', 'Concrete Nails', 'Nuts & Bolts', 'Washers', 'Anchor Fasteners / Rawlplugs', 'Tower Bolts', 'Mortise Locks & Handles', 'Hinges (Butt/Concealed)', 'Drawer Slides', 'Brackets & Clamps'],
                'spec_label' => 'Size / Spec',
                'spec_options' => ['1 inch', '1.5 inch', '2 inch', '2.5 inch', '3 inch', '4 inch', '5 inch', '6 inch', 'SS 304 Grade', 'MS Zinc Plated'],
                'unit' => ['Pack', 'Boxes', 'Nos', 'Kg', 'Pairs', 'Pieces'],
                'attachment' => false,
            ],
            [
                'id' => 18,
                'material_type' => 'waterproofinginsulation',
                'name' => 'Waterproofing & Insulation',
                'category_name' => ['Integral Liquid Waterproofing (LW+)', '2K Acrylic Polymer Coating', 'Bitumen Sheet / Membrane', 'APP Membrane', 'Bituminous Primer', 'PVC Waterproofing Sheet', 'Tile Adhesive', 'Epoxy Tile Grout', 'Silicone Sealant', 'Concrete Curing Compound'],
                'spec_label' => 'Brand / Spec',
                'spec_options' => ['Dr. Fixit 101 LW+', 'Dr. Fixit Fastflex', 'Dr. Fixit Pidifin 2K', 'Fosroc Nitoproof', 'SikaTop Seal 107', 'Roff Tile Adhesive'],
                'unit' => ['Litres', 'Kg', 'Pack', 'Bags', 'Roll', 'Buckets'],
                'attachment' => true,
            ],
            [
                'id' => 19,
                'material_type' => 'roofing',
                'name' => 'Roofing Materials',
                'category_name' => ['Colour-Coated Galvalume Sheets', 'GI Corrugated Sheets', 'Polycarbonate Multiwall Sheets', 'Polycarbonate Corrugated Sheets', 'Mangalore Clay Roof Tiles', 'UPVC Roofing Sheets', 'Bitumen Roofing Sheets', 'Fibre Cement Sheets', 'Ridge Caps', 'Gutter & Downpipes'],
                'spec_label' => 'Thickness / Length',
                'spec_options' => ['0.35mm', '0.40mm', '0.45mm', '0.50mm', '8 Feet Length', '10 Feet Length', '12 Feet Length', '14 Feet Length', '16 Feet Length'],
                'unit' => ['Rft', 'Meter', 'Sheets', 'Sqft', 'Nos'],
                'attachment' => true,
            ],
            [
                'id' => 20,
                'material_type' => 'finishingmaterials',
                'name' => 'Finishing Materials & False Ceiling',
                'category_name' => ['Wall Putty', 'Gypsum Plaster (One Coat)', 'Gypsum Ceiling Boards', 'Cement Fibre Boards', 'GI False Ceiling Perimeter Channel', 'GI Ceiling Section', 'POP (Plaster of Paris)', 'PVC Wall Panels', 'Acoustic Ceiling Tiles'],
                'spec_label' => 'Brand / Thickness',
                'spec_options' => ['Saint-Gobain Gyproc', 'USG Boral', 'Armstrong', 'Birla White', '12.5mm Gypsum Board', '9.5mm Gypsum Board'],
                'unit' => ['Bags', 'Sheets', 'Bundles', 'Sqft', 'Nos'],
                'attachment' => true,
            ],
            [
                'id' => 21,
                'material_type' => 'externaloutdoor',
                'name' => 'External & Outdoor Infrastructure',
                'category_name' => ['Interlocking Paver Blocks', 'Kerb Stones', 'Precast Compound Wall Slabs & Posts', 'Chain Link Fencing', 'Barbed Wire', 'RCC Hume Drainage Pipes', 'Manhole Covers & Frames', 'Landscaping Stones'],
                'spec_label' => 'Grade / Dimension',
                'spec_options' => ['60mm Paver (M30)', '80mm Heavy Paver (M40)', '100mm Kerb Stone', '150mm Kerb Stone', '300mm Hume Pipe (NP2)', '450mm Hume Pipe (NP2)'],
                'unit' => ['Sqft', 'Nos', 'Rft', 'Meter', 'Pairs'],
                'attachment' => false,
            ],
            [
                'id' => 22,
                'material_type' => 'scaffoldingformwork',
                'name' => 'Scaffolding & Formwork',
                'category_name' => ['MS Adjustable Props', 'Cuplock Verticals', 'Cuplock Ledgers', 'Film-Faced Shuttering Plywood', 'MS Shuttering Plates', 'Tie Rods & Wing Nuts', 'Scaffolding Couplers/Clamps', 'Adjustable Base Jacks', 'U-Head Jacks', 'Scaffolding Pipes (40mm)'],
                'spec_label' => 'Size / Spec',
                'spec_options' => ['2m x 3m Adjustable Props', '2m x 3.5m Adjustable Props', '12mm Film-Faced Plywood (8x4 ft)', '900 x 600mm MS Plates', '1200 x 600mm MS Plates'],
                'unit' => ['Nos', 'Sheets', 'Sets', 'Sqft', 'Ton'],
                'attachment' => true,
            ],
            [
                'id' => 23,
                'material_type' => 'safetyppe',
                'name' => 'Safety & PPE',
                'category_name' => ['Safety Helmets (ISI Mark)', 'Safety Shoes (Steel Toe)', 'High-Visibility Reflective Jackets', 'Full Body Safety Harness', 'Safety Fall Protection Nets', 'Heavy Duty Gloves', 'Safety Protective Goggles', 'Caution / Barricade Tape'],
                'spec_label' => 'Rating / Spec',
                'spec_options' => ['ISI Marked HDPE Shell', 'Class A / Class B', 'Steel Toe Cap (Size 7-11)', 'Double Lanyard Safety Harness', '50mm Retroreflective Tape'],
                'unit' => ['Nos', 'Pairs', 'Roll', 'Sets'],
                'attachment' => false,
            ],
            [
                'id' => 24,
                'material_type' => 'sanitarybathfittings',
                'name' => 'Sanitaryware & Bath Fittings',
                'category_name' => ['European Water Closet (EWC)', 'Indian Water Closet (IWC)', 'Wall Hung Toilet with Concealed Cistern', 'Countertop Wash Basin', 'Pedestal Wash Basin', 'Urinals', 'Health Faucets', 'Basin Mixers', 'Wall Mixers / Diverters', 'Overhead Showers', 'CP Bath Fittings'],
                'spec_label' => 'Brand / Model',
                'spec_options' => ['Jaquar', 'Parryware', 'Hindware', 'Cera', 'Kohler', 'Grohe', 'White Ceramic', 'Chrome Finish'],
                'unit' => ['Nos', 'Sets', 'Pieces'],
                'attachment' => true,
            ],
            [
                'id' => 25,
                'material_type' => 'glassaluminium',
                'name' => 'Glass & Aluminium',
                'category_name' => ['Toughened Glass', 'Laminated Safety Glass', 'Clear Float Glass', 'Frosted / Tinted Glass', 'Aluminium Partition Sections', 'Aluminium Window Sections', 'Structural Glazing Profiles', 'Spider Glazing Fittings', 'Silicone Weather Sealants'],
                'spec_label' => 'Thickness / Profile',
                'spec_options' => ['5mm Clear Glass', '6mm Toughened Glass', '8mm Toughened Glass', '10mm Toughened Glass', '12mm Toughened Glass', '63.5 x 38.1mm Alu Section'],
                'unit' => ['Sqft', 'Sheets', 'Nos', 'Rft'],
                'attachment' => true,
            ],
            [
                'id' => 26,
                'material_type' => 'welding',
                'name' => 'Welding & Fabrication',
                'category_name' => ['Welding Electrodes / Rods', 'MS Rods', 'MS Angles', 'MS Channels', 'MS Flats', 'MS Square Tubes', 'MS Round Pipes', 'Cutting Wheels', 'Grinding Wheels'],
                'spec_label' => 'Size / Gauge',
                'spec_options' => ['8 SWG Electrode', '10 SWG Electrode', '12 SWG Electrode', '25x25x3 mm Angle', '40x40x5 mm Angle', '50x50x6 mm Angle'],
                'unit' => ['Kg', 'Pack', 'Nos', 'Ton', 'Pieces'],
                'attachment' => false,
            ],
            [
                'id' => 27,
                'material_type' => 'lift',
                'name' => 'Lift / Elevator',
                'category_name' => ['Passenger Lift (6 Passengers)', 'Passenger Lift (8 Passengers)', 'Goods Lift / Material Hoist', 'Hydraulic Home Lift', 'Capsule Glass Lift', 'Dumbwaiter Lift'],
                'spec_label' => 'Stops / Capacity',
                'spec_options' => ['G+1 (2 Stops)', 'G+2 (3 Stops)', 'G+3 (4 Stops)', 'G+4 (5 Stops)', 'G+5 (6 Stops)', '408 Kg Capacity', '544 Kg Capacity'],
                'unit' => ['Nos', 'Sets'],
                'attachment' => true,
            ],
            [
                'id' => 28,
                'material_type' => 'transport',
                'name' => 'Transport & Machinery Rental',
                'category_name' => ['Tipper / Lorry (6 Wheeler)', 'Tipper / Lorry (10 Wheeler)', 'JCB Excavator (3DX)', 'Hitachi / Poclain Heavy Excavator', 'Bobcat Compact Loader', 'Concrete Boom Pump', 'Tractor with Trolley', 'Hydra Mobile Crane', 'Road Roller'],
                'spec_label' => 'Billing Basis / Shift',
                'spec_options' => ['Per Hour (with Diesel & Driver)', 'Per Day (8 Hours)', 'Per Trip / Load', 'Per Month Contract'],
                'unit' => ['Load', 'Unit', 'Hours', 'Days', 'Trips'],
                'attachment' => false,
            ],
            [
                'id' => 29,
                'material_type' => 'interior',
                'name' => 'Interior Works',
                'category_name' => ['Modular Kitchen Cabinets', 'Bedroom Wardrobes', 'TV Unit & Wall Panelling', 'False Ceiling Design', 'Glass Partitions', 'Loose Furniture & Bed Units', 'Wall Paper & Texture Finish'],
                'spec_label' => 'Finish / Material Spec',
                'spec_options' => ['High-Gloss Acrylic Finish', 'Anti-Fingerprint Laminate', 'PU Polish Finish', 'Natural Wood Veneer'],
                'unit' => ['Sqft', 'Rft', 'Sets', 'Ls'],
                'attachment' => true,
            ],
            [
                'id' => 30,
                'material_type' => 'watercan',
                'name' => 'Drinking Watercan',
                'category_name' => ['20 Litre Can', '25 Litre Can'],
                'spec_label' => 'Type',
                'spec_options' => ['Commercial RO Purified Water', 'Packaged Drinking Water'],
                'unit' => ['Nos', 'Pack', 'Units'],
                'attachment' => false,
            ],
            [
                'id' => 31,
                'material_type' => 'lorrywater',
                'name' => 'Lorry Water (Tanker)',
                'category_name' => ['6,000 Litre Tanker', '8,000 Litre Tanker', '12,000 Litre Tanker', '18,000 Litre Tanker'],
                'spec_label' => 'Water Purpose',
                'spec_options' => ['Construction Curing & Concrete Mixing Water', 'Ground Raw Water', 'Drinking Supply'],
                'unit' => ['Load', 'Litres', 'Unit'],
                'attachment' => false,
            ],
            [
                'id' => 32,
                'material_type' => 'tea',
                'name' => 'Tea & Site Refreshments',
                'category_name' => ['Morning Tea / Coffee', 'Afternoon Tea / Coffee', 'Snacks & Biscuits', 'Drinking Water Supply'],
                'spec_label' => 'Supply Schedule',
                'spec_options' => ['Daily Site Supply', 'Weekly Supply', 'Overtime Snacks'],
                'unit' => ['Cups', 'Nos', 'Pack', 'Days'],
                'attachment' => false,
            ],
            [
                'id' => 33,
                'material_type' => 'default',
                'name' => 'Other / Custom Material',
                'category_name' => ['General Construction Material', 'Tools & Consumables', 'Site Utilities'],
                'spec_label' => 'Specification',
                'spec_options' => [],
                'unit' => ['Units', 'Kg', 'Ton', 'Size', 'Inch', 'Dia', 'Nos', 'Pieces', 'Load', 'Pack', 'Sets'],
                'attachment' => false,
            ],
        ];

        return response()->json([
            'status' => 'true',
            'data' => $materials
        ]);
    }

}
