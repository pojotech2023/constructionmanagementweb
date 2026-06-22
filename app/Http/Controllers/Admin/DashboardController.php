<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\MaterialOrder;
use App\Models\OtherUtilities;
use App\Models\OtherUtilitiesSub;
use App\Models\Setting;
use App\Models\Site;
use App\Models\SubcontractorPayment;
use App\Models\SubcontractorService;
use App\Models\Wages;
use App\Services\SiteExpenseCalculator;

class DashboardController extends Controller
{
    public function getDashboard(SiteExpenseCalculator $expenseCalculator)
    {
        $currentPlan = Setting::getCurrentPlan();
        $totalSites = $currentPlan['site_limit'];
        $addedSites = Site::where('is_inactive', 0)->count();
        $remainingSites = max($totalSites - $addedSites, 0);

        $charts = Site::where('is_inactive', 0)
            ->latest()
            ->get()
            ->map(function ($site) use ($expenseCalculator) {
                $budgetAmount = (float) ($site->budget_amount ?? 0);
                $expenseAmount = $expenseCalculator->total((int) $site->id);
                $balanceAmount = max($budgetAmount - $expenseAmount, 0);

                return [
                    'site_name' => $site->site_name,
                    'status' => in_array($site->status, ['Completed', 'Ongoing'], true) ? $site->status : 'Ongoing',
                    'data' => [$expenseAmount, $balanceAmount, $budgetAmount],
                    'budget_amount' => $budgetAmount,
                    'expense_amount' => $expenseAmount,
                    'balance_amount' => $balanceAmount,
                ];
            })
            ->values();

        return view('admin.menus.dashboard', compact(
            'totalSites',
            'addedSites',
            'remainingSites',
            'charts'
        ));
    }

}
