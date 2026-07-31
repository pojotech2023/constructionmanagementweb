@extends('layouts.app')

@section('content')
    <style>
        .duplicate-entry-popup {
            text-align: center !important;
            border-radius: 16px !important;
            padding: 2rem 1.75rem 1.75rem !important;
        }

        .duplicate-entry-popup .swal-text {
            text-align: center !important;
            width: 100% !important;
            margin: 0.75rem 0 0 !important;
            color: #5a6169 !important;
            font-size: 0.95rem !important;
            line-height: 1.5 !important;
        }

        .duplicate-entry-popup .swal-footer {
            text-align: center !important;
            margin-top: 1.5rem !important;
        }

        .duplicate-entry-popup .swal-button-container {
            display: flex !important;
            justify-content: center !important;
            margin: 0 !important;
        }

        .duplicate-entry-popup .swal-button,
        .duplicate-entry-popup-btn {
            padding: 0.7rem 2.5rem !important;
            font-size: 1rem !important;
            border-radius: 8px !important;
        }
    </style>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Add Wages</h3>
            </div>
            <div class="col-lg-2 text-end">
                {{-- Back button removed per request --}}
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

                    <form id="requestForm" action="{{ route('add.wages') }}" method="POST" class="container" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <!-- Single Date & Check-in Time for all rows -->
                        <div class="row mt-3">
                            <div class="col-md-12 text-end">
                                <div class="form-group d-inline-block me-3">
                                    <label for="todayDate" class="fw-bold">Date</label>
                                    <input type="date" id="todayDate" class="form-control w-auto d-inline-block" name="date" value="{{ old('date') }}" required>
                                </div>
                                <div class="form-group d-inline-block">
                                    <label for="checkInTime" class="fw-bold">Time (Check-in)</label>
                                    <input type="time" id="checkInTime" class="form-control w-auto d-inline-block" name="time" value="{{ old('time') }}">
                                </div>
                                @error('date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                @error('time')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <h5 class="mb-0">Add Wages</h5>
                                <small class="text-muted">Enter category, amount and count </small>
                            </div>
                            <div>
                                <button type="button" id="addRowBtn" class="btn btn-sm btn-success">Add Row</button>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="wagesTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th style="width:140px">Amount</th>
                                        <th style="width:120px">Count</th>
                                        <th style="width:80px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $defaultCategories = ['Mason', 'Helper', 'Fitter', 'Centring Helper'];
                                    @endphp
                                    @foreach ($defaultCategories as $i => $cat)
                                        <tr>
                                            <td>
                                                <input type="text" name="rows[{{ $i }}][category]" class="form-control" value="{{ $cat }}">
                                            </td>
                                            <td>
                                                <input type="number" name="rows[{{ $i }}][amount]" class="form-control amount-input no-arrow" step="0.01" min="0" value="{{ old('rows.' . $i . '.amount') }}">
                                            </td>
                                            <td>
                                                <input type="number" name="rows[{{ $i }}][count]" class="form-control count-input no-arrow" min="0" value="{{ old('rows.' . $i . '.count') }}">
                                            </td>
                                            <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-link btn-primary btn-sm edit-row-btn" title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-link btn-danger btn-sm delete-row-btn" data-row-index="{{ $i }}" title="Delete">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Upload Photo -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="checkInPhoto" class="fw-bold">Upload Photo (Check-in)</label>
                                    <input type="file" id="checkInPhoto" name="photo" class="form-control" accept="image/*">
                                </div>
                                @error('photo')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="col-lg-2 ms-auto mt-3">
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

    <script>
        function showDuplicatePopup(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    text: message,
                    confirmButtonText: 'Got it',
                    customClass: {
                        popup: 'duplicate-entry-popup',
                        htmlContainer: 'duplicate-entry-popup-text',
                        actions: 'duplicate-entry-popup-actions',
                        confirmButton: 'duplicate-entry-popup-btn'
                    }
                }).then(function() {
                    window.location.reload();
                });
            } else if (typeof swal !== 'undefined') {
                swal({
                    text: message,
                    icon: 'warning',
                    className: 'duplicate-entry-popup',
                    button: {
                        text: 'Got it'
                    }
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

            const wagesTable = document.getElementById('wagesTable');
            const addRowBtn = document.getElementById('addRowBtn');

            function rowCount() {
                return wagesTable.querySelectorAll('tbody tr').length;
            }

            function createRow(index, data = {}) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <input type="text" name="rows[${index}][category]" class="form-control" value="${data.category || ''}">
                        </td>
                        <td>
                            <input type="number" name="rows[${index}][amount]" class="form-control amount-input no-arrow" step="0.01" min="0" value="${data.amount || ''}">
                        </td>
                        <td>
                            <input type="number" name="rows[${index}][count]" class="form-control count-input no-arrow" min="0" value="${data.count || ''}">
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-link btn-primary btn-sm edit-row-btn" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-link btn-danger btn-sm delete-row-btn" data-row-index="${index}" title="Delete">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </td>
                    `;
                return tr;
            }

            addRowBtn.addEventListener('click', function() {
                const idx = rowCount();
                wagesTable.querySelector('tbody').appendChild(createRow(idx));
            });

            // Delete flow: show confirmation modal before removing
            let pendingDeleteRow = null;
            wagesTable.addEventListener('click', function(e) {
                // remove via delete button (confirm first)
                if (e.target.closest && e.target.closest('.delete-row-btn')) {
                    pendingDeleteRow = e.target.closest('tr');
                    // open modal
                    const deleteModalEl = document.getElementById('deleteRowModal');
                    const deleteModal = new bootstrap.Modal(deleteModalEl);
                    deleteModal.show();
                    return;
                }

                // edit button: focus first input in the row
                if (e.target.closest && e.target.closest('.edit-row-btn')) {
                    const row = e.target.closest('tr');
                    const firstInput = row.querySelector('input, select');
                    if (firstInput) firstInput.focus();
                    return;
                }

                // handle remove-row-btn if still present
                if (e.target.classList.contains('remove-row-btn')) {
                    const tr = e.target.closest('tr');
                    if (tr) tr.remove();
                }
            });

            // Confirm delete button in modal
            const confirmDeleteBtn = document.getElementById('confirmDeleteRowBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (pendingDeleteRow && pendingDeleteRow.parentNode) {
                        pendingDeleteRow.parentNode.removeChild(pendingDeleteRow);
                        // reindex names
                        const rows = wagesTable.querySelectorAll('tbody tr');
                        rows.forEach((r, i) => {
                            r.querySelectorAll('input, select').forEach(el => {
                                const name = el.getAttribute('name');
                                if (name) {
                                    const newName = name.replace(/rows\[\d+\]/, `rows[${i}]`);
                                    el.setAttribute('name', newName);
                                }
                            });
                        });
                    }

                    // hide modal
                    const deleteModalEl = document.getElementById('deleteRowModal');
                    const bs = bootstrap.Modal.getInstance(deleteModalEl);
                    if (bs) bs.hide();
                });
            }

            // Spinner on submit
            if (form && spinner) {
                form.addEventListener('submit', function(e) {
                    // before submit, copy action-input values into amount fields
                    const rows = wagesTable.querySelectorAll('tbody tr');
                    rows.forEach((r, i) => {
                        const actionInput = r.querySelector('.action-input');
                        const amountInput = r.querySelector('.amount-input');
                        if (actionInput && amountInput) {
                            amountInput.value = actionInput.value;
                        }
                    });
                    spinner.classList.remove('d-none');
                });
            }

            // Success message fade & redirect
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
    <!-- Delete confirmation modal for table rows -->
    <div class="modal fade" id="deleteRowModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove this row?</p>
                    {{-- <div class="mb-3">
                        <label for="row_delete_reason" class="form-label">Reason (optional)</label>
                        <input type="text" id="row_delete_reason" class="form-control" placeholder="Enter reason">
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteRowBtn" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection
