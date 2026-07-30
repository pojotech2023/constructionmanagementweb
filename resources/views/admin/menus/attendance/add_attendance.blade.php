@extends('layouts.app')

@section('content')
    <style>
        .duplicate-entry-popup {
            max-width: 360px !important;
            border-radius: 18px !important;
            padding: 1.75rem 1.5rem 1.5rem !important;
            box-shadow: 0 12px 36px rgba(241, 65, 108, 0.22) !important;
        }
        /* Scale the whole icon proportionally (don't resize width/height directly —
           the exclamation mark inside is absolutely positioned for the default size
           and breaks/misaligns if the box is resized instead of scaled). */
        .duplicate-entry-popup .swal-icon,
        .duplicate-entry-popup .swal-icon--warning {
            transform: scale(0.7);
            margin: -8px auto -16px !important;
            background-color: rgba(241, 65, 108, 0.1) !important;
            border-color: #f1416c !important;
        }
        .duplicate-entry-popup .swal-icon--warning__body,
        .duplicate-entry-popup .swal-icon--warning__dot {
            background-color: #f1416c !important;
        }
        .duplicate-entry-popup-title,
        .duplicate-entry-popup .swal-title {
            margin: 0 0 0.4rem !important;
            font-size: 1.15rem !important;
            color: #d81b5f !important;
            font-weight: 700 !important;
        }
        .duplicate-entry-popup .swal-text {
            font-size: 0.85rem !important;
            line-height: 1.4 !important;
            color: #6c757d !important;
            text-align: center !important;
        }
        .duplicate-entry-popup .swal-footer {
            text-align: center !important;
            margin-top: 1rem !important;
        }
        .duplicate-entry-popup-btn,
        .duplicate-entry-popup .swal-button {
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            padding: 0.5rem 1.75rem !important;
            background-color: #f1416c !important;
            box-shadow: 0 4px 12px rgba(241, 65, 108, 0.3) !important;
            transition: transform 0.15s ease !important;
        }
        .duplicate-entry-popup .swal-button:hover {
            background-color: #d81b5f !important;
            transform: translateY(-1px) !important;
        }
    </style>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Add Attendance</h3>
            </div>

        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-lg p-4 ms-4">

                    <!-- Success Alert -->
                    @if (session('success'))
                        <div id="successAlert" class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                    <form id="requestForm" action="{{ route('add.attendance') }}" method="POST" class="container">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <!-- Date Picker -->
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <label for="todayDate" class="fw-bold">Date</label>
                                    <input type="date" id="todayDate"
                                           class="form-control w-auto d-inline-block"
                                           name="date"
                                           value="{{ old('date') }}"
                                           required>
                                </div>
                                @error('date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Labels -->
                        <div class="row align-items-center mt-4">
                            <div class="col-md-4">
                                <label class="fw-bold">Category</label>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Count</label>
                            </div>
                        </div>

                        <!-- Category & Count Inputs -->
                        @foreach (['Mason', 'Helper', 'Fitter', 'Centring Helper'] as $category)
                            <div class="row align-items-center mt-3">
                                <div class="col-lg-4">
                                    <input type="text" class="form-control"
                                           value="{{ ucfirst($category) }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control no-arrow"
                                           min="0" step="1"
                                           name="count_{{ $category }}"
                                           value="{{ old('count_' . $category) }}"
                                           placeholder="Enter Count">
                                    @error('count_' . $category)
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach

                        <!-- Submit -->
                        <div class="col-lg-2 ms-auto mt-4">
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
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
        function showDuplicatePopup(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Already Added',
                    text: message,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#f1416c',
                    customClass: {
                        popup: 'duplicate-entry-popup',
                        title: 'duplicate-entry-popup-title',
                        confirmButton: 'duplicate-entry-popup-btn'
                    }
                }).then(function() {
                    window.location.reload();
                });
            } else if (typeof swal !== 'undefined') {
                swal({
                    title: 'Already Added',
                    text: message,
                    icon: 'warning',
                    dangerMode: true,
                    className: 'duplicate-entry-popup',
                    button: { text: 'Got it' }
                }).then(function() {
                    window.location.reload();
                });
            } else {
                window.alert(message);
                window.location.reload();
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('requestForm');
            const spinner = document.getElementById('loadingSpinner');
            const successAlert = document.getElementById('successAlert');
            const dateInput = document.getElementById('todayDate');

            @if (session('error'))
                showDuplicatePopup(@json(session('error')));
            @endif

            // Proactively check the selected date before the admin fills the form —
            // if this site already has attendance/wages for that date, warn immediately.
            if (dateInput) {
                function checkDateAndWarn() {
                    const selectedDate = dateInput.value;
                    if (!selectedDate) return;

                    fetch("{{ route('attendance.checkDate', $siteId) }}?date=" + encodeURIComponent(selectedDate))
                        .then(res => res.json())
                        .then(data => {
                            if (data.exists) {
                                showDuplicatePopup(
                                    data.categories.join(', ') + ' already added for ' + data.date + '. Please edit the existing entry instead of adding it again.'
                                );
                            }
                        })
                        .catch(() => {});
                }

                // 'change' covers picking a different date; 'blur' also covers re-picking
                // the SAME date again (native date inputs don't fire 'change' when the
                // value doesn't actually change), so the warning shows every time.
                dateInput.addEventListener('change', checkDateAndWarn);
                dateInput.addEventListener('blur', checkDateAndWarn);
            }

            // ✅ Spinner show on submit
            if (form && spinner) {
                form.addEventListener('submit', function() {
                    spinner.classList.remove('d-none');
                });
            }

            // ✅ Success alert fade and redirect
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove('show');
                    successAlert.classList.add('fade');
                    const siteId = "{{ $siteId }}";
                    window.location.href = "/admin/public/admin/attendance/" + siteId;
                }, 1000);
            }
        });
    </script>
@endsection
