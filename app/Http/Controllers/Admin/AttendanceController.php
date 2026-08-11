<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCheckin;
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

        // Map of date => check-in/check-out photo & time (single source of truth: attendance_checkins)
        $dayPhotos = [];
        foreach (AttendanceCheckin::where('site_id', $siteId)->get() as $checkin) {
            $checkinDate = Carbon::parse($checkin->date)->toDateString();

            $dayPhotos[$checkinDate] = [
                'check_in_photo'  => $checkin->check_in_photo,
                'check_in_time'   => $checkin->check_in_time,
                'check_out_photo' => $checkin->check_out_photo,
                'check_out_time'  => $checkin->check_out_time,
            ];
        }

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
            'availableWeeks',
            'dayPhotos'
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
    return $attendances->sortBy('date')->groupBy('date')->map(function ($records, $day) use ($wages, &$allCategories) {
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
                'time' => 'nullable|date_format:H:i',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'rows' => 'array',
                'rows.*.category' => 'required|string',
                'rows.*.amount' => 'nullable|numeric|min:0',
                'rows.*.count' => 'nullable|integer|min:0',
            ]);

            if ($validate->fails()) {
                return redirect()->back()->withErrors($validate)->withInput();
            }

            // Don't silently duplicate — if attendance/wages already exist for a site/date/category
            // in these rows, stop and tell the admin to edit the existing entry instead.
            $duplicates = [];
            foreach ($request->input('rows') as $row) {
                $rowDate = $row['date'] ?? $request->input('date') ?? Carbon::now()->toDateString();
                $what = [];

                if (!empty($row['amount']) && Wages::where('site_id', $request->site_id)
                        ->where('date', $rowDate)->where('category', $row['category'])->exists()) {
                    $what[] = 'wages';
                }

                if (!empty($row['count']) && Attendance::where('site_id', $request->site_id)
                        ->where('date', $rowDate)->where('category', $row['category'])->exists()) {
                    $what[] = 'attendance';
                }

                if (!empty($what)) {
                    $duplicates[] = $row['category'] . ' ' . implode(' & ', $what) . ' (' . Carbon::parse($rowDate)->format('d-m-Y') . ')';
                }
            }

            if (!empty($duplicates)) {
                return redirect()->back()->withInput()->with(
                    'error',
                    implode(', ', $duplicates) . ' already added. Please edit instead.'
                );
            }

            $checkInPhoto = null;
            if ($request->hasFile('photo')) {
                $checkInPhoto = $request->file('photo')->store('wages_checkin', 'public');
            }

            $redirectMonth = null;
            $checkInDates = [];
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

                $checkInDates[$date ?? Carbon::now()->toDateString()] = true;
            }

            if ($request->filled('time') || $checkInPhoto) {
                foreach (array_keys($checkInDates) as $checkInDate) {
                    AttendanceCheckin::updateOrCreate(
                        [
                            'site_id' => $request->site_id,
                            'date'    => $checkInDate,
                        ],
                        array_filter([
                            'check_in_time'  => $request->time,
                            'check_in_photo' => $checkInPhoto,
                            'created_by'     => auth('admin')->id(),
                        ], fn ($v) => $v !== null)
                    );
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
            'time'     => 'nullable|date_format:H:i',
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'amount_Mason' => 'nullable|numeric|min:0',
            'amount_Helper' => 'nullable|numeric|min:0',
            'amount_Fitter' => 'nullable|numeric|min:0',
            'amount_Centring_Helper' => 'nullable|numeric|min:0',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $checkInPhoto = null;
        if ($request->hasFile('photo')) {
            $checkInPhoto = $request->file('photo')->store('wages_checkin', 'public');
        }

        $categories = [
            'Mason' => 'amount_Mason',
            'Helper' => 'amount_Helper',
            'Fitter' => 'amount_Fitter',
            'Centring Helper' => 'amount_Centring_Helper',
        ];

        // Don't silently duplicate — if a wage rate already exists for this site/date/category,
        // stop and tell the admin to edit the existing entry instead of creating a second one.
        $duplicates = [];
        foreach ($categories as $category => $amountField) {
            if ($request->filled($amountField)) {
                $exists = Wages::where('site_id', $request->site_id)
                    ->where('date', $request->date)
                    ->where('category', $category)
                    ->exists();

                if ($exists) {
                    $duplicates[] = $category;
                }
            }
        }

        if (!empty($duplicates)) {
            return redirect()->back()->withInput()->with(
                'error',
                'Wages for ' . implode(', ', $duplicates) . ' on ' . Carbon::parse($request->date)->format('d-m-Y')
                    . ' already added. Please edit instead.'
            );
        }

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

        if ($request->filled('time') || $checkInPhoto) {
            AttendanceCheckin::updateOrCreate(
                [
                    'site_id' => $request->site_id,
                    'date'    => $request->date,
                ],
                array_filter([
                    'check_in_time'  => $request->time,
                    'check_in_photo' => $checkInPhoto,
                    'created_by'     => auth('admin')->id(),
                ], fn ($v) => $v !== null)
            );
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
            'count_Mason' => 'nullable|integer|min:0',
            'count_Helper'   => 'nullable|integer|min:0',
            'count_Fitter' => 'nullable|integer|min:0',
            'count_Centring_Helper' => 'nullable|integer|min:0',
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

        // Don't silently duplicate — if attendance already exists for this site/date/category,
        // stop and tell the admin to edit the existing entry instead of creating a second one.
        $duplicates = [];
        foreach ($categories as $category => $countField) {
            if ($request->filled($countField)) {
                $exists = Attendance::where('site_id', $request->site_id)
                    ->where('date', $request->date)
                    ->where('category', $category)
                    ->exists();

                if ($exists) {
                    $duplicates[] = $category;
                }
            }
        }

        if (!empty($duplicates)) {
            return redirect()->back()->withInput()->with(
                'error',
                'Attendance for ' . implode(', ', $duplicates) . ' on ' . Carbon::parse($request->date)->format('d-m-Y')
                    . ' already added. Please edit instead.'
            );
        }

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

    // AJAX: check whether a date already has attendance/wages before the admin fills the form
    public function checkDate(Request $request, $siteId)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['exists' => false]);
        }

        $attendanceCategories = Attendance::where('site_id', $siteId)
            ->where('date', $date)
            ->pluck('category')
            ->unique()
            ->values();

        $wageCategories = Wages::where('site_id', $siteId)
            ->where('date', $date)
            ->pluck('category')
            ->unique()
            ->values();

        $categories = $attendanceCategories->merge($wageCategories)->unique()->sort()->values();

        return response()->json([
            'exists' => $categories->isNotEmpty(),
            'categories' => $categories,
            'date' => Carbon::parse($date)->format('d-m-Y'),
        ]);
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
    $attendance = Attendance::where('site_id', $site_id)
                            ->where('date', $date)
                            ->pluck('count', 'category');

    // 1. Check if wage exists for the given date
    $wagesForDate = Wages::where('site_id', $site_id)
                  ->where('date', $date)
                  ->pluck('amount', 'category');

    // 2. If NO wage on this date → use nearest previous wage (for pre-filling the rate only)
    $wages = $wagesForDate;
    if ($wages->isEmpty()) {
        $wages = Wages::where('site_id', $site_id)
                      ->where('date', '<', $date)     // previous dates only
                      ->orderBy('date', 'desc')        // nearest previous
                      ->pluck('amount', 'category');
    }

    // Only show categories actually recorded on THIS date — not the site's whole history,
    // so a date that only has e.g. "sithal" only shows "sithal".
    $categories = $attendance->keys()
        ->merge($wagesForDate->keys())
        ->filter()
        ->unique()
        ->sort()
        ->values();


    // Provide the variable name your view expects
    $siteId = $site_id;

    $checkOut = AttendanceCheckin::where('site_id', $site_id)
        ->where('date', $date)
        ->first();

    return view('admin.menus.attendance.edit', compact('attendance', 'wages', 'siteId', 'date', 'categories', 'checkOut'));
}


public function updateAttendance(Request $request)
{
    $validate = Validator::make($request->all(), [
        'count_Mason'            => 'nullable|integer|min:0',
        'count_Helper'           => 'nullable|integer|min:0',
        'count_Fitter'           => 'nullable|integer|min:0',
        'count_Centring_Helper'  => 'nullable|integer|min:0',
        'attendance_rows.*.count' => 'nullable|integer|min:0',
    ]);

    if ($validate->fails()) {
        return redirect()->back()->withErrors($validate)->withInput();
    }

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
    $validate = Validator::make($request->all(), [
        'amount_Mason'            => 'nullable|numeric|min:0',
        'amount_Helper'           => 'nullable|numeric|min:0',
        'amount_Fitter'           => 'nullable|numeric|min:0',
        'amount_Centring_Helper'  => 'nullable|numeric|min:0',
        'wage_rows.*.amount'      => 'nullable|numeric|min:0',
    ]);

    if ($validate->fails()) {
        return redirect()->back()->withErrors($validate)->withInput();
    }

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
    $rules = [
        'checkin_time'  => 'nullable|date_format:H:i',
        'checkin_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'time'  => 'nullable|date_format:H:i',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'attendance_rows.*.count' => 'nullable|integer|min:0',
        'wage_rows.*.amount'      => 'nullable|numeric|min:0',
    ];

    // Reject negative counts/amounts for the fixed categories too (count_Mason, amount_Fitter, ...)
    foreach ($request->input('categories', []) as $cat) {
        $key = str_replace(' ', '_', $cat);
        $rules["count_{$key}"]  = 'nullable|integer|min:0';
        $rules["amount_{$key}"] = 'nullable|numeric|min:0';
    }

    $validate = Validator::make($request->all(), $rules);

    if ($validate->fails()) {
        return redirect()->back()->withErrors($validate)->withInput();
    }

    $categories = $request->input('categories', []);

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

    // Check-in / check-out time & photo for this site/date
    if ($request->filled('checkin_time') || $request->hasFile('checkin_photo')
        || $request->filled('time') || $request->hasFile('photo')) {
        $checkInOutUpdate = [];

        if ($request->filled('checkin_time')) {
            $checkInOutUpdate['check_in_time'] = $request->checkin_time;
        }

        if ($request->hasFile('checkin_photo')) {
            $checkInOutUpdate['check_in_photo'] = $request->file('checkin_photo')->store('wages_checkin', 'public');
        }

        if ($request->filled('time')) {
            $checkInOutUpdate['check_out_time'] = $request->time;
        }

        if ($request->hasFile('photo')) {
            $checkInOutUpdate['check_out_photo'] = $request->file('photo')->store('wages_checkout', 'public');
        }

        AttendanceCheckin::updateOrCreate(
            [
                'site_id' => $request->site_id,
                'date'    => $request->date,
            ],
            $checkInOutUpdate + ['created_by' => auth('admin')->id()]
        );
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
        // Clear Attendance AND Wages (plus the check-in/out record) for this date —
        // leaving Wages behind caused the duplicate-entry check in addWages() to
        // still see the old rows and block re-adding after a "delete this date".
        Attendance::where('site_id', $siteId)
            ->whereDate('date', $date)
            ->delete();

        Wages::where('site_id', $siteId)
            ->whereDate('date', $date)
            ->delete();

        AttendanceCheckin::where('site_id', $siteId)
            ->whereDate('date', $date)
            ->delete();

        return redirect()->route('attendance', [
            'siteId' => $siteId,
            'month' => Carbon::parse($date)->format('Y-m')
        ])->with('success', 'Attendance for the date deleted successfully!');
    }

}
