<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialOrder;
use App\Models\MaterialPayment;
use App\Models\MaterialRequest;
use App\Models\Site;
use App\Models\Unit;
use Carbon\Carbon;

class BricksController extends Controller
{
    public function index(Request $request, $siteId)
    {
        // Inputs
        $monthYear = $request->query('monthYear', now()->format('Y-m')); // default: current month
        $week = (int) $request->query('week', 0); // default: 0 (full month)

        // Parse month range
        $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

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

    public function getBricksData(Request $request, $siteId)
    {
        $monthYear = $request->input('monthYear');
        $week = $request->input('week');

        $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $query = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        $weekStart = $startOfMonth;
        $weekEnd = $endOfMonth;

        if ($week > 0 && $week <= 4) {
            $weekStart = $startOfMonth->copy()->addDays(($week - 1) * 7);
            $weekEnd = $weekStart->copy()->addDays(6);
            if ($weekEnd > $endOfMonth) {
                $weekEnd = $endOfMonth;
            }

            $query->whereBetween('date', [$weekStart, $weekEnd]);
        }

        $bricks = $query->get();

        $totalUnits = $bricks->sum('quantity');
        $totalAmount = $bricks->sum('price');

        $paymentQuery = MaterialPayment::where('site_id', $siteId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        if ($week > 0 && $week <= 4) {
            $paymentQuery->whereBetween('date', [$weekStart, $weekEnd]);
        }

        $settledAmount = $paymentQuery->sum('settled_amount');
        $pendingAmount = $paymentQuery->sum('pending_amount');

        return response()->json([
            'status' => true,
            'message' => 'Bricks data fetched successfully.',
            'bricks' => $bricks,
            'totalUnits' => $totalUnits,
            'totalAmount' => $totalAmount,
            'settledAmount' => $settledAmount,
            'pendingAmount' => $pendingAmount,
        ]);
    }

    public function getRequestForm($siteId)
    {
        $site = Site::find($siteId);

        if (!$site) {
            return response()->json(['status' => false, 'message' => 'Site not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bricks request form details fetched successfully.',
            'data' => [
                'site_id' => $siteId,
                'material_type' => 'Bricks',
                'units' => Unit::orderBy('name')->pluck('name'),
                'delivery_needed_by_options' => ['Immediate', 'Later', 'One week'],
            ],
        ]);
    }

    public function getOrderForm($siteId)
    {
        $site = Site::find($siteId);

        if (!$site) {
            return response()->json(['status' => false, 'message' => 'Site not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bricks order form details fetched successfully.',
            'data' => [
                'site_id' => $siteId,
                'material_type' => 'Bricks',
                'units' => Unit::orderBy('name')->pluck('name'),
            ],
        ]);
    }

    public function getPayForm($siteId)
    {
        $site = Site::find($siteId);

        if (!$site) {
            return response()->json(['status' => false, 'message' => 'Site not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bricks payment form details fetched successfully.',
            'data' => [
                'site_id' => $siteId,
                'material_type' => 'Bricks',
            ],
        ]);
    }

    public function export(Request $request, $siteId)
    {
        $monthYear = $request->query('month') ?: now()->format('Y-m');
        $week = (int) $request->query('week');

        $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $startDate = $startOfMonth->copy();
        $endDate = $endOfMonth->copy();

        if ($week >= 1 && $week <= 4) {
            $weekStart = $startOfMonth->copy()->addDays(($week - 1) * 7);
            $weekEnd = $weekStart->copy()->addDays(6);
            if ($weekEnd->gt($endOfMonth)) $weekEnd = $endOfMonth;
            $startDate = $weekStart;
            $endDate = $weekEnd;
        }

        $bricks = MaterialOrder::with('vendor')
            ->where('site_id', $siteId)
            ->where('material_type', 'bricks')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $filename = sprintf('bricks_%s_%s.csv', $siteId, now()->format('Ymd_His'));
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['Date', 'Quantity', 'Vendor', 'Price'];

        $callback = function () use ($bricks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($bricks as $b) {
                fputcsv($file, [
                    $b->date,
                    $b->quantity,
                    optional($b->vendor)->name,
                    $b->price,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
