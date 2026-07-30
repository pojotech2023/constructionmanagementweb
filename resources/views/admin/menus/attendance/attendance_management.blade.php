@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">

        <!-- Page Header -->
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
            <div>
                <h3 class="fw-bold mb-1">Attendance Details</h3>
                <ul class="breadcrumbs mb-0">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="{{ route('sitemanagement.list') }}">Site</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="{{ route('site.detail', $siteId) }}">{{ $siteName }}</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Attendance Details</a></li>
                </ul>
            </div>

            
        </div>

        <div class="card">

            <!-- Card Header -->
            <div class="card-header">
                <h6 class="card-title fw-bold mb-3">
                    Site Name: {{ $siteName }}
                </h6>

                <!-- Month Picker -->
                <div class="row g-2 align-items-end pb-3 border-bottom">
                    <div class="col-12 col-md-3">
                        <input type="month" id="monthPicker" class="form-control"
                               value="{{ $month }}">
                    </div>

                    <!-- Week Buttons -->
                    <div class="col-12">
                        <div class="row justify-content-center align-items-center" id="weekFilterRow">
                            <div class="col-auto mb-2">
                                <span class="badge badge-secondary week-reset-btn {{ empty($week) ? 'active' : '' }}">
                                    Full Month
                                </span>
                            </div>
                            @foreach ($availableWeeks as $weekNumber)
                                <div class="col-auto mb-2">
                                    <span class="badge badge-black week-btn {{ (string) $week === (string) $weekNumber ? 'active' : '' }}"
                                          data-week="{{ $weekNumber }}">
                                        Week {{ $weekNumber }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Week Days -->
                <div class="row g-2 justify-content-center py-3 border-bottom" id="weekBoxRow">
                    @if (!empty($weekDays))
                        @foreach ($weekDays as $day)
                            <div class="col-auto">
                                <div class="day-box border rounded text-center
                                    {{ request('date') === $day['value'] ? 'selected-day' : 'bg-success' }}"
                                    data-date="{{ $day['value'] }}">
                                    <div>{{ \Carbon\Carbon::parse($day['value'])->format('D') }}</div>
                                    <div>{{ \Carbon\Carbon::parse($day['value'])->format('d') }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- TOTAL WORKERS + ACTION BUTTONS -->
                <div class="row align-items-center mt-3 g-2">
                    <div class="col-12 col-md-4">
                        <h4 class="card-title mb-0">Total Workers</h4>
                    </div>

                    <div class="col-12 col-md-8">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                            <a href="{{ route('wages.form', ['siteId' => $siteId]) }}" class="btn btn-info">
                                Add Wages
                            </a>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#attendanceExportModal">
                                Export
                            </button>

                            {{-- Add Attendance button removed per request --}}

                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3">
                    {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- BODY -->
            <div class="card-body">
                @if ($attendances->isEmpty())
                    <p class="text-center">No Attendance list.</p>
                @else

                {{-- MONTH VIEW TABLE --}}
                @if (!empty($month) && empty($date))
                <div class="table-responsive">
                    <table class="table table-striped table-hover attendance-table">
                        <thead>
                            <tr>
                                
                                <th>Date</th>
                                @foreach ($allCategories as $cat)
                                    <th>{{ ucfirst($cat) }}</th>
                                @endforeach
                                <th>Total (₹)</th>
                                <th class="text-center">Check In / Check Out</th>
                                <th class="text-center attendance-action-column">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedByDate as $day)
                                @php $photos = $dayPhotos[$day['date']] ?? null; @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('d-m-Y') }}</td>
                                    @foreach ($allCategories as $cat)
                                        <td>{{ $day[$cat] ?? '--' }}</td>
                                    @endforeach
                                    <td>
                                        ₹{{ $day['total'] }}
                                    </td>
                                    <td class="text-center">
                                        @if ($photos && $photos['check_in_photo'])
                                            <div class="attendance-photo-cell">
                                                <a href="{{ asset('storage/' . $photos['check_in_photo']) }}" target="_blank" title="Check-in photo">
                                                    <img src="{{ asset('storage/' . $photos['check_in_photo']) }}" class="attendance-photo-thumb" alt="Check-in">
                                                </a>
                                                @if ($photos['check_in_time'])
                                                    <div class="attendance-photo-time">In: {{ \Carbon\Carbon::parse($photos['check_in_time'])->format('h:i A') }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($photos && $photos['check_out_photo'])
                                            <div class="attendance-photo-cell">
                                                <a href="{{ asset('storage/' . $photos['check_out_photo']) }}" target="_blank" title="Check-out photo">
                                                    <img src="{{ asset('storage/' . $photos['check_out_photo']) }}" class="attendance-photo-thumb" alt="Check-out">
                                                </a>
                                                @if ($photos['check_out_time'])
                                                    <div class="attendance-photo-time">Out: {{ \Carbon\Carbon::parse($photos['check_out_time'])->format('h:i A') }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($photos && !$photos['check_in_photo'] && $photos['check_in_time'])
                                            <div class="attendance-photo-time">In: {{ \Carbon\Carbon::parse($photos['check_in_time'])->format('h:i A') }}</div>
                                        @endif
                                        @if ($photos && !$photos['check_out_photo'] && $photos['check_out_time'])
                                            <div class="attendance-photo-time">Out: {{ \Carbon\Carbon::parse($photos['check_out_time'])->format('h:i A') }}</div>
                                        @endif
                                        @if (!$photos || (!$photos['check_in_photo'] && !$photos['check_out_photo'] && !$photos['check_in_time'] && !$photos['check_out_time']))
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center attendance-action-column">
                                        <div class="attendance-action-group">
                                            <a href="{{ route('edit.attendance.wages', [
                                                'site_id' => $siteId,
                                                'date' => $day['date']
                                            ]) }}" class="btn btn-link btn-primary btn-sm attendance-action-btn" title="Edit" aria-label="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-link btn-danger btn-sm deleteDateBtn attendance-action-btn" data-siteid="{{ $siteId }}" data-date="{{ $day['date'] }}" data-bs-toggle="modal" data-bs-target="#deleteDateModal" title="Delete" aria-label="Delete">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="attendance-total-box-row">
                                <td></td>
                                @foreach ($allCategories as $cat)
                                    <td>
                                        <div class="attendance-total-box">
                                            <div>Total</div>
                                            <strong>{{ $workerCategoryTotals[$cat] ?? 0 }}</strong>
                                        </div>
                                    </td>
                                @endforeach
                                <td></td>
                                <td class="attendance-action-column"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- DAILY VIEW --}}
                @else
                @php $dailyPhotos = !empty($date) ? ($dayPhotos[$date] ?? null) : null; @endphp
                @if ($dailyPhotos && ($dailyPhotos['check_in_photo'] || $dailyPhotos['check_out_photo'] || $dailyPhotos['check_in_time'] || $dailyPhotos['check_out_time']))
                    <div class="d-flex gap-4 mb-3">
                        @if ($dailyPhotos['check_in_photo'] || $dailyPhotos['check_in_time'])
                            <div class="text-center">
                                <div class="small text-muted mb-1">Check-in</div>
                                @if ($dailyPhotos['check_in_photo'])
                                    <a href="{{ asset('storage/' . $dailyPhotos['check_in_photo']) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $dailyPhotos['check_in_photo']) }}" class="attendance-photo-thumb-lg" alt="Check-in">
                                    </a>
                                @endif
                                @if ($dailyPhotos['check_in_time'])
                                    <div class="attendance-photo-time">{{ \Carbon\Carbon::parse($dailyPhotos['check_in_time'])->format('h:i A') }}</div>
                                @endif
                            </div>
                        @endif
                        @if ($dailyPhotos['check_out_photo'] || $dailyPhotos['check_out_time'])
                            <div class="text-center">
                                <div class="small text-muted mb-1">Check-out</div>
                                @if ($dailyPhotos['check_out_photo'])
                                    <a href="{{ asset('storage/' . $dailyPhotos['check_out_photo']) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $dailyPhotos['check_out_photo']) }}" class="attendance-photo-thumb-lg" alt="Check-out">
                                    </a>
                                @endif
                                @if ($dailyPhotos['check_out_time'])
                                    <div class="attendance-photo-time">{{ \Carbon\Carbon::parse($dailyPhotos['check_out_time'])->format('h:i A') }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
                <div class="row">
                    @foreach ($attendances as $attendance)
                        @php
                            $attendanceDate = \Carbon\Carbon::parse($attendance->date);
                            $category = $attendance->category;

                            $amount = optional(
                                $wages->where('category', $category)
                                      ->where('date', '<=', $attendanceDate->toDateString())
                                      ->sortByDesc('date')
                                      ->first()
                            )->amount ?? 0;

                            $total = $attendance->count * $amount;
                        @endphp

                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="card border border-info h-100">
                                <div class="card-body">
                                    <h4>{{ $attendance->count }}</h4>
                                    <p class="mb-1">
                                        {{ ucfirst($category) }} × ₹{{ $amount }}
                                    </p>
                                    <strong>₹{{ $total }}</strong>
                                    <div class="mt-3 d-flex gap-2">
                                        <a href="{{ route('edit.attendance.wages', ['site_id' => $siteId, 'date' => $attendance->date]) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger deleteAttendanceBtn" data-id="{{ $attendance->id }}" data-date="{{ $attendance->date }}" data-bs-toggle="modal" data-bs-target="#deleteAttendanceModal">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                @if (!empty($date))
                <!-- TOTAL WORKERS -->
                <div class="row justify-content-center mt-4 g-3">
                    @foreach ($allCategories as $cat)
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card border border-primary text-center">
                            <div class="card-body">
                                <h5>{{ ucfirst($cat) }}</h5>
                                <h3 class="fw-bold">{{ $workerCategoryTotals[$cat] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- TOTAL WORKERS + WAGES -->
                <div class="row justify-content-center mt-4 g-3">
                    <div class="col-md-5">
                        <div class="card border border-primary text-center attendance-grand-total-card">
                            <div class="card-body">
                                <h5>Total Workers</h5>
                                <h3 class="fw-bold">{{ $totalWorkers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card border border-primary text-center attendance-grand-total-card">
                            <div class="card-body">
                                <h5>Total Wages</h5>
                                <h3 class="fw-bold">₹{{ $totalWages ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                @endif
            </div>
        </div>
    </div>
</div>

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Export Attendance Modal -->
    <div class="modal fade" id="attendanceExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="GET" action="{{ route('attendance.export', ['siteId' => $siteId]) }}" class="js-export-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Attendance Modal -->
    <div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteAttendanceForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this attendance record for <span id="deleteAttendanceDate"></span>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Date Modal (month view) -->
    <div class="modal fade" id="deleteDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteDateForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete all attendance records for <strong id="deleteDateText"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.js-export-modal-form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var modalEl = form.closest('.modal');
                    var modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                    if (modal) {
                        modal.hide();
                    }
                });
            });

            document.querySelectorAll('.deleteDateBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var siteId = this.getAttribute('data-siteid');
                    var date = this.getAttribute('data-date');
                    var form = document.getElementById('deleteDateForm');
                    form.action = '/admin/attendance-delete-date/' + siteId + '/' + date;
                    document.getElementById('deleteDateText').textContent = formatDisplayDate(date);
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.deleteAttendanceBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var date = this.getAttribute('data-date');
                    var form = document.getElementById('deleteAttendanceForm');
                    form.action = '/admin/attendance-delete/' + id;
                    document.getElementById('deleteAttendanceDate').textContent = formatDisplayDate(date);
                });
            });
        });
    </script>

    <!-- JS script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthPicker = document.getElementById('monthPicker');
            const weekButtons = document.querySelectorAll('.week-btn');
            const weekResetButton = document.querySelector('.week-reset-btn');
            const weekFilterRow = document.getElementById('weekFilterRow');
            const weekBoxRow = document.getElementById('weekBoxRow');
            const attendanceBaseUrl = @json(route('attendance', ['siteId' => $siteId]));

            let currentSelectedMonth = monthPicker.value;

            // Month change => Load entire month data
            monthPicker.addEventListener('change', function() {
                currentSelectedMonth = this.value;
                if (currentSelectedMonth) {
                    window.location.href = `${attendanceBaseUrl}?month=${currentSelectedMonth}`;
                }
            });

            // Week button click => Load week data for currentSelectedMonth
            weekButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const wasActive = this.classList.contains('active');
                    weekButtons.forEach(b => b.classList.remove('active'));
                    const selectedWeek = this.getAttribute('data-week');

                    if (wasActive) {
                        window.location.href = `${attendanceBaseUrl}?month=${currentSelectedMonth}`;
                        return;
                    }

                    this.classList.add('active');

                    if (currentSelectedMonth && selectedWeek) {
                        window.location.href =
                            `${attendanceBaseUrl}?month=${currentSelectedMonth}&week=${selectedWeek}`;
                    }
                });
            });

            if (weekResetButton) {
                weekResetButton.addEventListener('click', function() {
                    if (currentSelectedMonth) {
                        window.location.href = `${attendanceBaseUrl}?month=${currentSelectedMonth}`;
                    }
                });
            }

            if (weekFilterRow) {
                weekFilterRow.addEventListener('click', function(event) {
                    const hasSelectedWeek = !!getQueryParam('week');
                    const clickedWeekControl = event.target.closest('.week-btn, .week-reset-btn');

                    if (!hasSelectedWeek || clickedWeekControl) {
                        return;
                    }

                    if (currentSelectedMonth) {
                        window.location.href = `${attendanceBaseUrl}?month=${currentSelectedMonth}`;
                    }
                });
            }

            // Day box click => Load specific date view
            weekBoxRow.addEventListener('click', function(event) {
                const target = event.target.closest('.day-box');
                if (target) {
                    const selectedDateStr = target.getAttribute('data-date');
                    const selectedMonth = getQueryParam('month');
                    const selectedWeek = getQueryParam('week');

                    if (selectedDateStr) {
                        let url = `${attendanceBaseUrl}?date=${selectedDateStr}`;
                        if (selectedMonth) url += `&month=${selectedMonth}`;
                        if (selectedWeek) url += `&week=${selectedWeek}`;
                        window.location.href = url;
                    }
                }
            });

        });

        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function formatDisplayDate(dateValue) {
            if (!dateValue) return '-';
            const parts = dateValue.split('-');
            if (parts.length === 3 && parts[0].length === 4) {
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            return dateValue;
        }

        // Highlight selected week and day on page load
        const selectedWeek = getQueryParam('week');
        const selectedDate = getQueryParam('date');

        // Highlight the selected week button
        if (selectedWeek) {
            document.querySelectorAll('.week-btn').forEach(btn => {
                if (btn.getAttribute('data-week') === selectedWeek) {
                    btn.classList.add('active');
                }
            });
        }

        // Highlight the selected day box
        if (selectedDate) {
            document.querySelectorAll('.day-box').forEach(box => {
                if (box.getAttribute('data-date') === selectedDate) {
                    box.classList.add('selected-day');
                }
            });
        }
    </script>

    <!-- CSS -->
    <style>
          .week-btn {
    cursor: pointer;
}

.week-btn.active {
    background: #007bff;
    color: #fff;
}

.week-reset-btn {
    cursor: pointer;
}

.week-reset-btn.active {
    background: #198754;
    color: #fff;
}

.day-box {
    width: 60px;
    height: 60px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.day-box:hover {
    background: #28a745;
    color: #fff;
}

.selected-day {
    background: #007bff !important;
    color: #fff !important;
}

.attendance-action-btn {
    width: 28px;
    height: 28px;
    padding: 0;
    line-height: 1;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.attendance-action-column {
    width: 88px;
}

.attendance-action-group {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 72px;
}

.attendance-total-box-row td {
    background: #fff;
    padding-top: 12px;
    vertical-align: top;
}

.attendance-table {
    table-layout: auto;
    min-width: max-content;
}

.attendance-table th,
.attendance-table td {
    padding: 12px 14px;
    white-space: nowrap;
}

.attendance-table th:first-child,
.attendance-table td:first-child {
    width: 130px;
    min-width: 130px;
}

.attendance-table th:not(:first-child):not(:last-child),
.attendance-table td:not(:first-child):not(:last-child) {
    width: 145px;
    min-width: 145px;
}

.attendance-table th:nth-last-child(2),
.attendance-table td:nth-last-child(2) {
    width: 120px;
    min-width: 120px;
}

.table.attendance-table th:not(:first-child),
.table.attendance-table td:not(:first-child) {
    text-align: center;
}

.table.attendance-table th:first-child,
.table.attendance-table td:first-child {
    text-align: left;
}

.attendance-total-box {
    min-height: 62px;
    border: 1px solid #0d6efd;
    border-radius: 8px;
    padding: 8px 6px;
    text-align: center;
    color: #101828;
    background: #fff;
}

.attendance-total-box div {
    min-height: 24px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.25;
}

.attendance-total-box strong {
    display: block;
    margin-top: 4px;
    font-size: 20px;
    line-height: 1;
}

.attendance-grand-total-card .card-body {
    padding: 16px 12px;
}

.attendance-grand-total-card h5 {
    margin-bottom: 10px;
    font-size: 16px;
}

.attendance-grand-total-card h3 {
    font-size: 24px;
}

.attendance-photo-thumb {
    width: 36px;
    height: 36px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    margin: 0 2px;
}

.attendance-photo-thumb-lg {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.attendance-photo-cell {
    display: inline-block;
    text-align: center;
    margin: 0 4px;
    vertical-align: top;
}

.attendance-photo-time {
    font-size: 11px;
    color: #475569;
    margin-top: 2px;
    white-space: nowrap;
}

    </style>
@endsection
