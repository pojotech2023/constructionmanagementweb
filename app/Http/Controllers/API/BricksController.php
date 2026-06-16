<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialOrder;
use App\Models\MaterialPayment;
use App\Models\MaterialRequest;
use App\Models\Site;
use Carbon\Carbon;

class BricksController extends Controller
{
    public function index(Request $request, $siteId)
    {
        // Inputs
        $monthYear = $request->query('monthYear', now()->format('Y-m')); // default: current month
        $week = (int) $request->query('week', 0); // default: 0 (full month)

        // Parse month range
        $startOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->endOfMonth();

        // Calculate date range
        if ($week == 0) {
            $startDate = $startOfMonth;
            $endDate = $endOfMonth;
        } else {
            $startDate = (clone $startOfMonth)->addWeeks($week - 1)->startOfWeek(Carbon::MONDAY);
            $endDate = (clone $startDate)->endOfWeek(Carbon::SUNDAY);

            if ($startDate < $startOfMonth) $startDate = $startOfMonth;
            if ($endDate > $endOfMonth) $endDate = $endOfMonth;
        }

        // Orders and payments
        $bricks = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'site_id' => $order->site_id,
                    'vendor_name' => $order->vendor->name ?? null,
                    'material_type' => $order->material_type,
                    'date' => $order->date,
                    'quantity' => $order->quantity,
                    'unit' => $order->unit,
                    'price' => $order->price,
                    'available_unit_count' => $order->available_unit_count,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

        $totalUnits = $bricks->sum('quantity');
        $totalAmount = $bricks->sum('price');

        $payments = MaterialPayment::where('site_id', $siteId)
            ->whereBetween('date', [$startDate, $endDate]);

        $settledAmount = $payments->sum('settled_amount');
        $pendingAmount = $payments->sum('pending_amount');

        $site = Site::find($siteId);
        $siteName = $site ? $site->site_name : 'Unknown Site';

        return response()->json([
            'siteId' => $siteId,
            'siteName' => $siteName,
            'bricks' => $bricks,
            'totalUnits' => $totalUnits,
            'totalAmount' => $totalAmount,
            'settledAmount' => $settledAmount,
            'pendingAmount' => $pendingAmount,
            
        ]);
    }
}
