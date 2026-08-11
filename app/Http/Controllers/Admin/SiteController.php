<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiteReportExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Customer;
use App\Models\MaterialOrder;
use App\Models\OtherUtilities;
use App\Models\OtherUtilitiesSub;
use App\Models\RoleMapping;
use App\Models\Setting;
use App\Models\Site;
use App\Models\SitePayment;
use App\Models\SubcontractorPayment;
use App\Models\SubcontractorService;
use App\Models\User;
use App\Models\Wages;
use App\Services\SiteExpenseCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\SitePaymentMail;

class SiteController extends Controller
{
    protected function getSiteLimit(): int
    {
        return Setting::getCurrentPlan()['site_limit'];
    }

    protected function isSupervisor(): bool
    {
        return session('role_name') === 'Supervisor';
    }

    public function index(Request $request, SiteExpenseCalculator $expenseCalculator)
    {
        $status = $request->input('status');

        $sites = Site::with('customer')
            ->where('is_inactive', 0)
            ->when($status && $status !== 'All', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($this->isSupervisor(), function ($query) {
                return $query->where('supervisor_id', auth('admin')->id());
            })
            ->orderBy('id', 'desc')
            ->get();

        // Number only the currently-active sites so deleting one (a soft delete via
        // is_inactive) shifts the rest down instead of leaving a gap — e.g. deleting
        // site #1 makes the site that was #2 become #1, not stay #2.
        $displayNumbers = Site::where('is_inactive', 0)->orderBy('id')->pluck('id')
            ->values()
            ->flip()
            ->map(fn ($index) => $index + 1);

        $sites->each(function ($site) use ($expenseCalculator, $displayNumbers) {
            $site->display_no = $displayNumbers[$site->id];
            $expenses = $expenseCalculator->breakdown((int) $site->id);
            $site->attendance_expense = $expenses['attendance'];
            $site->material_expense = $expenses['materials'];
            $site->material_other_utility_expense = $expenses['material_utilities'];
            $site->subcontractor_expense = $expenses['subcontractors'];
            $site->petty_cash_expense = $expenses['petty_cash'];
            $site->subcontractor_other_utility_expense = $expenses['subcontractor_utilities'];
            $site->expense_amount = $expenses['total'];
            $site->balance_amount = (float) ($site->budget_amount ?? 0) - $site->expense_amount;
        });

        $activeSiteCount = $sites->count();
        $siteLimit = $this->getSiteLimit();

        return view('admin.menus.site_management.site_management', compact('sites', 'status', 'activeSiteCount', 'siteLimit'));
    }

 public function getForm()
{
    $activeSiteCount = Site::where('is_inactive', 0)->count();
    $siteLimit = $this->getSiteLimit();

    if ($activeSiteCount >= $siteLimit) {
        return redirect()->route('sitemanagement.list')
            ->with('error', "You reached maximum allowed sites ({$siteLimit}). Cannot add more.");
    }

    $supervisors = User::whereHas('roleMappings', function ($q) {
        $q->where('role_id', 2); // supervisor role
    })->get();

    return view('admin.menus.site_management.site_create', compact('supervisors'));
}

public function store(Request $request)
{
    $activeSiteCount = Site::where('is_inactive', 0)->count();
    $siteLimit = $this->getSiteLimit();
    if ($activeSiteCount >= $siteLimit) {
        return redirect()->route('sitemanagement.list')
            ->with('error', "You reached maximum allowed sites ({$siteLimit}). Cannot add more.");
    }

    $validate = Validator::make($request->all(), [
        'site_name'      => 'required|string',
        'site_img'       => 'nullable|image|mimes:jpg,jpeg,png,webp',
        'temp_site_img'  => 'nullable|string',
        'location'       => 'nullable|string',
        'budget_amount'  => 'nullable|numeric|min:0',
        'flat_area'      => 'nullable|string',
        'built_up_area'  => 'nullable|string',
        'duration'       => 'nullable|string',
        'supervisor_id'  => 'nullable|exists:users,id',

        // Customer fields
        'customer_type'       => 'nullable|in:new,existing',
        'existing_customer_id'=> 'nullable|exists:customers,id',
        'name'           => 'nullable|string',
        'mobile_no'      => 'nullable|numeric|digits:10',
        'email'          => 'nullable|email',
        'dob'            => 'nullable|date|before_or_equal:today',
        'address'        => 'nullable|string',
    ]);

    if ($validate->fails()) {
        $oldInput = $request->except('site_img');

        if (! $validate->errors()->has('site_img') && $request->hasFile('site_img') && $request->file('site_img')->isValid()) {
            $oldInput['temp_site_img'] = $request->file('site_img')->store('tmp/site', 'public');
        }

        return redirect()->back()->withErrors($validate)->withInput($oldInput);
    }

    // Upload Site Image (normalize directory to lowercase so display path matches storage path)
    $site_img = null;
    if ($request->hasFile('site_img')) {
        $site_img = $request->file('site_img')->store('site', 'public');
    } elseif ($request->filled('temp_site_img') && Storage::disk('public')->exists($request->temp_site_img)) {
        $site_img = 'site/' . basename($request->temp_site_img);
        Storage::disk('public')->move($request->temp_site_img, $site_img);
    }

    // ✅ Create Site Record
    $site = Site::create([
        'site_name'      => $request->site_name,
        'supervisor_id'  => $request->supervisor_id,
        'site_img'       => $site_img,
        'location'       => $request->location,
        'budget_amount'  => $request->budget_amount,
        'flat_area'      => $request->flat_area,
        'built_up_area'  => $request->built_up_area,
        'duration'       => $request->duration,
        'status'         => 'Ongoing',
        'created_by'     => auth('admin')->id(),
    ]);

    // ✅ Create Customer Record
    $customer = Customer::create([
        'site_id'     => $site->id,
        'name'        => $request->name,
        'mobile_no'   => $request->mobile_no,
        'email'       => $request->email,
        'dob'         => $request->dob,
        'address'     => $request->address,
        'created_by'  => auth('admin')->id(),
    ]);

    if ($customer) {
        return redirect()->back()->with('success', 'Site and Customer created successfully.');
    } else {
        return redirect()->back()->with('error', 'Failed to create Customer record.');
    }
}

    public function edit($id)
{
    $site = Site::findOrFail($id);

    // Get all supervisors (assuming role_mappings table connects users with roles)
    $supervisors = DB::table('role_mappings')
        ->join('users', 'role_mappings.user_id', '=', 'users.id')
        ->where('role_mappings.role_id', 2) // 2 = supervisor role_id (change if needed)
        ->select('users.id', 'users.name', 'users.mobile_no')
        ->get();

    return view('admin.menus.site_management.site_update', compact('site', 'supervisors'));
}


   public function update(Request $request, $id)
{
    $validate = Validator::make($request->all(), [
        'site_name'      => 'nullable|string',
        'site_img'       => 'nullable|image|mimes:jpg,jpeg,png,webp',
        'location'       => 'nullable|string',
        'budget_amount'  => 'nullable|numeric|min:0',
        'flat_area'      => 'nullable|string',
        'built_up_area'  => 'nullable|string',
        'duration'       => 'nullable|string',
        'supervisor_id'  => 'nullable|exists:users,id',
        'status'         => 'nullable|in:Ongoing,Completed'
    ]);

    if ($validate->fails()) {
        return redirect()->back()->withErrors($validate)->withInput();
    }

    $site = Site::findOrFail($id);

    // ✅ Handle image update
    $site_img = $site->site_img;
    if ($request->hasFile('site_img')) {
        if (!empty($site->site_img)) {
            Storage::disk('public')->delete($site->site_img);
        }
        $site_img = $request->file('site_img')->store('site', 'public');
    }

    // ✅ Update fields
    $site->update([
        'site_name'      => $request->site_name ?? $site->site_name,
        'site_img'       => $site_img,
        'location'       => $request->location ?? $site->location,
        'budget_amount'  => $request->budget_amount ?? $site->budget_amount,
        'flat_area'      => $request->flat_area ?? $site->flat_area,
        'built_up_area'  => $request->built_up_area ?? $site->built_up_area,
        'duration'       => $request->duration ?? $site->duration,
        'supervisor_id'  => $request->supervisor_id ?? $site->supervisor_id,
        'updated_by'     => auth('admin')->id(),
    ]);

    // ✅ Optional: status update
    if ($request->filled('status')) {
        $site->update([
            'status' => $request->status,
        ]);
    }

    return redirect()->back()->with('success', 'Site updated successfully!');
}


    public function delete($id) //softdelete , change 1 to is_inactive
    {
        $site = Site::find($id);
        $site->update(['is_inactive' => 1]);
        return back()->with('success', 'Site deleted successfully !');
    }

    //Site Detail
    public function siteDetail($id)
    {
        abort_unless(\App\Models\Setting::getMenuVisibility()['site_detail'] ?? true, 403);

        if ($this->isSupervisor()) {
            abort_unless(
                Site::where('id', $id)->where('supervisor_id', auth('admin')->id())->exists(),
                403,
                'You are not assigned to this site.'
            );
        }

        $site = Site::with('materialOrders')
            ->where('id', $id)->first();

        $totalUnits = $site->materialOrders->sum('quantity');
        $totalValues = $site->materialOrders->sum('price');
        return view('admin.menus.site_management.site_detail', compact('site', 'totalUnits', 'totalValues'));
    }

    public function exportFullReport($id)
    {
        abort_unless(\App\Models\Setting::getMenuVisibility()['export'] ?? true, 403);

        $site = Site::findOrFail($id);
        $filename = Str::slug($site->site_name) . '-full-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SiteReportExport($site), $filename);
    }

    public function paymentDetail($id)
    {
        $site = Site::with('payments')->findOrFail($id);
        $budgetAmount = (float) ($site->budget_amount ?? 0);
        $paidAmount = (float) $site->payments->sum('payment');
        $balanceAmount = max($budgetAmount - $paidAmount, 0);

        return view('admin.menus.site_management.payment_detail', compact('site', 'budgetAmount', 'paidAmount', 'balanceAmount'));
    }

    public function addPayment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'payment' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_mode' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors(),
            ], 422);
        }

        SitePayment::create([
            'site_id' => $request->site_id,
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'created_by' => auth('admin')->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('site.paymentDetail', $request->site_id),
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'payment' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_mode' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $payment = SitePayment::findOrFail($id);
        $payment->update([
            'payment' => $request->payment,
            'date' => $request->date,
            'payment_mode' => $request->payment_mode,
            'remarks' => $request->remarks,
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()->route('site.payment.history', $payment->site_id)->with('success', 'Payment updated successfully.');
    }

    public function deletePayment($id)
    {
        $payment = SitePayment::findOrFail($id);
        $siteId = $payment->site_id;
        $payment->delete();

        return redirect()->route('site.payment.history', $siteId)->with('success', 'Payment deleted successfully.');
    }

    public function paymentHistory(Request $request, $id)
    {
        $site = Site::findOrFail($id);
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $historyQuery = SitePayment::where('site_id', $id);

        if ($fromDate) {
            $historyQuery->whereDate('date', '>=', Carbon::parse($fromDate)->toDateString());
        }

        if ($toDate) {
            $historyQuery->whereDate('date', '<=', Carbon::parse($toDate)->toDateString());
        }

        $histories = $historyQuery
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $budgetAmount = (float) ($site->budget_amount ?? 0);
        $paidAmount = (float) $histories->sum('payment');
        $balanceAmount = max($budgetAmount - $paidAmount, 0);

        $customer = Customer::where('site_id', $id)
            ->where('is_inactive', 0)
            ->orderBy('id')
            ->first();
        $customerEmail = optional($customer)->email;

        return view('admin.menus.site_management.payment_history', compact('site', 'histories', 'budgetAmount', 'balanceAmount', 'customerEmail'));
    }

    public function downloadPaymentPdf($id)
    {
        $payment = SitePayment::with('site')->findOrFail($id);
        $site = $payment->site;
        $budgetAmount = (float) (optional($site)->budget_amount ?? 0);
        $totalPaid = (float) SitePayment::where('site_id', $payment->site_id)->sum('payment');
        $balanceAmount = max($budgetAmount - $totalPaid, 0);

        $pdf = Pdf::loadView('admin.helper.site_payment_pdf', compact(
            'payment',
            'budgetAmount',
            'totalPaid',
            'balanceAmount'
        ));
        $filename = 'site_payment_' . $payment->id . '.pdf';

        return $pdf->download($filename);
    }

    public function sendPaymentWhatsapp($id)
    {
        $payment = SitePayment::with('site')->findOrFail($id);
        $siteName = optional($payment->site)->site_name ?? 'Site';
        $pdfUrl = route('site.payment.pdf', $payment->id);
        $customer = Customer::where('site_id', $payment->site_id)
            ->where('is_inactive', 0)
            ->orderBy('id')
            ->first();
        $customerMobile = $customer ? preg_replace('/\D+/', '', $customer->mobile_no) : '';

        if (strlen($customerMobile) === 10) {
            $customerMobile = '91' . $customerMobile;
        }

        $message = "Site Payment History\n"
            . "Site: {$siteName}\n"
            . "Date: " . Carbon::parse($payment->date)->format('d-m-Y') . "\n"
            . "Payment: " . number_format((float) $payment->payment, 2) . "\n"
            . "Payment Mode: {$payment->payment_mode}\n"
            . "PDF: {$pdfUrl}";

        $whatsappUrl = $customerMobile
            ? 'https://wa.me/' . $customerMobile . '?text=' . urlencode($message)
            : 'https://wa.me/?text=' . urlencode($message);

        return redirect()->away($whatsappUrl);
    }

    public function sendPaymentMail($id)
    {
        $payment = SitePayment::with('site')->findOrFail($id);
        $customer = Customer::where('site_id', $payment->site_id)
            ->where('is_inactive', 0)
            ->orderBy('id')
            ->first();

        if (!$customer || !$customer->email) {
            return redirect()->route('site.payment.history', $payment->site_id)
                ->with('error', 'No customer email found for this site.');
        }

        $site = $payment->site;
        $budgetAmount = (float) (optional($site)->budget_amount ?? 0);
        $totalPaid = (float) SitePayment::where('site_id', $payment->site_id)->sum('payment');
        $balanceAmount = max($budgetAmount - $totalPaid, 0);

        $pdf = Pdf::loadView('admin.helper.site_payment_pdf', compact(
            'payment',
            'budgetAmount',
            'totalPaid',
            'balanceAmount'
        ));

        Mail::to($customer->email)->send(new SitePaymentMail($payment, $pdf->output()));

        return redirect()->route('site.payment.history', $payment->site_id)
            ->with('success', 'Payment receipt emailed successfully.');
    }

    public function exportPaymentHistory(Request $request, $id)
    {
        $site = Site::findOrFail($id);
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = SitePayment::where('site_id', $id);

        if ($fromDate) {
            $query->whereDate('date', '>=', Carbon::parse($fromDate)->toDateString());
        }

        if ($toDate) {
            $query->whereDate('date', '<=', Carbon::parse($toDate)->toDateString());
        }

        $histories = $query->orderBy('date')->orderBy('id')->get();
        $filename = 'site_payment_history_' . $site->id . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($histories) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.No', 'Date', 'Payment', 'Payment Mode', 'Remarks']);

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
