<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\SubcontractorService;
use App\Models\User;
use App\Models\Wages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SiteController extends Controller
{
    protected function getSiteLimit(): int
    {
        return Setting::getCurrentPlan()['site_limit'];
    }

    public function index(Request $request)
    {
        $status = $request->input('status');

        $sites = Site::with('customer')
            ->where('is_inactive', 0)
            ->when($status && $status !== 'All', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->get();

        $sites->each(function ($site) {
            $site->attendance_expense = $this->getAttendanceExpense((int) $site->id);
            $site->material_expense = (float) MaterialOrder::where('site_id', $site->id)->sum('price');
            $site->material_other_utility_expense = (float) OtherUtilities::where('site_id', $site->id)->sum('amount');
            $site->subcontractor_expense = (float) SubcontractorService::where('site_id', $site->id)->sum('amount');
            $site->subcontractor_other_utility_expense = (float) OtherUtilitiesSub::where('site_id', $site->id)->sum('amount');
            $site->expense_amount = $site->attendance_expense
                + $site->material_expense
                + $site->material_other_utility_expense
                + $site->subcontractor_expense
                + $site->subcontractor_other_utility_expense;
            $site->balance_amount = (float) ($site->budget_amount ?? 0) - $site->expense_amount;
        });

        $activeSiteCount = $sites->count();
        $siteLimit = $this->getSiteLimit();

        return view('admin.menus.site_management.site_management', compact('sites', 'status', 'activeSiteCount', 'siteLimit'));
    }

    private function getAttendanceExpense(int $siteId): float
    {
        $attendances = Attendance::where('site_id', $siteId)->get();
        $wages = Wages::where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->get();

        return $attendances->sum(function ($attendance) use ($wages) {
            $wage = $wages
                ->where('category', $attendance->category)
                ->where('date', '<=', $attendance->date)
                ->first();

            return (float) $attendance->count * (float) ($wage->amount ?? 0);
        });
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
        'site_img'       => 'required_without:temp_site_img|image|mimes:jpg,jpeg,png,webp',
        'temp_site_img'  => 'nullable|string',
        'location'       => 'required|string',
        'budget_amount'  => 'nullable|numeric|min:0',
        'flat_area'      => 'required|string',
        'built_up_area'  => 'required|string',
        'duration'       => 'required|string',
        'supervisor_id'  => 'required|exists:users,id',

        // Customer fields
        'name'           => 'required|string',
        'mobile_no'      => 'required|numeric|digits:10',
        'email'          => 'required|email|unique:customers,email',
        'dob'            => 'required|date',
        'address'        => 'required|string',
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
        'status'         => 'nullable'
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
        $site = Site::with('materialOrders')
            ->where('id', $id)->first();

        $totalUnits = $site->materialOrders->sum('quantity');
        $totalValues = $site->materialOrders->sum('price');
        return view('admin.menus.site_management.site_detail', compact('site', 'totalUnits', 'totalValues'));
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

    public function paymentHistory($id)
    {
        $site = Site::findOrFail($id);
        $histories = SitePayment::where('site_id', $id)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $budgetAmount = (float) ($site->budget_amount ?? 0);
        $paidAmount = (float) $histories->sum('payment');
        $balanceAmount = max($budgetAmount - $paidAmount, 0);

        return view('admin.menus.site_management.payment_history', compact('site', 'histories', 'budgetAmount', 'balanceAmount'));
    }
}
