<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialOrder;
use App\Models\MaterialPayment;
use App\Models\MaterialRequest;
use App\Models\Site;
use App\Models\VendorPayDetail;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User;


class MaterialController extends Controller
{
    //Material management
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

        $totalUnitsAll = $site->materialOrders->sum('quantity');
        $totalValuesAll = $site->materialOrders->sum('price');

        return view('admin.menus.material.material_management', compact('site', 'materials', 'totalUnitsAll', 'totalValuesAll'));
    }

    // All Materials unified form (Inward Order & Material Request)
    public function allMaterialsForm(Request $request, $siteId)
    {
        $site = Site::findOrFail($siteId);
        $supervisor = User::find($site->supervisor_id);
        $initialTab = $request->query('tab', 'order');
        $selectedMaterial = $request->query('material', '');

        $builtinMaterials = [
            'bricks' => 'Bricks',
            'sand' => 'Sand',
            'cement' => 'Cement',
            'steel' => 'Steel',
            'electricalwire' => 'Electrical Wires',
            'plumber' => 'Plumber',
            'painting' => 'Painting',
            'rmc' => 'RMC / Concrete',
            'aggregate' => 'Aggregate',
            'tea' => 'Tea',
            'watercan' => 'Watercan',
            'lorrywater' => 'Lorry Water',
            'tiles' => 'Tiles',
            'granite' => 'Granite',
            'jally' => 'Jally',
            'welding' => 'Welding',
            'lift' => 'Lift',
            'rcconcrete' => 'RC Concrete',
            'transport' => 'Transport',
            'interior' => 'Interior',
            'solidblock' => 'Solid Block',
            'readymix' => 'Readymix',
            'earthwork' => 'Earth Work',
            'upvc' => 'Upvc',
            'hardware' => 'Hardware',
            'wood' => 'Wood',
            'glass' => 'Glass',
            'pvcpipes' => 'Pvc Pipes',
        ];

        return view('admin.menus.material.all_materials_form', compact(
            'site',
            'siteId',
            'supervisor',
            'initialTab',
            'selectedMaterial',
            'builtinMaterials'
        ));
    }


    // Material details
    public function index(Request $request, $siteId, $materialType)
    {
        $month = $request->query('month') ?: Carbon::now()->format('Y-m');
        $week = (int) $request->query('week');

        $startOfMonth = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $startDate = $startOfMonth->copy();
        $endDate = $endOfMonth->copy();

        if ($week >= 1 && $week <= 4) {
            $daysInMonth = $startOfMonth->daysInMonth;
            $weekLength = ceil($daysInMonth / 4);

            $startDate = $startOfMonth->copy()->addDays(($week - 1) * $weekLength);
            $endDate = $startDate->copy()->addDays($weekLength - 1);

            if ($endDate->gt($endOfMonth)) {
                $endDate = $endOfMonth;
            }
        } else {
            $week = null;
        }

        $query = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if (strtolower($materialType) !== 'all') {
            $query->where('material_type', $materialType);
        }

        $materials = $query->get()
            ->map(function ($material) {
                $material->date = $material->date ? Carbon::parse($material->date)->format('d-m-Y') : null;
                $material->created_at = $material->created_at ? $material->created_at->format('d-m-Y') : null;
                $material->updated_at = $material->updated_at ? $material->updated_at->format('d-m-Y') : null;

                return $material;
            });

        $site = Site::find($siteId);
        $siteName = $site ? $site->site_name : 'Unknown Site';

        $unitQuery = MaterialOrder::where('site_id', $siteId)->whereBetween('date', [$startDate, $endDate]);
        $amountQuery = MaterialOrder::where('site_id', $siteId)->whereBetween('date', [$startDate, $endDate]);
        $settledQuery = MaterialPayment::where('site_id', $siteId)->whereBetween('date', [$startDate, $endDate]);
        $pendingQuery = MaterialPayment::where('site_id', $siteId)->whereBetween('date', [$startDate, $endDate]);

        if (strtolower($materialType) !== 'all') {
            $unitQuery->where('material_type', $materialType);
            $amountQuery->where('material_type', $materialType);
            $settledQuery->where('material_type', $materialType);
            $pendingQuery->where('material_type', $materialType);
        }

        $totalUnits = $unitQuery->sum('quantity');
        $totalAmount = $amountQuery->sum('price');
        $settledAmount = $settledQuery->sum('settled_amount');
        $pendingAmount = $pendingQuery->sum('pending_amount');

        $orderNos = $materials->pluck('order_no')->filter()->unique();
        $orderItemCounts = $orderNos->isNotEmpty()
            ? MaterialOrder::whereIn('order_no', $orderNos)
                ->groupBy('order_no')
                ->selectRaw('order_no, count(*) as count')
                ->pluck('count', 'order_no')
                ->toArray()
            : [];

        return view('admin.menus.material.material_details', compact(
            'materials',
            'siteId',
            'siteName',
            'totalAmount',
            'settledAmount',
            'pendingAmount',
            'totalUnits',
            'materialType',
            'month',
            'week',
            'orderItemCounts'
        ));
    }

    // Material details
    public function getMaterialData(Request $request, $siteId)
    {
        $monthYear = $request->input('monthYear'); // format: YYYY-MM
        $week = (int) $request->input('week'); // 1, 2, 3, 4
        $materialType = $request->input('material_type');

        $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Default to full month
        $startDate = $startOfMonth->copy();
        $endDate = $endOfMonth->copy();

        // If specific week selected (1 to 4)
        if ($week >= 1 && $week <= 4) {
            $daysInMonth = $startOfMonth->daysInMonth;
            $weekLength = ceil($daysInMonth / 4); // usually 7 or 8 days

            $startDate = $startOfMonth->copy()->addDays(($week - 1) * $weekLength);
            $endDate = $startDate->copy()->addDays($weekLength - 1);

            // Limit to end of month
            if ($endDate->gt($endOfMonth)) {
                $endDate = $endOfMonth;
            }
        }

        $query = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if (strtolower($materialType) !== 'all') {
            $query->where('material_type', $materialType);
        }

        $materials = $query->get()
            ->map(function ($material) {
                $material->date = $material->date ? Carbon::parse($material->date)->format('d-m-Y') : null;
                $material->created_at = $material->created_at ? $material->created_at->format('d-m-Y') : null;
                $material->updated_at = $material->updated_at ? $material->updated_at->format('d-m-Y') : null;

                return $material;
            });

        $totalUnits = $materials->sum('quantity');
        $totalAmount = $materials->sum('price');

        $settledQuery = MaterialPayment::where('site_id', $siteId)->whereBetween('date', [$startDate, $endDate]);
        $pendingQuery = MaterialPayment::where('site_id', $siteId)->whereBetween('date', [$startDate, $endDate]);

        if (strtolower($materialType) !== 'all') {
            $settledQuery->where('material_type', $materialType);
            $pendingQuery->where('material_type', $materialType);
        }

        $settledAmount = $settledQuery->sum('settled_amount');
        $pendingAmount = $pendingQuery->sum('pending_amount');

        return response()->json([
            'bricks' => $materials,
            'totalUnits' => $totalUnits,
            'totalAmount' => $totalAmount,
            'settledAmount' => $settledAmount,
            'pendingAmount' => $pendingAmount,
        ]);
    }

    // Export material data as CSV (month/week optional via query string)
    public function export(Request $request, $siteId, $materialType)
    {
        $monthYear = $request->query('month') ?: now()->format('Y-m');
        $week = (int) $request->query('week');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if ($fromDate || $toDate) {
            $startDate = $fromDate ? Carbon::parse($fromDate)->startOfDay() : Carbon::create(1900, 1, 1)->startOfDay();
            $endDate = $toDate ? Carbon::parse($toDate)->endOfDay() : now()->endOfDay();
        } else {
            $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $startDate = $startOfMonth->copy();
            $endDate = $endOfMonth->copy();

            if ($week >= 1 && $week <= 4) {
                $daysInMonth = $startOfMonth->daysInMonth;
                $weekLength = ceil($daysInMonth / 4);
                $startDate = $startOfMonth->copy()->addDays(($week - 1) * $weekLength);
                $endDate = $startDate->copy()->addDays($weekLength - 1);
                if ($endDate->gt($endOfMonth)) $endDate = $endOfMonth;
            }
        }

        $query = \App\Models\MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate]);

        if (strtolower($materialType) !== 'all') {
            $query->where('material_type', $materialType);
        }

        $materials = $query->get();

        $filename = sprintf('%s_%s_%s.csv', $materialType, $siteId, now()->format('Ymd_His'));

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['Date', 'Quantity', 'Vendor', 'Price', 'Material Type', 'Invoice Link'];

        $callback = function () use ($materials, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($materials as $m) {
                fputcsv($file, [
                    $m->date ? Carbon::parse($m->date)->format('d-m-Y') : '',
                    $m->quantity,
                    optional($m->vendor)->name,
                    $m->price,
                    $m->material_type,
                    $m->image_url ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Material get request form
    public function getRequestForm($siteId, $materialType)
    {
        if (strtolower($materialType) === 'all') {
            return redirect()->route('material.allForm', ['siteId' => $siteId]);
        }
        $site = Site::findOrFail($siteId);
        $supervisor = User::find($site->supervisor_id);
        return view('admin.menus.material.add_request', compact('siteId', 'materialType', 'supervisor'));
    }

public function materialRequest(Request $request)
{
    $rules = [
        'site_id'             => 'required|exists:sites,id',
        'vendor_name'         => 'required|string',
        'vendor_mobile'       => 'required|numeric|digits:10',
        'vendor_address'      => 'required|string',
        'items'               => 'nullable|string',
        'price'               => 'nullable|string',
        'amount'              => 'nullable|string',
        'quantity'            => 'nullable',
        'date_of_delivery'    => 'required',
        'supervisor_name'     => 'required',
        'supervisor_phone'    => 'required|numeric|digits:10',
        'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ];

    if (!$request->has('items_data') || empty($request->items_data)) {
        $rules['items'] = 'required|string';
        $rules['quantity'] = 'required';
    }

    $validate = Validator::make($request->all(), $rules);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors()
        ], 422);
    }

    // ✅ File upload
    $imageUrl = null;
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $path = $file->store('material_attachments', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    $price = $request->filled('price') ? $request->price : ($request->filled('amount') ? $request->amount : null);

    $itemsText = $request->items;
    $quantityText = $request->quantity;
    $itemsList = [];

    if ($request->has('items_data') && is_array($request->items_data) && count($request->items_data) > 0) {
        $descParts = [];
        $totalQty = 0;
        foreach ($request->items_data as $subName => $data) {
            if (!is_array($data)) continue;
            $name = $data['name'] ?? (is_string($subName) ? $subName : '');
            if (!empty($data['quantity']) && !empty($name)) {
                $qty = $data['quantity'];
                $u = $data['unit'] ?? '';
                $spec = $data['specification'] ?? '';
                $p = $data['price'] ?? '';
                $itemLine = "• {$name}" . ($spec ? " ({$spec})" : '') . ": {$qty} " . ($u ?: 'Units') . ($p ? " (₹{$p})" : '');
                $itemsList[] = $itemLine;
                $descParts[] = "{$name}" . ($spec ? " ({$spec})" : '') . " [{$qty} " . ($u ?: 'Units') . "]";
                $totalQty += (float) $qty;
            }
        }
        if (!empty($descParts)) {
            $itemsText = implode(', ', $descParts);
            $quantityText = $totalQty > 0 ? (string)$totalQty : ($request->quantity ?? '1');
        }
    }

    // ✅ Save only selected fields to DB
    $materialRequest = MaterialRequest::create([
        'site_id'             => $request->site_id,
        'quantity'            => $quantityText ?? $request->quantity,
        'unit'                => $request->unit ?? null,
        'image_url'           => $imageUrl,
        'date_of_delivery'    => $request->date_of_delivery,
        'price'               => $price,
        'items'               => $itemsText,
        'created_by'          => auth('admin')->id(),
        'source'              => MaterialRequest::SOURCE_ADMIN,
    ]);

    // ✅ WhatsApp message
    $site = Site::find($request->site_id);

    $itemsSummaryText = !empty($itemsList) ? "\n" . implode("\n", $itemsList) : "\nItems: {$itemsText}";

    $message = "*Pojo Infra360*\n"
        . "Site: {$site->site_name} - Material Request\n"
        . "Location: {$site->location}\n"
        . "Vendor Name: {$request->vendor_name}\n"
        . "Vendor Mobile Number: {$request->vendor_mobile}\n"
        . (!empty($request->material_type) ? "Material Type: {$request->material_type}\n" : "")
        . "Requested Items:{$itemsSummaryText}\n"
        . "Delivery Date: {$request->date_of_delivery}\n"
        . "Supervisor: {$request->supervisor_name} ({$request->supervisor_phone})\n"
        . (!empty($price) ? "Estimated Total: ₹{$price}\n" : "")
        . (!empty($imageUrl) ? "Image: {$imageUrl}\n" : "");

    $whatsappUrl = "https://wa.me/{$request->vendor_mobile}?text=" . urlencode($message);

    return response()->json([
        'status' => 'success',
        'message' => 'Request sent successfully.',
        'whatsapp_url' => $whatsappUrl
    ]);
}

    // View all material requests submitted for a site (supervisor mobile submissions included)
    public function requestList($siteId)
    {
        $site = Site::findOrFail($siteId);

        $requests = MaterialRequest::with('vendor')
            ->where('site_id', $siteId)
            ->where('source', MaterialRequest::SOURCE_SUPERVISOR)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.menus.material.request_list', compact('site', 'requests'));
    }

    // Approve or reject a supervisor-submitted material request
    public function updateRequestStatus(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'admin_remark' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $materialRequest = MaterialRequest::findOrFail($id);

        $materialRequest->update([
            'status' => $request->status === 'approved'
                ? MaterialRequest::STATUS_APPROVED
                : MaterialRequest::STATUS_REJECTED,
            'admin_remark' => $request->status === 'rejected' ? $request->admin_remark : null,
            'reviewed_at' => now(),
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Request ' . $request->status . ' successfully.');
    }

    // Material get order form
    public function getOrderForm($siteId, $materialType)
    {
        if (strtolower($materialType) === 'all') {
            return redirect()->route('material.allForm', ['siteId' => $siteId, 'tab' => 'order']);
        }
        $site = Site::findOrFail($siteId);
        $supervisor = User::find($site->supervisor_id); 
        return view('admin.menus.material.add_order', compact('siteId', 'materialType','supervisor'));
    }

   public function materialOrder(Request $request)
{
    try {
        $rules = [
            'site_id'        => 'required|exists:sites,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'vendor_name'    => 'required',
            'vendor_mobile'  => 'required|numeric|digits:10',
            'vendor_address' => 'required',
            'material_type'  => 'required|string',
            'date'           => 'required',
            'price'          => 'required|numeric',
            'gst'            => 'nullable|numeric',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ];

        $hasMultiItems = $request->has('items') && is_array($request->items) && count($request->items) > 0;
        if ($hasMultiItems) {
            $rules['quantity'] = 'nullable';
        } else {
            $rules['quantity'] = 'required|numeric';
        }

        $validate = Validator::make($request->all(), $rules);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        // ✅ File upload
        $imageUrl = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('material_attachments', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $itemsList = [];
        $totalItemsQty = 0;
        $totalItemsPrice = 0;

        if ($hasMultiItems) {
            $parsedItems = [];
            foreach ($request->items as $key => $item) {
                if (!is_array($item)) continue;
                $name = $item['name'] ?? (is_string($key) ? $key : '');
                $qty = isset($item['quantity']) ? (float)$item['quantity'] : 0;
                if (!empty($name) && $qty > 0) {
                    $itemPrice = isset($item['price']) && $item['price'] !== '' ? (float)$item['price'] : ($qty * (float)($item['rate'] ?? 0));
                    $parsedItems[] = [
                        'name'          => $name,
                        'category'      => $item['category'] ?? null,
                        'specification' => $item['specification'] ?? null,
                        'quantity'      => $qty,
                        'unit'          => $item['unit'] ?? null,
                        'rate'          => !empty($item['rate']) ? (float)$item['rate'] : null,
                        'price'         => $itemPrice,
                    ];
                    $totalItemsQty += $qty;
                    $totalItemsPrice += $itemPrice;
                }
            }

            if (!empty($parsedItems)) {
                $totalGst = (float)($request->gst ?? 0);
                $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                foreach ($parsedItems as $idx => $pi) {
                    $itemCategory = $pi['name'];
                    $itemUnit = !empty($pi['unit']) ? $pi['unit'] : 'Nos';

                    $itemGst = null;
                    if ($totalGst > 0) {
                        if ($totalItemsPrice > 0) {
                            $itemGst = round(($pi['price'] / $totalItemsPrice) * $totalGst, 2);
                        } elseif ($idx === 0) {
                            $itemGst = $totalGst;
                        }
                    }

                    $itemMatType = !empty($pi['category']) ? $pi['category'] : $request->material_type;

                    MaterialOrder::create([
                        'site_id'       => $request->site_id,
                        'vendor_id'     => $request->vendor_id,
                        'order_no'      => $orderNo,
                        'order_group'   => $orderNo,
                        'material_type' => $itemMatType,
                        'category'      => $itemCategory,
                        'category_name' => $itemCategory,
                        'spec'          => $pi['specification'] ?? null,
                        'date'          => $request->date,
                        'quantity'      => $pi['quantity'],
                        'unit'          => $itemUnit,
                        'price'         => $pi['price'],
                        'gst'           => $itemGst,
                        'total_amount'  => (float)$pi['price'] + (float)($itemGst ?? 0),
                        'created_by'    => auth('admin')->id(),
                        'image_url'     => $imageUrl
                    ]);

                    $specStr = !empty($pi['specification']) ? " ({$pi['specification']})" : "";
                    $unitStr = !empty($pi['unit']) ? " {$pi['unit']}" : "";
                    $itemsList[] = "• {$pi['name']}{$specStr}: {$pi['quantity']}{$unitStr} - ₹" . number_format($pi['price'], 2);
                }
            } else {
                $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $singlePrice = (float)($request->price ?? 0);
                $singleGst = (float)($request->gst ?? 0);
                MaterialOrder::create([
                    'site_id'       => $request->site_id,
                    'vendor_id'     => $request->vendor_id,
                    'order_no'      => $orderNo,
                    'order_group'   => $orderNo,
                    'material_type' => $request->material_type,
                    'category'      => $request->category_name ?? $request->category ?? null,
                    'category_name' => $request->category_name ?? $request->category ?? null,
                    'date'          => $request->date,
                    'quantity'      => $request->quantity ?? 1,
                    'unit'          => $request->unit,
                    'price'         => $singlePrice,
                    'gst'           => $singleGst > 0 ? $singleGst : null,
                    'total_amount'  => $singlePrice + $singleGst,
                    'created_by'    => auth('admin')->id(),
                    'image_url'     => $imageUrl
                ]);
            }
        } else {
            $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $singlePrice = (float)($request->price ?? 0);
            $singleGst = (float)($request->gst ?? 0);
            MaterialOrder::create([
                'site_id'       => $request->site_id,
                'vendor_id'     => $request->vendor_id,
                'order_no'      => $orderNo,
                'order_group'   => $orderNo,
                'material_type' => $request->material_type,
                'category'      => $request->category_name ?? $request->category ?? null,
                'category_name' => $request->category_name ?? $request->category ?? null,
                'date'          => $request->date,
                'quantity'      => $request->quantity,
                'unit'          => $request->unit,
                'price'         => $singlePrice,
                'gst'           => $singleGst > 0 ? $singleGst : null,
                'total_amount'  => $singlePrice + $singleGst,
                'created_by'    => auth('admin')->id(),
                'image_url'     => $imageUrl
            ]);
        }

        // ✅ Vendor payment details update
        $totalUnits = MaterialOrder::where('vendor_id', $request->vendor_id)->sum('quantity');
        $totalAmount = MaterialOrder::where('vendor_id', $request->vendor_id)->sum('price');
        $paidAmount = VendorPayment::where('vendor_id', $request->vendor_id)->sum('payment');

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

        // ✅ Site details
        $site = Site::findOrFail($request->site_id);

        // ✅ WhatsApp message
        $itemsSummary = !empty($itemsList) ? "\nOrdered Items:\n" . implode("\n", $itemsList) . "\n" : "";

        $message = "*Pojo Infra360*\n"
            . "Site Name: {$site->site_name} - Material Order\n"
            . "Location: {$site->location} \n"
            . "Vendor Name: {$request->vendor_name}\n"
            . "Vendor Address: {$request->vendor_address}\n"
            . "Mobile Number: {$request->vendor_mobile}\n"
            . "Material Type: " . ucfirst($request->material_type) . "\n"
            . (!empty($request->category_name) ? "Category: {$request->category_name}\n" : "")
            . (!empty($request->unit) && empty($itemsSummary) ? "Unit: {$request->unit}\n" : "")
            . $itemsSummary
            . "Date: " . \Carbon\Carbon::parse($request->date)->format('d-m-Y') . "\n"
            . "Total Quantity: " . ($totalItemsQty > 0 ? $totalItemsQty : $request->quantity) . "\n"
            . "Total Price: ₹" . number_format((float)$request->price, 2) . "\n"
            . (!empty($request->gst) ? "GST: ₹{$request->gst}\n" : "")
            . (!empty($imageUrl) ? "Image: {$imageUrl}\n" : "");

        $whatsappUrl = "https://wa.me/{$request->vendor_mobile}?text=" . urlencode($message);

        return response()->json([
            'status' => 'success',
            'message' => 'Material order placed successfully.',
            'whatsapp_url' => $whatsappUrl
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function materialPayment(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'vendor_id' => 'required|exists:vendors,id',
            'material_type' => 'required|string',
            'quantity' => 'required',
            'date'  => 'required',
            'total_amount' => 'required',
            'settled_amount' => 'required',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
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
            'created_by'  => auth('admin')->id(),
        ]);

        // Save PDF
        Storage::makeDirectory('public/whatsapp_pdfs');
        $pdf = Pdf::loadView('admin.helper.pdf_request', [
            'request' => $materialPayment,
            'vendor_name' => $request->vendor_name
        ]);
        $filename = 'material_request_' . Str::slug($request->vendor_name) . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::put("public/whatsapp_pdfs/$filename", $pdf->output());
        $publicLink = asset("storage/whatsapp_pdfs/$filename");

        $whatsappUrl = "https://wa.me/{$request->vendor_mobile}?text=" . urlencode("\n$publicLink\n\n");

        return response()->json([
            'status' => 'success',
            'message' => 'Request has been sent successfully.',
            'whatsapp_url' => $whatsappUrl
        ]);
    }

    // Update material order
    public function updateOrder(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'date' => 'required|date',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'gst' => 'nullable|numeric',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $order = MaterialOrder::findOrFail($id);

        $updateData = [
            'date' => $request->date,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'gst' => $request->gst,
            'total_amount' => (float)$request->price + (float)($request->gst ?? 0),
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('material_attachments', 'public');
            $updateData['image_url'] = asset('storage/' . $path);
        }

        $order->update($updateData);

        if (!empty($order->order_no)) {
            $sharedUpdates = ['date' => $request->date];
            if (isset($updateData['image_url'])) {
                $sharedUpdates['image_url'] = $updateData['image_url'];
            }
            MaterialOrder::where('order_no', $order->order_no)
                ->where('id', '!=', $order->id)
                ->update($sharedUpdates);
        }

        if ($order->vendor_id) {
            $totalUnits = MaterialOrder::where('vendor_id', $order->vendor_id)->sum('quantity');
            $totalAmount = MaterialOrder::where('vendor_id', $order->vendor_id)->sum('price');
            $paidAmount = VendorPayment::where('vendor_id', $order->vendor_id)->sum('payment');

            VendorPayDetail::updateOrCreate(
                ['vendor_id' => $order->vendor_id],
                [
                    'total_units' => $totalUnits,
                    'total_unit_price' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                    'updated_by' => auth('admin')->id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Material order updated successfully.');
    }

    // Download material order purchase invoice PDF (Consolidates all items from the purchase order into ONE PDF)
    public function orderPdf($id)
    {
        $order = MaterialOrder::with('vendor', 'site')->findOrFail($id);

        if (!empty($order->order_no)) {
            $orderItems = MaterialOrder::with('vendor')
                ->where('order_no', $order->order_no)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            // Legacy fallback: find items for same site, vendor, date, and creation timestamp within 5 seconds
            $orderItems = MaterialOrder::with('vendor')
                ->where('site_id', $order->site_id)
                ->where('vendor_id', $order->vendor_id)
                ->where('date', $order->date)
                ->whereBetween('created_at', [
                    $order->created_at->copy()->subSeconds(5),
                    $order->created_at->copy()->addSeconds(5)
                ])
                ->orderBy('id', 'asc')
                ->get();
        }

        if ($orderItems->isEmpty()) {
            $orderItems = collect([$order]);
        }

        $pdf = Pdf::loadView('admin.helper.material_order_pdf', compact('order', 'orderItems'));
        $orderIdentifier = $order->order_no ?: ('PO-' . str_pad($order->id, 5, '0', STR_PAD_LEFT));
        $filename = 'purchase_order_' . $orderIdentifier . '.pdf';

        return $pdf->download($filename);
    }

    // Download materials overview PDF for the filtered site, month/week
    public function overviewPdf(Request $request, $siteId, $materialType)
    {
        $month = $request->query('month') ?: Carbon::now()->format('Y-m');
        $week = (int) $request->query('week');

        $startOfMonth = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $startDate = $startOfMonth->copy();
        $endDate = $endOfMonth->copy();

        if ($week >= 1 && $week <= 4) {
            $daysInMonth = $startOfMonth->daysInMonth;
            $weekLength = ceil($daysInMonth / 4);
            $startDate = $startOfMonth->copy()->addDays(($week - 1) * $weekLength);
            $endDate = $startDate->copy()->addDays($weekLength - 1);
            if ($endDate->gt($endOfMonth)) {
                $endDate = $endOfMonth;
            }
        } else {
            $week = null;
        }

        $query = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if (strtolower($materialType) !== 'all') {
            $query->where('material_type', $materialType);
        }

        $materials = $query->get();
        $site = Site::findOrFail($siteId);
        $totalUnits = $materials->sum('quantity');
        $totalAmount = $materials->sum('price');

        $pdf = Pdf::loadView('admin.helper.material_overview_pdf', compact(
            'materials',
            'site',
            'siteId',
            'materialType',
            'month',
            'week',
            'totalUnits',
            'totalAmount'
        ));

        $filename = 'materials_overview_' . strtolower($materialType) . '_' . $month . '.pdf';
        return $pdf->download($filename);
    }

    // Delete material order
    public function deleteOrder($id)
    {
        $order = MaterialOrder::findOrFail($id);
        $vendorId = $order->vendor_id;

        if (!empty($order->order_no)) {
            MaterialOrder::where('order_no', $order->order_no)->delete();
        } else {
            $order->delete();
        }

        if ($vendorId) {
            $totalUnits = MaterialOrder::where('vendor_id', $vendorId)->sum('quantity');
            $totalAmount = MaterialOrder::where('vendor_id', $vendorId)->sum('price');
            $paidAmount = VendorPayment::where('vendor_id', $vendorId)->sum('payment');

            VendorPayDetail::updateOrCreate(
                ['vendor_id' => $vendorId],
                [
                    'total_units' => $totalUnits,
                    'total_unit_price' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                    'updated_by' => auth('admin')->id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Material order deleted successfully.');
    }
}
