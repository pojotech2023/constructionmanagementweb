@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3>Edit for Date: {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h3>
            </div>
            
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-lg p-4 ms-4">

                    <!-- Success Alert -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                   <div class="d-flex justify-content-between align-items-center mb-3">
                       <h4 class="mb-0">Update Attendance &amp; Wages</h4>
                       <button type="button" class="btn btn-primary px-4 py-2 fw-bold" id="addCombinedRow">
                           Add Row
                       </button>
                   </div>
<form action="{{ route('update.attendance.wages') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="site_id" value="{{ $siteId }}">
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="row mt-2">
        <div class="col-md-4">
            <label for="checkOutTime" class="fw-bold">Time (Check-out)</label>
            <input type="time" id="checkOutTime" class="form-control" name="time" value="{{ old('time', optional($checkOut)->check_out_time ? \Carbon\Carbon::parse($checkOut->check_out_time)->format('H:i') : '') }}">
            @error('time')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="checkOutPhoto" class="fw-bold">Upload Photo (Check-out)</label>
            <input type="file" id="checkOutPhoto" name="photo" class="form-control" accept="image/*">
            @if (optional($checkOut)->check_out_photo)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $checkOut->check_out_photo) }}" alt="Check-out photo" style="max-width: 120px; max-height: 90px; object-fit: contain;">
                </div>
            @endif
            @error('photo')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <hr>

    <h5>Attendance</h5>
    <div id="attendanceRows"></div>

    @foreach ($categories as $cat)
        <div class="row mt-2">
            <div class="col-md-4">
                <label>{{ $cat }}</label>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control"
                       name="count_{{ str_replace(' ', '_', $cat) }}"
                       value="{{ $attendance[$cat] ?? 0 }}">
            </div>
        </div>
    @endforeach

    <hr>

    <h5>Wages</h5>
    <div id="wageRows"></div>

    @foreach ($categories as $cat)
        <div class="row mt-2">
            <div class="col-md-4">
                <label>{{ $cat }}</label>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control"
                       name="amount_{{ str_replace(' ', '_', $cat) }}"
                       value="{{ $wages[$cat] ?? 0 }}">
            </div>
        </div>
    @endforeach

    <button class="btn btn-primary mt-3">Update Attendance &amp; Wages</button>
</form>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('requestForm');
            const spinner = document.getElementById('loadingSpinner');
            const alert = document.querySelector('.alert');
            const attendanceRows = document.getElementById('attendanceRows');
            const wageRows = document.getElementById('wageRows');
            let attendanceRowIndex = 0;
            let wageRowIndex = 0;
            let combinedRowIndex = 0;

            function addDynamicRow(container, index, pairId, fieldName, valueName, valueLabel, inputStep) {
                const row = document.createElement('div');
                row.className = 'row mt-2 align-items-end dynamic-row';
                row.setAttribute('data-pair-id', pairId);
                row.innerHTML = `
                    <div class="col-md-4">
                        <label>Category</label>
                        <input type="text" class="form-control" name="${fieldName}[${index}][category]" placeholder="Category">
                    </div>
                    <div class="col-md-4">
                        <label>${valueLabel}</label>
                        <input type="number" step="${inputStep}" class="form-control" name="${fieldName}[${index}][${valueName}]" placeholder="${valueLabel}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" aria-label="Remove row">&times;</button>
                    </div>
                `;
                container.prepend(row);
            }

            document.getElementById('addCombinedRow').addEventListener('click', function() {
                const pairId = `combined-${combinedRowIndex++}`;
                addDynamicRow(attendanceRows, attendanceRowIndex++, pairId, 'attendance_rows', 'count', 'Count', '1');
                addDynamicRow(wageRows, wageRowIndex++, pairId, 'wage_rows', 'amount', 'Amount', '0.01');
            });

            document.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-row');
                if (removeButton) {
                    const row = removeButton.closest('.dynamic-row');
                    const pairId = row ? row.getAttribute('data-pair-id') : null;

                    if (pairId) {
                        document.querySelectorAll(`.dynamic-row[data-pair-id="${pairId}"]`).forEach(function(pairRow) {
                            pairRow.remove();
                        });
                    }
                }
            });

            // ✅ Spinner on submit
            if (form && spinner) {
                form.addEventListener('submit', function() {
                    spinner.classList.remove('d-none');
                });
            }

            // ✅ Success message fade & redirect
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    const siteId = "{{ $siteId }}";
                    // window.location.href = "/admin/attendance/" + siteId;
                }, 1000);
            }
        });
    </script>
@endsection
