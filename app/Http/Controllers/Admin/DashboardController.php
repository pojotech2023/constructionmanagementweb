<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Site;

class DashboardController extends Controller
{
    public function getDashboard()
    {
        $currentPlan = Setting::getCurrentPlan();
        $totalSites = $currentPlan['site_limit'];
        $addedSites = Site::where('is_inactive', 0)->count();
        $remainingSites = max($totalSites - $addedSites, 0);

        return view('admin.menus.dashboard', compact('totalSites', 'addedSites', 'remainingSites'));
    }

}
