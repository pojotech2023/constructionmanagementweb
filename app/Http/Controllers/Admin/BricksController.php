<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialOrder;
use App\Models\MaterialPayment;
use App\Models\MaterialRequest;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BricksController extends Controller
{
  public function index(Request $request, $siteId)
  {
    $month = $request->query('month') ?: Carbon::now()->format('Y-m');
    $week = (int) $request->query('week');

    $startOfMonth = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    $startDate = $startOfMonth->copy();
    $endDate = $endOfMonth->copy();

    if ($week >= 1 && $week <= 4) {
      $startDate = $startOfMonth->copy()->addDays(($week - 1) * 7);
      $endDate = $startDate->copy()->addDays(6);

      if ($endDate->gt($endOfMonth)) {
        $endDate = $endOfMonth;
      }
    } else {
      $week = null;
    }

    // Bricks orders list
    $bricks = MaterialOrder::with('vendor')
      ->where('site_id', $siteId)
      ->whereBetween('date', [$startDate, $endDate])
      ->get();

    // Site Name
    $site = Site::find($siteId);
    $siteName = $site ? $site->site_name : 'Unknown Site';

    // Selected month total unit count from MaterialOrder
    $totalUnits = MaterialOrder::where('site_id', $siteId)
      ->whereBetween('date', [$startDate, $endDate])
      ->sum('quantity');

    // Selected month amount summary from MaterialPayment
    $totalAmount = MaterialOrder::where('site_id', $siteId)
      ->whereBetween('date', [$startDate, $endDate])
      ->sum('price');

    $settledAmount = MaterialPayment::where('site_id', $siteId)
      ->whereBetween('date', [$startDate, $endDate])
      ->sum('settled_amount');

    $pendingAmount = MaterialPayment::where('site_id', $siteId)
      ->whereBetween('date', [$startDate, $endDate])
      ->sum('pending_amount');

    return view('admin.menus.bricks.bricks_details', compact(
      'bricks',
      'siteId',
      'siteName',
      'totalAmount',
      'settledAmount',
      'pendingAmount',
      'totalUnits',
      'month',
      'week'
    ));
  }

  public function getBricksData(Request $request, $siteId)
{
    $monthYear = $request->input('monthYear'); // format: YYYY-MM
    $week = $request->input('week'); // 1, 2, 3, 4

    $startOfMonth = Carbon::createFromFormat('Y-m-d', $monthYear . '-01')->startOfMonth();
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    $query = MaterialOrder::with('vendor')
        ->where('site_id', $siteId)
        ->whereBetween('date', [$startOfMonth, $endOfMonth]);

    if ($week > 0 && $week <= 4) {
        $weekStart = $startOfMonth->copy()->addDays(($week - 1) * 7);
        $weekEnd = $weekStart->copy()->addDays(6);
        if ($weekEnd > $endOfMonth) {
            $weekEnd = $endOfMonth;
        }

        $query->whereBetween('date', [$weekStart, $weekEnd]);
    }

    $bricks = $query->get();

    // Summary
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
        'bricks' => $bricks,
        'totalUnits' => $totalUnits,
        'totalAmount' => $totalAmount,
        'settledAmount' => $settledAmount,
        'pendingAmount' => $pendingAmount
    ]);
}

  public function getRequestForm($siteId)
  {
    return view('admin.menus.bricks.add_request', compact('siteId'));
  }

  public function getOrderForm($siteId)
  {
    return view('admin.menus.bricks.add_order', compact('siteId'));
  }

  public function getPayForm($siteId)
  {
    return view('admin.menus.bricks.add_paydetails', compact('siteId'));
  }

  // Export bricks as CSV
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

    $bricks = \App\Models\MaterialOrder::with('vendor')
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



  // public function requestList($siteId)
  // {
  //   $requests = MaterialRequest::with('vendor')
  //     ->where('site_id', $siteId)
  //     ->where('material_type', 'Bricks')
  //     ->get();

  //   return view('admin.menus.bricks.request_list', compact('requests', 'siteId'));
  // }
}
