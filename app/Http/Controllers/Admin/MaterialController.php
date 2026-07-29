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

        return view('admin.menus.material.material_management', compact('site', 'materials'));
    }


    // Material details
    public function index(Request $request, $siteId, $materialType)
    {
        $month = $request->query('month') ?: Carbon::now()->format('Y-m');
        $week = (int) $request->query('week');

        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

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

        $materials = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function ($material) {
                $material->date = $material->date ? Carbon::parse($material->date)->format('d-m-Y') : null;
                $material->created_at = $material->created_at ? $material->created_at->format('d-m-Y') : null;
                $material->updated_at = $material->updated_at ? $material->updated_at->format('d-m-Y') : null;

                return $material;
            });

        $site = Site::find($siteId);
        $siteName = $site ? $site->site_name : 'Unknown Site';

        $totalUnits = MaterialOrder::where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('quantity');

        $totalAmount = MaterialOrder::where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('price');

        $settledAmount = MaterialPayment::where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('settled_amount');

        $pendingAmount = MaterialPayment::where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('pending_amount');

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
            'week'
        ));
    }

    // Material details
    public function getMaterialData(Request $request, $siteId)
    {
        $monthYear = $request->input('monthYear'); // format: YYYY-MM
        $week = (int) $request->input('week'); // 1, 2, 3, 4
        $materialType = $request->input('material_type');

        $startOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->endOfMonth();

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

        $materials = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function ($material) {
                $material->date = $material->date ? Carbon::parse($material->date)->format('d-m-Y') : null;
                $material->created_at = $material->created_at ? $material->created_at->format('d-m-Y') : null;
                $material->updated_at = $material->updated_at ? $material->updated_at->format('d-m-Y') : null;

                return $material;
            });

        $totalUnits = $materials->sum('quantity');
        $totalAmount = $materials->sum('price');

        $settledAmount = MaterialPayment::where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('settled_amount');

        $pendingAmount = MaterialPayment::where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('pending_amount');

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
            $startOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->endOfMonth();

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

        $materials = \App\Models\MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->where('material_type', $materialType)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $filename = sprintf('%s_%s_%s.csv', $materialType, $siteId, now()->format('Ymd_His'));

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['Date', 'Quantity', 'Vendor', 'Price', 'Material Type', 'Image Link'];

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
    // Get the site
    $site = Site::findOrFail($siteId);
    $supervisor = User::find($site->supervisor_id);
   return view('admin.menus.material.add_request', compact('siteId', 'materialType', 'supervisor'));
}

public function materialRequest(Request $request)
{
    
    $validate = Validator::make($request->all(), [
        'site_id'             => 'required|exists:sites,id',
        'vendor_name'         => 'required|string',
        'vendor_mobile'       => 'required|numeric|digits:10',
        'vendor_address'      => 'required|string',
        'items'       => 'required|string',
        'price'       => 'required|string',
        'quantity'            => 'required',
        'date_of_delivery'  => 'required',
        'supervisor_name'     => 'required',
        'supervisor_phone'    => 'required|numeric|digits:10',
       'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

    ]);

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

    // ✅ Save only selected fields to DB
    $materialRequest = MaterialRequest::create([
        'site_id'             => $request->site_id,
        'quantity'            => $request->quantity,
        'unit'                => $request->unit ?? null,
        'image_url'           => $imageUrl,
        'date_of_delivery'  => $request->date_of_delivery,
        'price'             => $request->price,
        'items'             =>$request->items,
        'created_by'          => auth('admin')->id(),
        'source'              => MaterialRequest::SOURCE_ADMIN,
    ]);

    // ✅ WhatsApp message (full info even if not stored)
    $site = Site::find($request->site_id);


   $message = "*POJO INFRA 360*\n"
    . "Site: {$site->site_name} - Material Request\n"
    . "Location: {$site->location}\n"
    . "Vendor Name: {$request->vendor_name}\n"
    . "Vendor Mobile Number: {$request->vendor_mobile}\n"
    . "items: {$request->items}\n"
    . (!empty($request->category_name) ? "Category: {$request->category_name}\n" : "")
    . (!empty($request->unit) ? "Unit: {$request->unit}\n" : "")
    . "Quantity: {$request->quantity}\n"
    . "Delivery: {$request->date_of_delivery}\n"
    . "Supervisor: {$request->supervisor_name} ({$request->supervisor_phone})\n"
    ."Price: {$request->price}\n"
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
          $site = Site::findOrFail($siteId);
        $supervisor = User::find($site->supervisor_id); 
        return view('admin.menus.material.add_order', compact('siteId', 'materialType','supervisor'));
    }

   public function materialOrder(Request $request)
{
    try {
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'vendor_id' => 'required|exists:vendors,id',
            'vendor_name' => 'required',
            'vendor_mobile' => 'required|numeric|digits:10',
            'vendor_address' => 'required',
            'material_type' => 'required|string',
            'date' => 'required',
            'quantity' => 'required|numeric',
            'unit' => 'nullable',
            'price' => 'required|numeric',
            'gst' => 'nullable|numeric',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

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

        $material_order = MaterialOrder::create([
            'site_id' => $request->site_id,
            'vendor_id' => $request->vendor_id,
            'material_type' => $request->material_type,
            'date' => $request->date,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'price' => $request->price,
            'gst' => $request->gst,
            'created_by' => auth('admin')->id(),
            'image_url' => $imageUrl
        ]);

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

        // ✅ Update site expense
        $site = Site::findOrFail($request->site_id);

        // ✅ WhatsApp message
        $message = "*POJO INFRA 360*\n"
            . "Site Name: {$site->site_name} - Material Order\n"
            ."Location: {$site->location} \n"
            . "Vendor Name: {$request->vendor_name}\n"
            . "Vendor Address: {$request->vendor_address}\n"
            . "Mobile Number: {$request->vendor_mobile}\n"
            . "Material Type: {$request->material_type}\n"
            . (!empty($request->category_name) ? "Category: {$request->category_name}" : "")
            . (!empty($request->unit) ? " ({$request->unit})" : "") . "\n"
            . "Date: " . \Carbon\Carbon::parse($request->date)->format('d-m-Y') . "\n"
            . "Quantity: {$request->quantity}\n"
            . "Price: ₹{$request->price}\n"
            . (!empty($imageUrl) ? "Image: {$imageUrl}\n" : "");

        $whatsappUrl = "https://wa.me/{$request->vendor_mobile}?text=" . urlencode($message);

        return response()->json([
            'status' => 'success',
            'message' => 'Material order placed successfully.'
        ]);
    } catch (\Exception $e) {
        // Catch all exceptions and show actual error message
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
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('material_attachments', 'public');
            $updateData['image_url'] = asset('storage/' . $path);
        }

        $order->update($updateData);

        return redirect()->back()->with('success', 'Material order updated successfully.');
    }

    // Download material order purchase invoice PDF
    public function orderPdf($id)
    {
        $order = MaterialOrder::with('vendor')->findOrFail($id);

        $pdf = Pdf::loadView('admin.helper.material_order_pdf', compact('order'));
        $filename = 'material_order_' . $order->id . '.pdf';

        return $pdf->download($filename);
    }

    // Delete material order
    public function deleteOrder($id)
    {
        $order = MaterialOrder::findOrFail($id);
        $order->delete();
        return redirect()->back()->with('success', 'Material order deleted successfully.');
    }
}
