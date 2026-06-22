<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Subcontractor;
use App\Models\SubcontractorPayDetail;
use App\Models\SubcontractorPayment;
use App\Models\SubcontractorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubcontractorController extends Controller
{
     //Subcontractor Management

     //List
     public function index()
    {
        $subcontractors = Subcontractor::orderBy('id', 'desc')->get();
        return view('admin.menus.subcontractor.subcontractor_management', compact('subcontractors'));
    }

    //add
    public function store(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'name'          => 'required|string',
            'subcontractors' => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email'         => 'required|email|unique:subcontractors,email',
            'address'       => 'required|string',
            'gst'           => 'required'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

       $subcontractor = Subcontractor::create([
            'name' => $request->name,
            'subcontractors' => $request->subcontractors,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'gst' => $request->gst,
            'created_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Subcontractor created successfully!');
    }
    
    //update
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'subcontractors' => 'required|string',
            'mobile_no' => 'required|numeric|digits:10',
            'email'         => 'required|email',
            'address'  => 'required|string',
            'gst'           => 'required'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

       $subcontractor = Subcontractor::findOrFail($request->subcontractor_id);

       $subcontractor->update([
            'name'      => $request->name,
            'subcontractors' => $request->subcontractors,
            'mobile_no' => $request->mobile_no,
            'email'  => $request->email,
            'address'  => $request->address,
            'gst' => $request->gst,
            'updated_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Subcontractor updated successfully!');
    }

    //delete
    public function delete($id)
    {
       $subcontractor = Subcontractor::findOrFail($id);
       $subcontractor->delete();
        return back()->with('success', 'Subcontractor Deleted Successfully!');
    }

    //search
    public function search(Request $request)
    {
       $subcontractors = Subcontractor::where('name', 'LIKE', $request->name . '%')
            ->select('id', 'name', 'mobile_no', 'address')
            ->get();

        return response()->json($subcontractors);
    }


    //subcontractor detail
    public function getSubcontractor($siteId)
    {
        $site = Site::with('subcontractorService')->findOrFail($siteId);

        $subcontractors = $site->subcontractorService->groupBy(function ($item) {
            return strtolower($item->subcontractor_type);
        })->mapWithKeys(function ($group, $type) {
            return [
                $type =>  [
                    'totalAmounts' => $group->sum(function ($item) {
                        return is_numeric($item->amount) ? $item->amount : 0;
                    })
                ]
            ];
        });
        return view('admin.menus.subcontractor.subcontractor_detail', compact('site', 'subcontractors'));
    }

    public function pettyCashPaymentDetail($siteId)
    {
        $site = Site::findOrFail($siteId);
        $pettyCashEmail = 'pettycash.site.' . $site->id . '@local.invalid';

        $subcontractor = Subcontractor::firstOrCreate(
            ['email' => $pettyCashEmail],
            [
                'name' => 'Petty Cash - ' . $site->site_name,
                'subcontractors' => 'Petty Cash',
                'mobile_no' => '0000000000',
                'address' => $site->address ?? $site->site_name,
                'gst' => 'N/A',
                'created_by' => auth('admin')->id(),
            ]
        );

        $services = SubcontractorService::where('subcontractor_id', $subcontractor->id)->get();
        $totalAmount = $services->sum('amount');
        $subcontractorId = $subcontractor->id;
        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $subcontractorId)->first();
        $histories = SubcontractorPayment::where('subcontractor_id', $subcontractorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $paidAmount = $histories->sum('payment');
        $title = 'Petty Cash Payment Detail';
        $backRoute = route('subcontractor.detail', ['siteId' => $site->id]);
        $siteName = $site->site_name;

        return view('admin.menus.subcontractor.subcontractor_paydetail', compact(
            'totalAmount',
            'subcontractorId',
            'siteId',
            'siteName',
            'paydetail',
            'histories',
            'paidAmount',
            'title',
            'backRoute'
        ));
    }

    //subcontractor inner detail
    public function getSubcontractorDetails($siteId, $subcontractorType)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $subcontractors = SubcontractorService::with('subcontractor')
            ->where('site_id', $siteId)
            ->where('subcontractor_type', $subcontractorType)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        $site = Site::find($siteId);
        $siteName = $site ? $site->site_name : 'Unknown Site';

        $totalAmount = SubcontractorService::where('site_id', $siteId)
            ->where('subcontractor_type', $subcontractorType)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        return view('admin.menus.subcontractor.subcontractor_inner_detail', compact(
            'subcontractors',
            'siteId',
            'siteName',
            'totalAmount',
            'subcontractorType'
        ));
    }

    // Export all subcontractor services for a site
    public function exportSite(Request $request, $siteId)
    {
        $month = $request->query('month') ?: now()->format('Y-m');
        $week = (int) $request->query('week');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if ($fromDate || $toDate) {
            $startDate = $fromDate ? Carbon::parse($fromDate)->startOfDay() : Carbon::create(1900, 1, 1)->startOfDay();
            $endDate = $toDate ? Carbon::parse($toDate)->endOfDay() : now()->endOfDay();
        } else {
            $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

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

        $records = SubcontractorService::with('subcontractor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $filename = 'subcontractor_site_' . $siteId . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['Date', 'Type', 'Subcontractor', 'Amount', 'Counts'];

        $callback = function () use ($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($records as $r) {
                fputcsv($file, [
                    $r->date ? Carbon::parse($r->date)->format('d-m-Y') : '',
                    $r->subcontractor_type,
                    optional($r->subcontractor)->name,
                    $r->amount,
                    $r->no_counts,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Export subcontractor services filtered by type
    public function exportType(Request $request, $siteId, $subcontractorType)
    {
        $month = $request->query('month') ?: now()->format('Y-m');
        $week = (int) $request->query('week');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if ($fromDate || $toDate) {
            $startDate = $fromDate ? Carbon::parse($fromDate)->startOfDay() : Carbon::create(1900, 1, 1)->startOfDay();
            $endDate = $toDate ? Carbon::parse($toDate)->endOfDay() : now()->endOfDay();
        } else {
            $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

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

        $records = SubcontractorService::with('subcontractor')
            ->where('site_id', $siteId)
            ->where('subcontractor_type', $subcontractorType)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function ($subcontractor) {
                $subcontractor->date = $subcontractor->date ? Carbon::parse($subcontractor->date)->format('d-m-Y') : null;
                $subcontractor->created_at = $subcontractor->created_at ? $subcontractor->created_at->format('d-m-Y') : null;
                $subcontractor->updated_at = $subcontractor->updated_at ? $subcontractor->updated_at->format('d-m-Y') : null;

                return $subcontractor;
            });

        $filename = 'subcontractor_' . Str::slug($subcontractorType) . '_' . $siteId . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['Date', 'Subcontractor', 'Amount', 'Counts'];

        $callback = function () use ($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($records as $r) {
                fputcsv($file, [
                    $r->date,
                    optional($r->subcontractor)->name,
                    $r->amount,
                    $r->no_counts,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // subcontractor inner detail
    public function getSubcontractorData(Request $request, $siteId)
    {
        $monthYear = $request->input('monthYear'); // format: YYYY-MM
        $week = (int) $request->input('week'); // 1, 2, 3, 4
        $subcontractorType = $request->input('subcontractor_type');

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

        $subcontractors = SubcontractorService::with('subcontractor')
            ->where('site_id', $siteId)
            ->where('subcontractor_type', $subcontractorType)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalAmount = $subcontractors->sum('amount');

        return response()->json([
            'subcontractors' => $subcontractors,
            'totalAmount' => $totalAmount,
        ]);
    }

    //subcontractor get service form
    public function getServiceForm($siteId, $subcontractorType)
    {
        return view('admin.menus.subcontractor.add_service', compact('siteId', 'subcontractorType'));
    }

    // subcontractor add service form
    public function subcontractorService(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'subcontractor_name'         => 'required',
            'subcontractor_mobile'       => 'required',
            'subcontractor_address' => 'required',
            'subcontractor_type' => 'required|string',
             'no_counts' => 'required|string',
            'date' => 'required',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $subcontractor_service = SubcontractorService::create([
            'site_id' => $request->site_id,
            'subcontractor_id' => $request->subcontractor_id,
            'subcontractor_type' => $request->subcontractor_type,
            'date' => $request->date,
            'amount' => $request->amount,
            'no_counts'=>$request->no_counts,
            'remarks' => $request->remarks,
            'created_by'  => auth('admin')->id(),
        ]);

        $totalAmount = SubcontractorService::where('subcontractor_id', $request->subcontractor_id)->sum('amount');
        $paidAmount = SubcontractorPayment::where('subcontractor_id', $request->subcontractor_id)->sum('payment');

        SubcontractorPayDetail::updateOrCreate(
            ['subcontractor_id' => $request->subcontractor_id],
            [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('admin')->id(),
            ]
        );

        $site = Site::find($request->site_id);

       // $message = "* SS BULIDERS*\n"
         //   . "Site Name: {$site->site_name} - SubContractor Service\n"
          //  . "SubContractor Name: {$request->subcontractor_name}\n"
          //  . "SubContractor Address: {$request->subcontractor_address}\n"
          //  . "Mobile Number: {$request->subcontractor_mobile}\n"
          //  . "SubContractor Type: {$request->subcontractor_type}\n"
          //  . "Date: " . Carbon::parse($request->date)->format('d-m-Y') . "\n"
          //  . "Amount: ₹{$request->amount}\n";

        //$whatsappUrl = "https://wa.me/{$request->subcontractor_mobile}?text=" . urlencode($message);

        return response()->json([
            'status' => 'success',
            'message' => 'SubContractor service added successfully.',
           // 'whatsapp_url' => $whatsappUrl
        ]);
    }

    // Update subcontractor service
    public function updateService(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $service = SubcontractorService::findOrFail($id);
        $service->update([
            'date' => $request->date,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
        ]);

        return redirect()->back()->with('success', 'Subcontractor service updated successfully.');
    }

    // Delete subcontractor service
    public function deleteService($id)
    {
        $service = SubcontractorService::findOrFail($id);
        $service->delete();
        return redirect()->back()->with('success', 'Subcontractor service deleted successfully.');
    }

     public function dashboard()
    {
        $subcontractors = Subcontractor::withSum('subcontractorPayment', 'payment')
            ->withSum('subcontractorService', 'amount')
            ->get();

        return view('admin.menus.subcontractor.subcontractor_dashboard', compact('subcontractors'));
    }

    public function getPayDetailsForm($subcontractorId)
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);
        $services = SubcontractorService::with('site')
            ->where('subcontractor_id', $subcontractorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $totalCounts = $services->sum('no_counts');
        $totalAmount = $services->sum('amount');

        $paydetail = SubcontractorPayDetail::where('subcontractor_id', $subcontractorId)->first();
        $histories = SubcontractorPayment::where('subcontractor_id', $subcontractorId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $paidAmount = $histories->sum('payment');
        $balanceAmount = $totalAmount - $paidAmount;

        return view('admin.menus.subcontractor.subcontractor_paydetail', compact(
            'subcontractor',
            'services',
            'totalCounts',
            'totalAmount',
            'subcontractorId',
            'paydetail',
            'histories',
            'paidAmount',
            'balanceAmount'
        ));
    }

    public function subcontractorpayUpdate(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'subcontractor_id'    => 'required|exists:subcontractors,id',
            'total_amount' => 'required',
            'balance_amount'  => 'required',
            'paid_amount'  => 'nullable'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $payDetail = SubcontractorPayDetail::where('subcontractor_id', $request->subcontractor_id)->first();
        $paidAmount = SubcontractorPayment::where('subcontractor_id', $request->subcontractor_id)->sum('payment');
        $balanceAmount = (float) $request->total_amount - (float) $paidAmount;

        if ($payDetail) {
            $payDetail->update([
                'subcontractor_id' => $request->subcontractor_id,
                'total_amount' => $request->total_amount,
                'balance_amount'  => $balanceAmount,
                'paid_amount'  => $paidAmount,
                'updated_by'  => auth('admin')->id(),
            ]);
        } else {
            SubcontractorPayDetail::create([
                'subcontractor_id'         => $request->subcontractor_id,
                'total_amount'   => $request->total_amount,
                'balance_amount'      => $balanceAmount,
                'paid_amount' => $paidAmount,
                'created_by'  => auth('admin')->id(),
            ]);
        }

        return redirect()->back()->with('success', 'SubContractor pay details updated successfully!');
    }

    public function addPayment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'site_id' => 'nullable|exists:sites,id',
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

        $payment = SubcontractorPayment::create([
            'subcontractor_id' => $request->subcontractor_id,
            'site_id' => $request->site_id,
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'created_by'  => auth('admin')->id(),
        ]);

        $paidAmount = SubcontractorPayment::where('subcontractor_id', $request->subcontractor_id)->sum('payment');
        $services = SubcontractorService::where('subcontractor_id', $request->subcontractor_id);
        $totalAmount = (clone $services)->sum('amount');

        SubcontractorPayDetail::updateOrCreate(
            ['subcontractor_id' => $request->subcontractor_id],
            [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('admin')->id(),
            ]
        );

        // Get Subcontractor Info
        $subcontractor = Subcontractor::find($request->subcontractor_id);

       // $message = "Hi {$subcontractor->name},\nYour payment of ₹{$request->payment} on {$request->date} via {$request->payment_mode} has been recorded. Thank you!";
       // $whatsappUrl = "https://wa.me/{$subcontractor->mobile_no}?text=" . urlencode($message);

        return response()->json([
            'status' => 'success',
          //  'whatsapp_url' => $whatsappUrl
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

        $payment = SubcontractorPayment::findOrFail($id);

        $payment->update([
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'updated_by' => auth('admin')->id(),
        ]);

        $paidAmount = SubcontractorPayment::where('subcontractor_id', $payment->subcontractor_id)->sum('payment');
        $totalAmount = SubcontractorService::where('subcontractor_id', $payment->subcontractor_id)->sum('amount');

        SubcontractorPayDetail::updateOrCreate(
            ['subcontractor_id' => $payment->subcontractor_id],
            [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('admin')->id(),
            ]
        );

        return redirect()->back()->with('success', 'Payment updated successfully.');
    }

    public function deletePayment($id)
    {
        $payment = SubcontractorPayment::findOrFail($id);
        $subcontractorId = $payment->subcontractor_id;

        $payment->delete();

        $paidAmount = SubcontractorPayment::where('subcontractor_id', $subcontractorId)->sum('payment');
        $totalAmount = SubcontractorService::where('subcontractor_id', $subcontractorId)->sum('amount');

        SubcontractorPayDetail::updateOrCreate(
            ['subcontractor_id' => $subcontractorId],
            [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => (float) $totalAmount - (float) $paidAmount,
                'updated_by' => auth('admin')->id(),
            ]
        );

        return redirect()->back()->with('success', 'Payment deleted successfully.');
    }

    public function exportPettyCash(Request $request, $siteId)
    {
        $site = Site::findOrFail($siteId);
        $pettyCashEmail = 'pettycash.site.' . $site->id . '@local.invalid';
        $subcontractor = Subcontractor::where('email', $pettyCashEmail)->firstOrFail();

        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $historyQuery = SubcontractorPayment::where('subcontractor_id', $subcontractor->id);

        if ($fromDate) {
            $historyQuery->whereDate('date', '>=', Carbon::parse($fromDate)->toDateString());
        }

        if ($toDate) {
            $historyQuery->whereDate('date', '<=', Carbon::parse($toDate)->toDateString());
        }

        $histories = $historyQuery
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $filename = 'petty_cash_' . $site->id . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($histories) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.No', 'Date', 'Payment Mode', 'Remarks', 'Payment']);

            foreach ($histories as $index => $history) {
                fputcsv($file, [
                    $index + 1,
                    $history->date ? Carbon::parse($history->date)->format('d-m-Y') : '',
                    $history->payment_mode,
                    $history->remarks,
                    number_format((float) $history->payment, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function paymentHistory($subcontractorId)
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);
        $histories = SubcontractorPayment::with('subcontractor')
            ->where('subcontractor_id', $subcontractorId)
            ->get();

        $paidAmount = $histories->sum('payment');

        return view('admin.menus.subcontractor.payment_history', compact('subcontractor', 'histories', 'subcontractorId', 'paidAmount'));
    }

    public function exportPaymentHistory(Request $request, $subcontractorId)
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);
        $query = SubcontractorPayment::where('subcontractor_id', $subcontractorId);

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', Carbon::parse($request->from_date)->toDateString());
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', Carbon::parse($request->to_date)->toDateString());
        }

        $histories = $query->orderBy('date')->orderBy('id')->get();
        $filename = 'subcontractor_payment_history_' . $subcontractor->id . '_' . now()->format('Ymd_His') . '.csv';
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
