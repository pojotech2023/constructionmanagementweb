<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCheckin;
use App\Models\Wages;
use App\Models\Site;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function addWages(Request $request)
{
    $this->decodeJsonArrayField($request, 'attendance_rows');

    $validate = Validator::make($request->all(), [
        'time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'check_in_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'checkin_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'check_out_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'checkout_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'check_in_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'checkin_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'check_out_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'checkout_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'site_id' => 'required|exists:sites,id',
        'date' => 'required|date',
        'amount_mason' => 'nullable|numeric',
        'amount_helper' => 'nullable|numeric',
        'amount_fitter' => 'nullable|numeric',
        'amount_Centring_Helper' => 'nullable|numeric',
        'attendance_rows' => 'nullable|array',
        'attendance_rows.*.category' => 'required_with:attendance_rows|string|max:255',
        'attendance_rows.*.count' => 'nullable|numeric',
        'attendance_rows.*.amount' => 'nullable|numeric',
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors(),
        ], 422);
    }

    $categories = [
        'Mason' => 'amount_mason',
        'Helper' => 'amount_helper',
        'Fitter' => 'amount_fitter',
        'Centring Helper' => 'amount_Centring_Helper',
    ];

    // Don't silently duplicate — if a wage rate already exists for this site/date/category,
    // stop and tell the client to use /attendance/wages/update instead (matches web admin).
    $duplicates = [];
    foreach ($categories as $categoryName => $amountField) {
        if ($request->filled($amountField) && Wages::where('site_id', $request->site_id)
                ->where('date', $request->date)->where('category', $categoryName)->exists()) {
            $duplicates[] = $categoryName;
        }
    }
    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        if ($category === '') {
            continue;
        }
        $rowDate = $row['date'] ?? $request->date;
        if (($row['count'] ?? null) !== null && $row['count'] !== '' && Attendance::where('site_id', $request->site_id)
                ->where('date', $rowDate)->where('category', $category)->exists()) {
            $duplicates[] = $category . ' (attendance)';
        }
        if (($row['amount'] ?? null) !== null && $row['amount'] !== '' && Wages::where('site_id', $request->site_id)
                ->where('date', $rowDate)->where('category', $category)->exists()) {
            $duplicates[] = $category . ' (wages)';
        }
    }

    if (!empty($duplicates)) {
        return response()->json([
            'status' => false,
            'message' => 'Already added for ' . implode(', ', array_unique($duplicates)) . ' on this date. Please use update instead.',
        ], 422);
    }

    // Clean up any stale AttendanceCheckin from a previous deleted cycle
    $hasExisting = Attendance::where('site_id', $request->site_id)
        ->whereDate('date', $request->date)
        ->exists();
    if (!$hasExisting) {
        $staleCheckins = AttendanceCheckin::where('site_id', $request->site_id)
            ->whereDate('date', $request->date)
            ->get();
        foreach ($staleCheckins as $sc) {
            if ($sc->check_in_photo && Storage::disk('public')->exists($sc->check_in_photo)) {
                Storage::disk('public')->delete($sc->check_in_photo);
            }
            if ($sc->check_out_photo && Storage::disk('public')->exists($sc->check_out_photo)) {
                Storage::disk('public')->delete($sc->check_out_photo);
            }
            $sc->delete();
        }
    }

    foreach ($categories as $categoryName => $amountField) {
        if ($request->filled($amountField)) {
            Wages::create([
                'site_id' => $request->site_id,
                'category' => $categoryName,
                'amount' => $request->$amountField,
                'date' => $request->date,
                'created_by' => auth('api')->id(),
            ]);
        }
    }

    // Same free-text dynamic-category flow used by /add-attendance (attendance_rows[]):
    // each row can carry a count (-> Attendance) and/or an amount (-> Wages), and its own
    // date (falls back to the top-level date), matching the web admin's rows[] format.
    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');

        if ($category === '') {
            continue;
        }

        $rowDate = $row['date'] ?? $request->date;

        $count = $row['count'] ?? null;
        if ($count !== null && $count !== '') {
            Attendance::create([
                'site_id'  => $request->site_id,
                'date'     => $rowDate,
                'category' => $category,
                'count' => $count,
                'created_by' => auth('api')->id(),
            ]);
        }

        $amount = $row['amount'] ?? null;
        if ($amount !== null && $amount !== '') {
            Wages::create([
                'site_id'  => $request->site_id,
                'date'     => $rowDate,
                'category' => $category,
                'amount' => $amount,
                'created_by' => auth('api')->id(),
            ]);
        }
    }

    $rawCheckInTime = $request->input('check_in_time') ?? $request->input('checkin_time');
    if (($rawCheckInTime === null || $rawCheckInTime === '') && $request->filled('time')) {
        $rawCheckInTime = $request->input('time');
    }
    $checkInTime = $this->normalizeTime($rawCheckInTime);
    $checkInPhotoFile = $request->hasFile('photo') ? $request->file('photo') : ($request->hasFile('check_in_photo') ? $request->file('check_in_photo') : ($request->hasFile('checkin_photo') ? $request->file('checkin_photo') : null));

    $rawCheckOutTime = $request->input('check_out_time') ?? $request->input('checkout_time');
    $checkOutTime = $this->normalizeTime($rawCheckOutTime);
    $checkOutPhotoFile = $request->hasFile('check_out_photo') ? $request->file('check_out_photo') : ($request->hasFile('checkout_photo') ? $request->file('checkout_photo') : null);

    $checkinUpdate = array_filter([
        'check_in_time'   => $checkInTime,
        'check_in_photo'  => $checkInPhotoFile ? $checkInPhotoFile->store('wages_checkin', 'public') : null,
        'check_out_time'  => $checkOutTime,
        'check_out_photo' => $checkOutPhotoFile ? $checkOutPhotoFile->store('wages_checkout', 'public') : null,
    ], fn ($v) => $v !== null);

    if (!empty($checkinUpdate)) {
        AttendanceCheckin::updateOrCreate(
            [
                'site_id' => $request->site_id,
                'date'    => $request->date,
            ],
            $checkinUpdate + ['created_by' => auth('api')->id()]
        );
    }

    return response()->json([
        'response_code' => 200,
        'status' => true,
        'message' => 'Wages added successfully.',
    ]);
}

public function addAttendance(Request $request)
{
    $this->decodeJsonArrayField($request, 'attendance_rows');

    $validate = Validator::make($request->all(), [
        'site_id'  => 'required|exists:sites,id',
        'date'     => 'required|date',
        'time'     => 'nullable|date_format:H:i,H:i:s',
        'image'    => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'count_mason' => 'nullable|numeric',
        'count_helper' => 'nullable|numeric',
        'count_fitter' => 'nullable|numeric',
        'count_Centring_Helper' => 'nullable|numeric',
        'attendance_rows' => 'nullable|array',
        'attendance_rows.*.category' => 'required_with:attendance_rows|string|max:255',
        'attendance_rows.*.count' => 'required_with:attendance_rows|numeric',
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors()
        ], 422);
    }

    // Map database category names with request input fields
    $categories = [
        'Mason' => 'count_mason',
        'Helper' => 'count_helper',
        'Fitter' => 'count_fitter',
        'Centring Helper' => 'count_Centring_Helper',
    ];

    // Don't silently duplicate — if attendance already exists for this site/date/category,
    // stop and tell the client to use /attendance/update instead (matches web admin).
    $duplicates = [];
    foreach ($categories as $categoryName => $fieldName) {
        if ($request->filled($fieldName) && Attendance::where('site_id', $request->site_id)
                ->where('date', $request->date)->where('category', $categoryName)->exists()) {
            $duplicates[] = $categoryName;
        }
    }
    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        $count = $row['count'] ?? null;
        if ($category !== '' && $count !== null && $count !== '' && Attendance::where('site_id', $request->site_id)
                ->where('date', $request->date)->where('category', $category)->exists()) {
            $duplicates[] = $category;
        }
    }

    if (!empty($duplicates)) {
        return response()->json([
            'status' => false,
            'message' => 'Attendance for ' . implode(', ', array_unique($duplicates)) . ' on this date already added. Please use update instead.',
        ], 422);
    }

    // If no attendance exists for this site/date yet (fresh creation or re-entry after deletion),
    // clear any stale/orphan AttendanceCheckin or Wages left behind from previously deleted records
    $hasExisting = Attendance::where('site_id', $request->site_id)
        ->whereDate('date', $request->date)
        ->exists();

    if (!$hasExisting) {
        $staleCheckins = AttendanceCheckin::where('site_id', $request->site_id)
            ->whereDate('date', $request->date)
            ->get();
        foreach ($staleCheckins as $sc) {
            if ($sc->check_in_photo && Storage::disk('public')->exists($sc->check_in_photo)) {
                Storage::disk('public')->delete($sc->check_in_photo);
            }
            if ($sc->check_out_photo && Storage::disk('public')->exists($sc->check_out_photo)) {
                Storage::disk('public')->delete($sc->check_out_photo);
            }
            $sc->delete();
        }

        $staleWages = Wages::where('site_id', $request->site_id)
            ->whereDate('date', $request->date)
            ->get();
        foreach ($staleWages as $sw) {
            if ($sw->check_in_photo && Storage::disk('public')->exists($sw->check_in_photo)) {
                Storage::disk('public')->delete($sw->check_in_photo);
            }
            if ($sw->check_out_photo && Storage::disk('public')->exists($sw->check_out_photo)) {
                Storage::disk('public')->delete($sw->check_out_photo);
            }
            $sw->delete();
        }
    }

    $imageUrl = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('attendance_images', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    $savedCount = 0;

    foreach ($categories as $categoryName => $fieldName) {
        if ($request->filled($fieldName)) {
            Attendance::create([
                'site_id'  => $request->site_id,
                'date'     => $request->date,
                'category' => $categoryName,
                'count' => $request->$fieldName,
                'time' => $request->time,
                'image_url' => $imageUrl,
                'created_by' => auth('api')->id(),
            ]);
            $savedCount++;
        }
    }

    // Same free-text dynamic-category flow the web app uses (attendance_rows[])
    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');
        $count = $row['count'] ?? null;

        if ($category !== '' && $count !== null && $count !== '') {
            Attendance::create([
                'site_id'  => $request->site_id,
                'date'     => $request->date,
                'category' => $category,
                'count' => $count,
                'time' => $request->time,
                'image_url' => $imageUrl,
                'created_by' => auth('api')->id(),
            ]);
            $savedCount++;
        }
    }

    if ($savedCount === 0) {
        return response()->json([
            'response code' => 422,
            'status' => false,
            'message' => 'No attendance categories were provided to save.',
        ], 422);
    }

    return response()->json([
        'response code' => 200,
        'status' => true,
        'message' => 'Attendance added successfully.',
    ]);
}

     public function index(Request $request, $siteId)
    {
        $site = Site::find($siteId);
        $siteName = $site ? $site->site_name : '';

        $month = $request->query('month'); // YYYY-MM
        $week  = $request->query('week');  // 1,2,3
        $date  = $request->query('date');  // YYYY-MM-DD

        $attendanceQuery = Attendance::where('site_id', $siteId);

        $weekDays      = [];
        $attendances   = collect();
        $groupedByDate = [];
        $categoryWages = [];
        $totalWages    = 0;
        $allCategories = [];
        $totalWeeks    = 0;

        /* =======================
           MONTH / WEEK / DATE FILTER
        ========================*/
        if ($month) {

            $startDate = Carbon::parse($month . '-01');
            $endDate   = $startDate->copy()->endOfMonth();

            $calendarStart = $startDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
            $calendarEnd   = $startDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

            $totalDays  = $calendarStart->diffInDays($calendarEnd) + 1;
            $totalWeeks = ceil($totalDays / 7);

            if ($week) {
                $weekStart = $calendarStart->copy()->addDays(($week - 1) * 7);
                $weekEnd   = $weekStart->copy()->addDays(6);

                if ($date) {
                    $attendances = $attendanceQuery
                        ->whereDate('date', $date)
                        ->get();
                } else {
                    $attendances = $attendanceQuery
                        ->whereBetween('date', [$weekStart, $weekEnd])
                        ->get();
                }

                for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
                    $weekDays[] = [
                        'label' => $d->format('D d'),
                        'value' => $d->toDateString(),
                    ];
                }

            } else {
                // Entire month
                $attendances = $attendanceQuery
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
            }

        } elseif ($date) {
            $date = Carbon::parse($date)->toDateString();
            $attendances = $attendanceQuery
                ->whereDate('date', $date)
                ->get();
        } else {
            // Default today
            $date = Carbon::today()->toDateString();
            $attendances = $attendanceQuery
                ->whereDate('date', $date)
                ->get();
        }

        /* =======================
           CHECK-IN / CHECK-OUT PHOTOS (single source of truth: attendance_checkins, per date)
        ========================*/
        $dayPhotos = [];
        $allCheckins = AttendanceCheckin::where('site_id', $siteId)->get();

        foreach ($allCheckins as $checkin) {
            $checkinDate = Carbon::parse($checkin->date)->toDateString();

            $dayPhotos[$checkinDate] = [
                'check_in_time'   => $checkin->check_in_time,
                'check_in_photo'  => $checkin->check_in_photo ? asset('storage/' . $checkin->check_in_photo) : null,
                'check_out_time'  => $checkin->check_out_time,
                'check_out_photo' => $checkin->check_out_photo ? asset('storage/' . $checkin->check_out_photo) : null,
            ];
        }

        // Fallback: check Wages table for any check-in/out times/photos if missing in AttendanceCheckin
        $allWagesWithCheckin = Wages::where('site_id', $siteId)
            ->where(function ($q) {
                $q->whereNotNull('check_in_time')
                  ->orWhereNotNull('check_in_photo')
                  ->orWhereNotNull('check_out_time')
                  ->orWhereNotNull('check_out_photo');
            })
            ->get();

        foreach ($allWagesWithCheckin as $w) {
            $wDate = Carbon::parse($w->date)->toDateString();
            if (!isset($dayPhotos[$wDate])) {
                $dayPhotos[$wDate] = [
                    'check_in_time'   => $w->check_in_time,
                    'check_in_photo'  => $w->check_in_photo ? asset('storage/' . $w->check_in_photo) : null,
                    'check_out_time'  => $w->check_out_time,
                    'check_out_photo' => $w->check_out_photo ? asset('storage/' . $w->check_out_photo) : null,
                ];
            } else {
                if (empty($dayPhotos[$wDate]['check_in_time']) && !empty($w->check_in_time)) {
                    $dayPhotos[$wDate]['check_in_time'] = $w->check_in_time;
                }
                if (empty($dayPhotos[$wDate]['check_in_photo']) && !empty($w->check_in_photo)) {
                    $dayPhotos[$wDate]['check_in_photo'] = asset('storage/' . $w->check_in_photo);
                }
                if (empty($dayPhotos[$wDate]['check_out_time']) && !empty($w->check_out_time)) {
                    $dayPhotos[$wDate]['check_out_time'] = $w->check_out_time;
                }
                if (empty($dayPhotos[$wDate]['check_out_photo']) && !empty($w->check_out_photo)) {
                    $dayPhotos[$wDate]['check_out_photo'] = asset('storage/' . $w->check_out_photo);
                }
            }
        }

        /* =======================
           GROUPED BY DATE (OLD STRUCTURE + EXTRA WAGE FIELD)
        ========================*/
        $groupedByDate = $attendances->groupBy('date')->map(function ($records, $day) use ($siteId, &$allCategories, $dayPhotos) {

            $dayData = [
                'date'  => $day,
                'total' => 0,
            ];

            foreach ($records as $rec) {

                $categoryKey = (string) Str::of($rec->category)
                    ->lower()
                    ->replace(' ', '_');

                $amount = $this->getApplicableWage($siteId, $rec->category, $rec->date);

                // 🔹 OLD: count (do not break mobile)
                $dayData[$categoryKey] = ($dayData[$categoryKey] ?? 0) + $rec->count;

                // 🔹 NEW: per-category wage
                $dayData[$categoryKey . '_wage'] = $amount;

                // 🔹 OLD: total wages
                $dayData['total'] += ($rec->count * $amount);

                $allCategories[$categoryKey] = true;
            }

            // 🔹 NEW: check-in/check-out time & photo for this date
            $photos = $dayPhotos[$day] ?? [
                'check_in_time'   => null,
                'check_in_photo'  => null,
                'check_out_time'  => null,
                'check_out_photo' => null,
            ];
            $dayData['check_in_time']   = $photos['check_in_time'];
            $dayData['check_in_photo']  = $photos['check_in_photo'];
            $dayData['check_out_time']  = $photos['check_out_time'];
            $dayData['check_out_photo'] = $photos['check_out_photo'];

            return $dayData;

        })->values()->toArray();

        $allCategories = array_keys($allCategories);

        /* =======================
           SUMMARY TOTALS (UNCHANGED)
        ========================*/
        foreach ($attendances as $attendance) {

            $categoryKey = (string) Str::of($attendance->category)
                ->lower()
                ->replace(' ', '_');

            $amount = $this->getApplicableWage($siteId, $attendance->category, $attendance->date);

            $categoryWages[$categoryKey] = ($categoryWages[$categoryKey] ?? 0)
                + ($attendance->count * $amount);

            $totalWages += ($attendance->count * $amount);
        }

        /* =======================
           WAGE MASTER DATA (OPTIONAL)
        ========================*/
        $wageData = Wages::where('site_id', $siteId)
            ->orderBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = (string) Str::of($item->category)
                    ->lower()
                    ->replace(' ', '_');

                return [$key => $item->amount];
            })
            ->toArray();

        // 🔹 Flat check-in/check-out for the selected single date (defaults to today when no month/week given)
        $selectedDayPhotos = $dayPhotos[$date] ?? [
            'check_in_time'   => null,
            'check_in_photo'  => null,
            'check_out_time'  => null,
            'check_out_photo' => null,
        ];

        /* =======================
           FINAL RESPONSE
        ========================*/
        return response()->json([
            'site_name'       => $siteName,
            'site_id'         => $siteId,
            'month'           => $month,
            'week'            => $week,
            'date'            => $date,
            'total_weeks'     => $totalWeeks,
            'week_days'       => $weekDays,
            'attendances'     => $attendances,
            'grouped_by_date' => $groupedByDate,
            'all_categories'  => $allCategories,
            'category_wages'  => $categoryWages,
            'total_wages'     => $totalWages,
            'wage'            => $wageData,
            'check_in_time'   => $selectedDayPhotos['check_in_time'],
            'check_in_photo'  => $selectedDayPhotos['check_in_photo'],
            'check_out_time'  => $selectedDayPhotos['check_out_time'],
            'check_out_photo' => $selectedDayPhotos['check_out_photo'],
            'day_photos'      => $dayPhotos,
        ]);
    }
private function normalizeTime($value): ?string
{
    if (!$value) {
        return null;
    }

    $str = is_string($value) ? trim($value) : (string) $value;
    if ($str === '' || strtolower($str) === 'null') {
        return null;
    }

    try {
        return Carbon::parse($str)->format('H:i:s');
    } catch (\Exception $e) {
        return null;
    }
}

private function getApplicableWage($siteId, $category, $attendanceDate)
{
    return Wages::where('site_id', $siteId)
        ->where('category', $category)
        ->whereDate('date', '<=', $attendanceDate)
        ->orderBy('date', 'desc')
        ->value('amount');
}
public function attendanceByDate(Request $request, $siteId)
{
    $date = $request->query('date');
    $fromDate = $request->query('from_date');
    $toDate = $request->query('to_date');

    if (!$date && !($fromDate && $toDate)) {
        return response()->json([
            'status'  => false,
            'message' => 'Date is required (YYYY-MM-DD), or provide from_date and to_date for a range.'
        ], 422);
    }

    $wages = Wages::where('site_id', $siteId)
        ->orderBy('category')
        ->get();

    $categories = [];
    foreach ($wages as $wage) {
        $categoryKey = (string) Str::of($wage->category)
            ->lower()
            ->replace(' ', '_');

        $categories[$categoryKey] = $categoryKey;
    }

    // 🔹 Week/range view
    if ($fromDate && $toDate) {
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);

        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = $this->buildDayData($siteId, $day->format('Y-m-d'), $categories);
        }

        return response()->json([
            'status'      => true,
            'site_id'     => (string) $siteId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
            'data'        => $days,
            'categories'  => array_values($categories),
            'total_wages' => array_sum(array_column($days, 'total')),
        ]);
    }

    // 🔹 Single day view
    $dayData = $this->buildDayData($siteId, $date, $categories);

    return response()->json([
        'status'      => true,
        'site_id'     => (string) $siteId,
        'date'        => $date,
        'data'        => $dayData,
        'categories'  => array_values($categories),
        'total_wages' => $dayData['total'],
    ]);
}

private function buildDayData($siteId, $date, $categories)
{
    $dayData = [
        'date'  => $date,
        'total' => 0,
    ];

    // 🔹 Default all categories (count=0, wage=0)
    foreach ($categories as $categoryKey) {
        $dayData[$categoryKey] = 0;
        $dayData[$categoryKey . '_wage'] = "0"; // ✅ IMPORTANT
    }

    /* =======================
       ATTENDANCE DATA
    ========================*/
    $attendances = Attendance::where('site_id', $siteId)
        ->whereDate('date', $date)
        ->get();

    foreach ($attendances as $rec) {

        $categoryKey = (string) Str::of($rec->category)
            ->lower()
            ->replace(' ', '_');

        $amount = $this->getApplicableWage(
            $siteId,
            $rec->category,
            $rec->date
        );

        // count
        $dayData[$categoryKey] += $rec->count;

        // 🔹 ONLY IF attendance exists → show wage
        if ($rec->count > 0) {
            $dayData[$categoryKey . '_wage'] = (string) $amount;
        }

        // total
        $dayData['total'] += ($rec->count * $amount);
    }

    return $dayData;
}


     public function apiAttendanceExport(Request $request)
    {
        // Validate inputs
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'month' => 'nullable|date_format:Y-m',
            'week' => 'nullable|integer|min:1|max:5',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $siteId = $request->site_id;
        $month = $request->month;
        $week = $request->week;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Generate file name
        $fileName = 'attendance_' . $siteId . '_' . time() . '.xlsx';
        $filePath = 'exports/' . $fileName;

        try {
            // Generate and store Excel file
            Excel::store(new AttendanceExport($siteId, $month, $week, $fromDate, $toDate), $filePath, 'public');

            // Create public download URL
           $downloadUrl = asset('storage/' . $filePath);


            return response()->json([
                'status' => true,
                'message' => 'Attendance Excel file generated successfully.',
                'download_url' => $downloadUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate Excel: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function editPage(Request $request)
{
    $request->validate([
        'site_id' => 'required',
        'date'    => 'required|date',
    ]);

    $site_id = $request->site_id;
    $date    = $request->date;

    // Attendance
    $attendance = Attendance::where('site_id', $site_id)
                            ->where('date', $date)
                            ->pluck('count', 'category');

    // Convert attendance keys
    $formattedAttendance = [];
    foreach ($attendance as $key => $value) {
        $newKey = strtolower(str_replace(' ', '_', $key));
        $formattedAttendance[$newKey] = $value;
    }

    // Wages recorded on this exact date (used below to scope categories, matches web admin)
    $wagesForDate = Wages::where('site_id', $site_id)
                  ->where('date', $date)
                  ->pluck('amount', 'category');

    // If NO wage on this date -> use nearest previous wage (for pre-filling the rate only)
    $wages = $wagesForDate;
    if ($wages->isEmpty()) {
        $wages = Wages::where('site_id', $site_id)
                      ->where('date', '<', $date)
                      ->orderBy('date', 'desc')
                      ->pluck('amount', 'category');
    }

    // Convert wages keys
    $formattedWages = [];
    foreach ($wages as $key => $value) {
        $newKey = strtolower(str_replace(' ', '_', $key));
        $formattedWages[$newKey] = $value;
    }

    // Only categories actually recorded on THIS date — not the site's whole history,
    // so a date that only has e.g. "sithal" only shows "sithal" (matches web admin).
    $categories = $attendance->keys()
        ->merge($wagesForDate->keys())
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $checkOut = AttendanceCheckin::where('site_id', $site_id)
        ->where('date', $date)
        ->first();

    $wageCheck = null;
    if (!$checkOut || empty($checkOut->check_out_time) || empty($checkOut->check_in_time)) {
        $wageCheck = Wages::where('site_id', $site_id)
            ->where('date', $date)
            ->where(function ($q) {
                $q->whereNotNull('check_in_time')
                  ->orWhereNotNull('check_in_photo')
                  ->orWhereNotNull('check_out_time')
                  ->orWhereNotNull('check_out_photo');
            })
            ->first();
    }

    $resCheckInTime = $checkOut->check_in_time ?? ($wageCheck->check_in_time ?? null);
    $resCheckInPhoto = ($checkOut && $checkOut->check_in_photo) ? asset('storage/' . $checkOut->check_in_photo) : (($wageCheck && $wageCheck->check_in_photo) ? asset('storage/' . $wageCheck->check_in_photo) : null);
    $resCheckOutTime = $checkOut->check_out_time ?? ($wageCheck->check_out_time ?? null);
    $resCheckOutPhoto = ($checkOut && $checkOut->check_out_photo) ? asset('storage/' . $checkOut->check_out_photo) : (($wageCheck && $wageCheck->check_out_photo) ? asset('storage/' . $wageCheck->check_out_photo) : null);

    return response()->json([
        'success'    => true,
        'site_id'    => $site_id,
        'date'       => $date,
        'categories' => $categories,
        'attendance' => $formattedAttendance,
        'wages'      => $formattedWages,
        'check_in_time'   => $resCheckInTime,
        'check_in_photo'  => $resCheckInPhoto,
        'check_out_time'  => $resCheckOutTime,
        'check_out_photo' => $resCheckOutPhoto,
    ]);
}


    public function updateAttendance(Request $request)
    {
        $this->decodeJsonArrayField($request, 'attendance_rows');

        $request->validate([
            'site_id' => 'required',
            'date'    => 'required|date',
            'time'    => 'nullable|date_format:H:i,H:i:s',
            'image'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'attendance_rows' => 'nullable|array',
            'attendance_rows.*.category' => 'required_with:attendance_rows|string|max:255',
            'attendance_rows.*.count' => 'required_with:attendance_rows|numeric',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('attendance_images', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];
        $affected = [];

        foreach ($categories as $cat) {

            $key = "count_" . str_replace(' ', '_', $cat);

            $input = $request->input($key);

            if ($input === null || $input === '') {
                continue;
            }

            $updateData = [
                'count'      => $input,
                'created_by' => $request->admin_id ?? 1, // mobile will send admin_id
            ];

            if ($request->filled('time')) {
                $updateData['time'] = $request->time;
            }

            if ($imageUrl) {
                $updateData['image_url'] = $imageUrl;
            }

            $record = Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                $updateData
            );
            $affected[] = ['category' => $cat, 'id' => $record->id, 'created' => $record->wasRecentlyCreated];
        }

        // Same free-text dynamic-category flow the web app uses (attendance_rows[])
        foreach ($request->input('attendance_rows', []) as $row) {
            $category = trim($row['category'] ?? '');
            $count = $row['count'] ?? null;

            if ($category === '' || $count === null || $count === '') {
                continue;
            }

            $updateData = [
                'count'      => $count,
                'created_by' => $request->admin_id ?? 1,
            ];

            if ($request->filled('time')) {
                $updateData['time'] = $request->time;
            }

            if ($imageUrl) {
                $updateData['image_url'] = $imageUrl;
            }

            $record = Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $category,
                ],
                $updateData
            );
            $affected[] = ['category' => $category, 'id' => $record->id, 'created' => $record->wasRecentlyCreated];
        }

        if (empty($affected)) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance categories were provided to update.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully!',
            'affected' => $affected,
        ]);
    }

    public function updateWages(Request $request)
    {
        $this->decodeJsonArrayField($request, 'wage_rows');

        $request->validate([
            'site_id' => 'required',
            'date'    => 'required|date',
        ]);

        $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];
        $affected = [];

        foreach ($categories as $cat) {

            $key = "amount_" . str_replace(' ', '_', $cat);

            $input = $request->input($key);

            if ($input === null || $input === '') {
                continue;
            }

            $record = Wages::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                [
                    'amount'     => $input,
                    'created_by' => $request->admin_id ?? 1,
                ]
            );
            $affected[] = ['category' => $cat, 'id' => $record->id, 'created' => $record->wasRecentlyCreated];
        }

        // Same free-text dynamic-category flow the web app uses (wage_rows[])
        foreach ($request->input('wage_rows', []) as $row) {
            $category = trim($row['category'] ?? '');
            $amount = $row['amount'] ?? null;

            if ($category === '' || $amount === null || $amount === '') {
                continue;
            }

            $record = Wages::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $category,
                ],
                [
                    'amount'     => $amount,
                    'created_by' => $request->admin_id ?? 1,
                ]
            );
            $affected[] = ['category' => $category, 'id' => $record->id, 'created' => $record->wasRecentlyCreated];
        }

        if (empty($affected)) {
            return response()->json([
                'success' => false,
                'message' => 'No wage categories were provided to update.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wages updated successfully!',
            'affected' => $affected,
        ]);
    }


    public function deleteWages(Request $request)
    {
        $request->validate([
            'site_id' => 'required',
            'date'    => 'required|date',
        ]);

        $query = Wages::where('site_id', $request->site_id)
            ->whereDate('date', $request->date);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $deleted = $query->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'No wages record found to delete.',
            ], 404);
        }

        $parsedDate = Carbon::parse($request->date)->toDateString();
        $remainingWages = Wages::where('site_id', $request->site_id)->whereDate('date', $parsedDate)->count();
        $remainingAttendance = Attendance::where('site_id', $request->site_id)->whereDate('date', $parsedDate)->count();

        if ($remainingWages === 0 && $remainingAttendance === 0) {
            $checkins = AttendanceCheckin::where('site_id', $request->site_id)
                ->whereDate('date', $parsedDate)
                ->get();
            foreach ($checkins as $checkin) {
                if ($checkin->check_in_photo && Storage::disk('public')->exists($checkin->check_in_photo)) {
                    Storage::disk('public')->delete($checkin->check_in_photo);
                }
                if ($checkin->check_out_photo && Storage::disk('public')->exists($checkin->check_out_photo)) {
                    Storage::disk('public')->delete($checkin->check_out_photo);
                }
                $checkin->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Wages deleted successfully!',
        ]);
    }

    // AJAX-style: check whether a date already has attendance/wages before the client fills the form
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

    // Delete all attendance records for a specific date (used from month view)
    public function deleteByDate($siteId, $date)
    {
        $parsedDate = Carbon::parse($date)->toDateString();

        // 1. Delete associated check-in / check-out records and files
        $checkins = AttendanceCheckin::where('site_id', $siteId)
            ->whereDate('date', $parsedDate)
            ->get();

        foreach ($checkins as $checkin) {
            if ($checkin->check_in_photo && Storage::disk('public')->exists($checkin->check_in_photo)) {
                Storage::disk('public')->delete($checkin->check_in_photo);
            }
            if ($checkin->check_out_photo && Storage::disk('public')->exists($checkin->check_out_photo)) {
                Storage::disk('public')->delete($checkin->check_out_photo);
            }
            $checkin->delete();
        }

        // 2. Delete Wages records and files for this site and date
        $wages = Wages::where('site_id', $siteId)
            ->whereDate('date', $parsedDate)
            ->get();

        foreach ($wages as $wage) {
            if ($wage->check_in_photo && Storage::disk('public')->exists($wage->check_in_photo)) {
                Storage::disk('public')->delete($wage->check_in_photo);
            }
            if ($wage->check_out_photo && Storage::disk('public')->exists($wage->check_out_photo)) {
                Storage::disk('public')->delete($wage->check_out_photo);
            }
            $wage->delete();
        }

        // 3. Delete Attendance records for this site and date
        $deleted = Attendance::where('site_id', $siteId)
            ->whereDate('date', $parsedDate)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Attendance for the date deleted successfully.',
            'deleted' => $deleted,
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'status'  => false,
                'message' => 'Attendance record not found.',
            ], 404);
        }

        $siteId = $attendance->site_id;
        $date = Carbon::parse($attendance->date)->toDateString();

        $attendance->delete();

        // Check if any attendance records remain for this site and date
        $remaining = Attendance::where('site_id', $siteId)
            ->whereDate('date', $date)
            ->count();

        if ($remaining === 0) {
            $checkins = AttendanceCheckin::where('site_id', $siteId)
                ->whereDate('date', $date)
                ->get();
            foreach ($checkins as $checkin) {
                if ($checkin->check_in_photo && Storage::disk('public')->exists($checkin->check_in_photo)) {
                    Storage::disk('public')->delete($checkin->check_in_photo);
                }
                if ($checkin->check_out_photo && Storage::disk('public')->exists($checkin->check_out_photo)) {
                    Storage::disk('public')->delete($checkin->check_out_photo);
                }
                $checkin->delete();
            }

            $wages = Wages::where('site_id', $siteId)
                ->whereDate('date', $date)
                ->get();
            foreach ($wages as $wage) {
                if ($wage->check_in_photo && Storage::disk('public')->exists($wage->check_in_photo)) {
                    Storage::disk('public')->delete($wage->check_in_photo);
                }
                if ($wage->check_out_photo && Storage::disk('public')->exists($wage->check_out_photo)) {
                    Storage::disk('public')->delete($wage->check_out_photo);
                }
                $wage->delete();
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Attendance record deleted successfully.',
        ]);
    }

    public function updateAttendanceAndWages(Request $request)
{
    $this->decodeJsonArrayField($request, 'attendance_rows');
    $this->decodeJsonArrayField($request, 'wage_rows');

    $request->validate([
        'site_id'         => 'required',
        'date'            => 'required|date',
        'time'            => 'nullable|string',
        'check_in_time'   => 'nullable|string',
        'checkin_time'    => 'nullable|string',
        'check_out_time'  => 'nullable|string',
        'checkout_time'   => 'nullable|string',
        'image'           => 'nullable',
        'photo'           => 'nullable',
        'check_in_photo'  => 'nullable',
        'checkin_photo'   => 'nullable',
        'check_out_photo' => 'nullable',
        'checkout_photo'  => 'nullable',
    ]);

    $adminId = $request->admin_id ?? 1; // mobile will send admin_id

    // If no attendance exists for this site/date before this update (fresh create or re-entry after deletion),
    // clear any stale/orphan AttendanceCheckin or Wages left behind from previous records
    $hadExistingAttendance = Attendance::where('site_id', $request->site_id)
        ->whereDate('date', $request->date)
        ->exists();

    if (!$hadExistingAttendance) {
        $staleCheckins = AttendanceCheckin::where('site_id', $request->site_id)
            ->whereDate('date', $request->date)
            ->get();
        foreach ($staleCheckins as $sc) {
            if ($sc->check_in_photo && Storage::disk('public')->exists($sc->check_in_photo)) {
                Storage::disk('public')->delete($sc->check_in_photo);
            }
            if ($sc->check_out_photo && Storage::disk('public')->exists($sc->check_out_photo)) {
                Storage::disk('public')->delete($sc->check_out_photo);
            }
            $sc->delete();
        }
    }

    // Dynamic category list (matches web admin's 'categories[]'), falls back to the fixed set
    $categories = $request->input('categories') ?: ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

    $imageUrl = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('attendance_images', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    foreach ($categories as $cat) {

        // ---------- ATTENDANCE ----------
        $countKey = "count_" . str_replace(' ', '_', $cat);
        $countInput = $request->input($countKey);

        if ($countInput !== null && $countInput !== '') {
            $attendanceData = [
                'count'      => $countInput,
                'created_by' => $adminId,
            ];

            if ($request->filled('time')) {
                $attendanceData['time'] = $request->time;
            }

            if ($imageUrl) {
                $attendanceData['image_url'] = $imageUrl;
            }

            Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                $attendanceData
            );
        }

        // ---------- WAGES ----------
        $amountKey = "amount_" . str_replace(' ', '_', $cat);
        $amountInput = $request->input($amountKey);

        if ($amountInput !== null && $amountInput !== '') {
            Wages::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                [
                    'amount'     => $amountInput,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // Same free-text dynamic-category flow the web app uses (attendance_rows[] / wage_rows[]).
    // Each attendance_rows[] entry may carry both a count (-> Attendance) and an amount
    // (-> Wages) in the same row, matching the combined-row shape used by addWages/addAttendance.
    foreach ($request->input('attendance_rows', []) as $row) {
        $category = trim($row['category'] ?? '');

        if ($category === '') {
            continue;
        }

        $count = $row['count'] ?? null;
        if ($count !== null && $count !== '') {
            Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $category,
                ],
                [
                    'count' => $count,
                    'created_by' => $adminId,
                ]
            );
        }

        $amount = $row['amount'] ?? null;
        if ($amount !== null && $amount !== '') {
            Wages::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $category,
                ],
                [
                    'amount' => $amount,
                    'created_by' => $adminId,
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
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // --- Check-in Time & Photo ---
    $rawCheckInTime = $request->input('check_in_time') ?? $request->input('checkin_time');
    $checkInTime = $this->normalizeTime($rawCheckInTime);

    $checkInPhotoFile = null;
    if ($request->hasFile('check_in_photo')) {
        $checkInPhotoFile = $request->file('check_in_photo');
    } elseif ($request->hasFile('checkin_photo')) {
        $checkInPhotoFile = $request->file('checkin_photo');
    }

    // --- Check-out Time & Photo ---
    // Make check_out_time completely independent of check_out_photo.
    // Explicit checkout time field takes highest priority.
    $rawCheckOutTime = $request->input('check_out_time');
    if ($rawCheckOutTime === null || $rawCheckOutTime === '' || $rawCheckOutTime === 'null') {
        $rawCheckOutTime = $request->input('checkout_time');
    }
    // Only fall back to 'time' if 'time' is filled AND check-in time was not supplied
    if (($rawCheckOutTime === null || $rawCheckOutTime === '' || $rawCheckOutTime === 'null')
        && $request->filled('time')
        && !$request->filled('check_in_time')
        && !$request->filled('checkin_time')) {
        $rawCheckOutTime = $request->input('time');
    }
    $checkOutTime = $this->normalizeTime($rawCheckOutTime);

    $checkOutPhotoFile = null;
    if ($request->hasFile('check_out_photo')) {
        $checkOutPhotoFile = $request->file('check_out_photo');
    } elseif ($request->hasFile('checkout_photo')) {
        $checkOutPhotoFile = $request->file('checkout_photo');
    } elseif ($request->hasFile('photo')) {
        $checkOutPhotoFile = $request->file('photo');
    }

    $checkinUpdate = [];
    if ($checkInTime !== null) {
        $checkinUpdate['check_in_time'] = $checkInTime;
    }
    if ($checkInPhotoFile && $checkInPhotoFile->isValid()) {
        $checkinUpdate['check_in_photo'] = $checkInPhotoFile->store('wages_checkin', 'public');
    }
    if ($checkOutTime !== null) {
        $checkinUpdate['check_out_time'] = $checkOutTime;
    }
    if ($checkOutPhotoFile && $checkOutPhotoFile->isValid()) {
        $checkinUpdate['check_out_photo'] = $checkOutPhotoFile->store('wages_checkout', 'public');
    } elseif ($request->has('check_out_photo') || $request->has('checkout_photo')) {
        $checkinUpdate['check_out_photo'] = null;
    }

    if (!empty($checkinUpdate)) {
        AttendanceCheckin::updateOrCreate(
            [
                'site_id' => $request->site_id,
                'date'    => $request->date,
            ],
            $checkinUpdate + ['created_by' => $adminId]
        );

        $wagesCheckinUpdate = array_intersect_key($checkinUpdate, array_flip([
            'check_in_time',
            'check_in_photo',
            'check_out_time',
            'check_out_photo',
        ]));
        if (!empty($wagesCheckinUpdate)) {
            Wages::where('site_id', $request->site_id)
                ->where('date', $request->date)
                ->update($wagesCheckinUpdate);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Attendance and wages updated successfully!',
    ]);
}

    /**
     * multipart/form-data can't carry nested arrays natively, so mobile clients
     * sometimes send a JSON-encoded string for an array field instead of
     * bracket-indexed keys. Decode it in place so validation/downstream code
     * can treat the field as an array either way.
     */
    private function decodeJsonArrayField(Request $request, string $field): void
    {
        $value = $request->input($field);

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge([$field => $decoded]);
            }
        }
    }

}


