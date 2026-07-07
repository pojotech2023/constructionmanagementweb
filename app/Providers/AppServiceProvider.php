<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\Site;
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
            ]);
        });
    }
}
