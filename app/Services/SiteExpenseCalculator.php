<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\MaterialOrder;
use App\Models\OtherUtilities;
use App\Models\OtherUtilitiesSub;
use App\Models\SubcontractorPayment;
use App\Models\SubcontractorService;
use App\Models\Wages;

class SiteExpenseCalculator
{
    public function breakdown(int $siteId): array
    {
        $breakdown = [
            'attendance' => $this->attendance($siteId),
            'materials' => (float) MaterialOrder::where('site_id', $siteId)->sum('price'),
            'material_utilities' => (float) OtherUtilities::where('site_id', $siteId)->sum('amount'),
            'subcontractors' => (float) SubcontractorService::where('site_id', $siteId)->sum('amount'),
            'petty_cash' => (float) SubcontractorPayment::where('site_id', $siteId)->sum('payment'),
            'subcontractor_utilities' => (float) OtherUtilitiesSub::where('site_id', $siteId)->sum('amount'),
        ];

        $breakdown['total'] = array_sum($breakdown);

        return $breakdown;
    }

    public function total(int $siteId): float
    {
        return (float) $this->breakdown($siteId)['total'];
    }

    private function attendance(int $siteId): float
    {
        $attendances = Attendance::where('site_id', $siteId)->get();
        $wages = Wages::where('site_id', $siteId)->orderByDesc('date')->get();

        return (float) $attendances->sum(function ($attendance) use ($wages) {
            $wage = $wages->first(function ($wage) use ($attendance) {
                return $wage->category === $attendance->category
                    && $wage->date <= $attendance->date;
            });

            return (float) $attendance->count * (float) ($wage->amount ?? 0);
        });
    }
}
