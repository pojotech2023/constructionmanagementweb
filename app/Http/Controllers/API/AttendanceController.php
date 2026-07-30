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
    $validate = Validator::make($request->all(), [
        'time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'check_in_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'check_out_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'check_in_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'check_out_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'site_id' => 'required|exists:sites,id',
        'date' => 'required|date',
        'amount_mason' => 'nullable|numeric',
        'amount_helper' => 'nullable|numeric',
        'amount_fitter' => 'nullable|numeric',
        'amount_Centring_Helper' => 'nullable|numeric',
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

    $checkInTime = $this->normalizeTime($request->input('time', $request->input('check_in_time')));
    $checkInPhotoFile = $request->hasFile('photo') ? $request->file('photo') : ($request->hasFile('check_in_photo') ? $request->file('check_in_photo') : null);

    $checkOutTime = $this->normalizeTime($request->input('check_out_time'));
    $checkOutPhotoFile = $request->hasFile('check_out_photo') ? $request->file('check_out_photo') : null;

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
    $validate = Validator::make($request->all(), [
        'site_id'  => 'required|exists:sites,id',
        'date'     => 'required|date',
        'time'     => 'nullable|date_format:H:i,H:i:s',
        'image'    => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'count_mason' => 'nullable|numeric',
        'count_helper' => 'nullable|numeric',
        'count_fitter' => 'nullable|numeric',
        'count_Centring_Helper' => 'nullable|numeric',
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validate->errors()
        ], 422);
    }

    $imageUrl = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('attendance_images', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    // Map database category names with request input fields
    $categories = [
        'Mason' => 'count_mason',
        'Helper' => 'count_helper',
        'Fitter' => 'count_fitter',
        'Centring Helper' => 'count_Centring_Helper',
    ];

    foreach ($categories as $categoryName => $fieldName) {
        if ($request->filled($fieldName)) {
            Attendance::create([
                'site_id' => $request->site_id,
                'category' => $categoryName,
                'count' => $request->$fieldName,
                'date' => $request->date,
                'time' => $request->time,
                'image_url' => $imageUrl,
                'created_by' => auth('api')->id(),
            ]);
        }
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
private function normalizeTime(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    try {
        return Carbon::parse($value)->format('H:i:s');
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
        ]);

        $siteId = $request->site_id;
        $month = $request->month;
        $week = $request->week;

        // Generate file name
        $fileName = 'attendance_' . $siteId . '_' . time() . '.xlsx';
        $filePath = 'exports/' . $fileName;

        try {
            // Generate and store Excel file
            Excel::store(new AttendanceExport($siteId, $month, $week), $filePath, 'public');

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

    $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

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

    // Wages
    $wages = Wages::where('site_id', $site_id)
                  ->where('date', $date)
                  ->pluck('amount', 'category');

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

    return response()->json([
        'success'    => true,
        'site_id'    => $site_id,
        'date'       => $date,
        'categories' => $categories,
        'attendance' => $formattedAttendance,
        'wages'      => $formattedWages,
    ]);
}


    public function updateAttendance(Request $request)
    {
        $request->validate([
            'site_id' => 'required',
            'date'    => 'required|date',
            'time'    => 'nullable|date_format:H:i,H:i:s',
            'image'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('attendance_images', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

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

            Attendance::updateOrCreate(
                [
                    'site_id'  => $request->site_id,
                    'date'     => $request->date,
                    'category' => $cat,
                ],
                $updateData
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully!'
        ]);
    }

    public function updateWages(Request $request)
    {
        $request->validate([
            'site_id' => 'required',
            'date'    => 'required|date',
        ]);

        $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

        foreach ($categories as $cat) {

            $key = "amount_" . str_replace(' ', '_', $cat);

            $input = $request->input($key);

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
                    'amount'     => $input,
                    'created_by' => $request->admin_id ?? 1,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Wages updated successfully!'
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

        $attendance->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Attendance record deleted successfully.',
        ]);
    }

    public function updateAttendanceAndWages(Request $request)
{
    $request->validate([
        'site_id' => 'required',
        'date'    => 'required|date',
        'time'    => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'check_in_time'  => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'check_out_time' => 'nullable|date_format:H:i,H:i:s,g:i A,h:i A,g:i:s A,h:i:s A',
        'image'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'photo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'check_in_photo'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'check_out_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $adminId = $request->admin_id ?? 1; // mobile will send admin_id
    $categories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];

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

    $checkInTime = $this->normalizeTime($request->input('check_in_time'));
    $checkInPhotoFile = $request->hasFile('check_in_photo') ? $request->file('check_in_photo') : null;

    $checkOutTime = $this->normalizeTime($request->input('time', $request->input('check_out_time')));
    $checkOutPhotoFile = $request->hasFile('photo') ? $request->file('photo') : ($request->hasFile('check_out_photo') ? $request->file('check_out_photo') : null);

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
            $checkinUpdate + ['created_by' => $adminId]
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Attendance and wages updated successfully!',
    ]);
}

}


