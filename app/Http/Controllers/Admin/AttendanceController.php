<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Site;
use App\Models\Wages;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function index(Request $request, $siteId)
    {
        $site = Site::find($siteId);
        $siteName = $site ? $site->site_name : '';

        $month = $request->query('month') ?: Carbon::now()->format('Y-m');
        $week = $request->query('week');
        $date = $request->query('date');

        $attendanceQuery = Attendance::where('site_id', $siteId);
        $wageQuery = Wages::where('site_id', $siteId);

        $weekDays = [];
        $attendances = collect();
        $wages = $wageQuery->get();
        $groupedByDate = [];
        $categoryWages = [];
        $totalWages = 0;
        $workerCategoryTotals = [];
        $totalWorkers = 0;
        $allCategories = [];

        $startDate = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;
        $totalWeeks = (int) ceil($daysInMonth / 7);
        $availableWeeks = range(1, $totalWeeks);

        if ($week !== null) {
            $week = (int) $week;
            if ($week < 1 || $week > $totalWeeks) {
                $week = null;
            }
        }

        if ($week) {
            $weekStart = $startDate->copy()->addDays(($week - 1) * 7);
            $weekEnd = $weekStart->copy()->addDays(6);

            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
                $weekDays[] = [
                    'label' => $d->format('D d'),
                    'value' => $d->toDateString(),
                ];
            }

            if ($date) {
                $selectedDate = Carbon::parse($date);
                if ($selectedDate->betweenIncluded($weekStart, $weekEnd)) {
                    $attendances = $attendanceQuery->whereDate('date', $selectedDate->toDateString())->get();
                }
            } else {
                $attendances = $attendanceQuery
                    ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->get();
                $groupedByDate = $this->getGroupedAttendance($attendances, $wages, $allCategories);
            }
        } else {
            if ($date) {
                $selectedDate = Carbon::parse($date);
                if ($selectedDate->betweenIncluded($startDate, $endDate)) {
                    $attendances = $attendanceQuery->whereDate('date', $selectedDate->toDateString())->get();
                }
            } else {
                $attendances = $attendanceQuery
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get();
                $groupedByDate = $this->getGroupedAttendance($attendances, $wages, $allCategories);
            }
        }

        foreach ($attendances as $attendance) {
            $amount = $this->getApplicableWage($attendance->category, Carbon::parse($attendance->date), $wages);
            $workerCategoryTotals[$attendance->category] = ($workerCategoryTotals[$attendance->category] ?? 0) + $attendance->count;
            $totalWorkers += $attendance->count;
            $categoryWages[$attendance->category] = ($categoryWages[$attendance->category] ?? 0) + $attendance->count * $amount;
            $totalWages += $attendance->count * $amount;
            $allCategories[$attendance->category] = true;
        }

        $allCategories = array_keys($allCategories);

        return view('admin.menus.attendance.attendance_management', compact(
            'siteName',
            'siteId',
            'month',
            'week',
            'date',
            'weekDays',
            'attendances',
            'wages',
            'categoryWages',
            'workerCategoryTotals',
            'totalWorkers',
            'totalWages',
            'groupedByDate',
            'allCategories',
            'totalWeeks',
            'availableWeeks'
        ));
    }

// Helper: get latest wage for category up to a date
private function getApplicableWage($category, $attendanceDate, $wages)
{
    $filtered = $wages->where('category', $category)
                      ->where('date', '<=', $attendanceDate->toDateString())
                      ->sortByDesc('date');

    return $filtered->isNotEmpty() ? $filtered->first()->amount : 0;
}

// Helper: group attendance by date with calculated total
private function getGroupedAttendance($attendances, $wages, &$allCategories)
{
    return $attendances->groupBy('date')->map(function ($records, $day) use ($wages, &$allCategories) {
        $dayData = ['date' => $day, 'total' => 0];
        foreach ($records as $rec) {
            $amount = $this->getApplicableWage($rec->category, Carbon::parse($rec->date), $wages);
            $dayData[$rec->category] = ($dayData[$rec->category] ?? 0) + $rec->count;
            $dayData['total'] += $rec->count * $amount;
            $allCategories[$rec->category] = true;
        }
        return $dayData;
    })->values()->toArray();
}

   
    // 🟢 Add Wages
    public function addWages(Request $request)
    {
        // If rows[] provided (new UI): use top-level date when per-row date absent
        if ($request->has('rows')) {
            $validate = Validator::make($request->all(), [
                'site_id' => 'required|exists:sites,id',
                'date' => 'nullable|date',
                'rows' => 'array',
                'rows.*.category' => 'required|string',
                'rows.*.amount' => 'nullable|numeric',
                'rows.*.count' => 'nullable|numeric',
            ]);

            if ($validate->fails()) {
                return redirect()->back()->withErrors($validate)->withInput();
            }

            $redirectMonth = null;
            foreach ($request->input('rows') as $row) {
                $date = $row['date'] ?? $request->input('date') ?? null;
                if ($date && !$redirectMonth) {
                    $redirectMonth = Carbon::parse($date)->format('Y-m');
                }

                if (!empty($row['amount'])) {
                    Wages::create([
                        'site_id' => $request->site_id,
                        'category' => $row['category'],
                        'amount' => $row['amount'],
                        'date' => $date ?? Carbon::now()->toDateString(),
                        'created_by' => auth('admin')->id(),
                    ]);
                }

                if (!empty($row['count'])) {
                    Attendance::create([
                        'site_id' => $request->site_id,
                        'category' => $row['category'],
                        'count' => $row['count'],
                        'date' => $date ?? Carbon::now()->toDateString(),
                        'created_by' => auth('admin')->id(),
                    ]);
                }
            }

            return redirect()->route('attendance', [
                'siteId' => $request->site_id,
                'month' => $redirectMonth ?? Carbon::now()->format('Y-m'),
            ])->with('success', 'Wages added successfully!');
        }

        // Legacy form handling (per-category inputs)
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'date'     => 'required|date',
            'amount_Mason' => 'nullable|numeric',
            'amount_Helper' => 'nullable|numeric',
            'amount_Fitter' => 'nullable|numeric',
            'amount_Centring_Helper' => 'nullable|numeric',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $categories = [
            'Mason' => 'amount_Mason',
            'Helper' => 'amount_Helper',
            'Fitter' => 'amount_Fitter',
            'Centring Helper' => 'amount_Centring_Helper',
        ];

        foreach ($categories as $category => $amountField) {
            if ($request->filled($amountField)) {
                Wages::create([
                    'site_id'    => $request->site_id,
                    'category'   => $category,
                    'amount'     => $request->$amountField,
                    'date'       => $request->date,
                    'created_by' => auth('admin')->id(),
                ]);
            }
        }

        return redirect()->route('attendance', [
            'siteId' => $request->site_id,
            'month' => Carbon::parse($request->date)->format('Y-m'),
        ])->with('success', 'Wages added successfully!');
    }

    // 🟢 Add Attendance
    public function addAttendance(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id'  => 'required|exists:sites,id',
            'date'     => 'required|date',
            'count_Mason' => 'nullable|numeric',
            'count_Helper'   => 'nullable|numeric',
            'count_Fitter' => 'nullable|numeric',
            'count_Centring_Helper' => 'nullable|numeric',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $categories = [
            'Mason' => 'count_Mason',
            'Helper' => 'count_Helper',
            'Fitter' => 'count_Fitter',
            'Centring Helper' => 'count_Centring_Helper',
        ];

        foreach ($categories as $category => $countField) {
            if ($request->filled($countField)) {
                Attendance::create([
                    'site_id'     => $request->site_id,
                    'category'    => $category,
                    'count'       => $request->$countField,
                    'date'        => $request->date,
                    'created_by'  => auth('admin')->id(),
                ]);
            }
        }

        return redirect()->route('attendance', [
            'siteId' => $request->site_id,
            'month' => Carbon::parse($request->date)->format('Y-m'),
        ])->with('success', 'Attendance added successfully!');
    }


    public function getWagesForm($siteId)
    {
        return view('admin.menus.attendance.add_wages', compact('siteId'));
    }

    public function getAttendanceForm($siteId)
    {
        return view('admin.menus.attendance.add_attendance', compact('siteId'));
    }

    public function exportAttendance(Request $request, $siteId)
{
    $month = $request->query('month'); // format YYYY-MM
    $week = $request->query('week');   // optional week number
    $fromDate = $request->query('from_date');
    $toDate = $request->query('to_date');

    $fileName = 'Attendance_Site_'.$siteId;
    if ($fromDate || $toDate) {
        if ($fromDate) $fileName .= '_from_'.$fromDate;
        if ($toDate) $fileName .= '_to_'.$toDate;
    }
    if ($month) $fileName .= '_'.$month;
    if ($week) $fileName .= '_Week'.$week;
    $fileName .= '.xlsx';

    return Excel::download(new AttendanceExport($siteId, $month, $week, $fromDate, $toDate), $fileName);
}

public function editPage($site_id, $date)
{
    $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

    $attendance = Attendance::where('site_id', $site_id)
                            ->where('date', $date)
                            ->pluck('count', 'category');

                             // 1. Check if that date has wages
// 1. Check if wage exists for the given date
$wages = Wages::where('site_id', $site_id)
              ->where('date', $date)
              ->pluck('amount', 'category');


// 2. If NO wage on this date → use nearest previous wage
if ($wages->isEmpty()) {
    $wages = Wages::where('site_id', $site_id)
                  ->where('date', '<', $date)     // previous dates only
                  ->orderBy('date', 'desc')        // nearest previous
                  ->pluck('amount', 'category');
}


    // Provide the variable name your view expects
    $siteId = $site_id;

    return view('admin.menus.attendance.edit', compact('attendance', 'wages', 'siteId', 'date', 'categories'));
}


public function updateAttendance(Request $request)
{
    $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

    foreach ($categories as $cat) {

        $key = "count_" . str_replace(' ', '_', $cat);  // FIX

        $input = $request->input($key);

        if ($input === null || $input === '') {
            continue;
        }

        Attendance::updateOrCreate(
            [
                'site_id'  => $request->site_id,
                'date'     => $request->date,
                'category' => $cat,
            ],
            [
                'count' => $input,
                'created_by' => auth('admin')->id()
            ]
        );
    }

    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        $count = $row['count'] ?? null;

        if ($category === '' || $count === null || $count === '') {
            continue;
        }

        Attendance::updateOrCreate(
            [
                'site_id'  => $request->site_id,
                'date'     => $request->date,
                'category' => $category,
            ],
            [
                'count' => $count,
                'created_by' => auth('admin')->id()
            ]
        );
    }

    return redirect()->back()->with('success', 'Attendance updated successfully!');
}


public function updateWages(Request $request)
{
    $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

    foreach ($categories as $cat) {
         $key = "amount_" . str_replace(' ', '_', $cat);  // FIX

        $input = $request->input($key);

      

        // Do NOT overwrite if no value entered
        if ($input === null || $input === '') {
            continue;
        }

        Wages::updateOrCreate(
            [
                'site_id'  => $request->site_id,
                'date'     => $request->date,
                'category' => $cat,
            ],
            [
                'amount' => $input,
                'created_by' => auth('admin')->id()
            ]
        );
    }

    foreach ($request->input('wage_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        $amount = $row['amount'] ?? null;

        if ($category === '' || $amount === null || $amount === '') {
            continue;
        }

        Wages::updateOrCreate(
            [
                'site_id'  => $request->site_id,
                'date'     => $request->date,
                'category' => $category,
            ],
            [
                'amount' => $amount,
                'created_by' => auth('admin')->id()
            ]
        );
    }

    return redirect()->back()->with('success', 'Wages updated successfully!');
}

public function updateAttendanceAndWages(Request $request)
{
    $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

    foreach ($categories as $cat) {
        $countKey = "count_" . str_replace(' ', '_', $cat);
        $count = $request->input($countKey);

        if ($count !== null && $count !== '') {
            Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                [
                    'count' => $count,
                    'created_by' => auth('admin')->id()
                ]
            );
        }

        $amountKey = "amount_" . str_replace(' ', '_', $cat);
        $amount = $request->input($amountKey);

        if ($amount !== null && $amount !== '') {
            Wages::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                [
                    'amount' => $amount,
                    'created_by' => auth('admin')->id()
                ]
            );
        }
    }

    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        $count = $row['count'] ?? null;

        if ($category !== '' && $count !== null && $count !== '') {
            Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $category,
                ],
                [
                    'count' => $count,
                    'created_by' => auth('admin')->id()
                ]
            );
        }
    }

    foreach ($request->input('wage_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        $amount = $row['amount'] ?? null;

        if ($category !== '' && $amount !== null && $amount !== '') {
            Wages::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $category,
                ],
                [
                    'amount' => $amount,
                    'created_by' => auth('admin')->id()
                ]
            );
        }
    }

    return redirect()->route('attendance', [
            'siteId' => $request->site_id,
            'month'  => Carbon::parse($request->date)->format('Y-m'),
        ])
        ->with('success', 'Attendance and Wages updated successfully!');
}

    // Delete a single attendance record
    public function delete($id)
    {
        $attendance = Attendance::findOrFail($id);
        $siteId = $attendance->site_id;
        $date = $attendance->date;
        $attendance->delete();

        return redirect()->route('attendance', ['siteId' => $siteId, 'date' => $date])
            ->with('success', 'Attendance deleted successfully!');
    }

    // Delete all attendance records for a specific date (used from month view)
    public function deleteByDate($siteId, $date)
    {
        Attendance::where('site_id', $siteId)
            ->whereDate('date', $date)
            ->delete();

        return redirect()->route('attendance', [
            'siteId' => $siteId,
            'month' => Carbon::parse($date)->format('Y-m')
        ])->with('success', 'Attendance for the date deleted successfully!');
    }

}
