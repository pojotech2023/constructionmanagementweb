<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Site;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboard(Request $request)
    {
        $currentPlan = Setting::getCurrentPlan();
        $totalSites = $currentPlan['site_limit'];
        $addedSites = Site::where('is_inactive', 0)->count();

        return response()->json([
            'planName' => $currentPlan['name'],
            'planKey' => $currentPlan['key'],
            'totalSites' => $totalSites,
            'addedSites' => $addedSites,
            'remainingSites' => max($totalSites - $addedSites, 0),
        ]);
    }

}
