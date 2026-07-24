<?php

namespace App\Providers;

use App\Models\MaterialType;
use App\Models\Setting;
use App\Models\Site;
use App\Models\SubcontractorType;
use App\Models\Unit;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $currentPlan = Setting::getCurrentPlan();
            $siteLimit = $currentPlan['site_limit'];
            $activeSiteCount = Site::where('is_inactive', 0)->count();
            $remainingSiteLimit = max($siteLimit - $activeSiteCount, 0);

            $view->with([
                'sharedSiteLimit' => $siteLimit,
                'sharedActiveSiteCount' => $activeSiteCount,
                'sharedRemainingSiteLimit' => $remainingSiteLimit,
                'sharedCurrentPlanName' => $currentPlan['name'],
                'sharedCurrentPlanKey' => $currentPlan['key'],
                'sharedMenuVisibility' => Setting::getMenuVisibility(),
                'sharedUnits' => Unit::orderBy('name')->pluck('name'),
                'sharedMaterialTypes' => MaterialType::orderBy('name')->get(),
                'sharedSubcontractorTypes' => SubcontractorType::orderBy('name')->get(),
                'sharedHiddenMaterialTypes' => Setting::getHiddenMaterialTypes(),
                'sharedHiddenSubcontractorTypes' => Setting::getHiddenSubcontractorTypes(),
            ]);
        });
    }
}
