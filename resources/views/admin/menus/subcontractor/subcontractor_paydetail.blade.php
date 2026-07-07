@extends('layouts.app')

@section('content')
    @php
        $showPayment = request()->query('show_payment') === '1';
    @endphp
    <div class="container">
        <div class="page-inner">
            <div class="page-header mb-3">
                <div>
                    <ul class="breadcrumbs mb-2">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="icon-home"></i>
                            </a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item">
                            <a href="{{ isset($siteId) ? route('sitemanagement.list') : route('subcontractor.dashboard') }}">
                                {{ isset($siteId) ? 'Site' : 'SubContractor Dashboard' }}
                            </a>
                        </li>
                        @isset($siteId)
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item">
                                <a href="{{ route('site.detail', $siteId) }}">{{ $siteName ?? 'Site Details' }}</a>
                            </li>
                        @endisset
                        @isset($subcontractor)
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item">
                                <a href="#">{{ $subcontractor->subcontractors ?: 'Site Utilities' }}</a>
                            </li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item">
                                <a href="{{ route('subcontractor.payDetailForm', ['subcontractorId' => $subcontractorId]) }}">{{ $subcontractor->name }}</a>
                            </li>
                        @else
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item">
                                <a href="#">{{ $title ?? 'SubContractor Payment Detail' }}</a>
                            </li>
                        @endisset
                    </ul>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    @isset($siteId)
                        <button class="btn btn-success mb-2" id="addButton" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fa fa-plus"></i> Add Payment
                        </button>
                        <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#exportHistoryModal">
                            Export
                        </button>
                    @else
                        <a href="{{ route('subcontractor.payDetailForm', ['subcontractorId' => $subcontractorId]) }}?show_payment=1" class="btn btn-success me-2 mb-2">
                            <i class="fa fa-credit-card"></i> Payment
                        </a>
                        <a href="{{ route('subpayment.history', ['subcontractorId' => $subcontractorId]) }}" class="btn btn-primary mb-2">
                            <i class="fa fa-history"></i> Payment History
                        </a>
                    @endisset
                </div>
            </div>

            @if (!isset($siteId) && !$showPayment)
                <div class="card subcontractor-history-card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <h4 class="card-title mb-0">Service Details</h4>
                        </div>

                        @if (($services ?? collect())->isEmpty())
                            <p class="text-center mt-3 mb-0">No service details found for this subcontractor.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-borderless align-middle mb-0 subcontractor-detail-table">
                                    <thead>
                                        <tr>
                                            <th>S.NO</th>
                                            <th>DATE</th>
                                            <th>SITE NAME</th>
                                            <th>COUNTS</th>
                                            <th>AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($services as $service)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $service->date ? \Carbon\Carbon::parse($service->date)->format('d-m-Y') : '-' }}</td>
                                                <td>{{ optional($service->site)->site_name ?? '-' }}</td>
                                                <td>{{ $service->no_counts }}</td>
                                                <td>{{ number_format((float) $service->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                @if (($services ?? collect())->isNotEmpty())
                    <div class="card subcontractor-history-card mt-4">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3 subcontractor-total-table">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL COUNTS</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info">{{ $totalCounts ?? 0 }}</h5>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL AMOUNT</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info">{{ number_format((float) ($totalAmount ?? 0), 2) }}</h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            @if (isset($siteId))
                <div class="card subcontractor-history-card">
                    <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <h4 class="card-title mb-0">Payment History</h4>
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            @unless(isset($siteId))
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subPaymentExportModal">
                                    Export Excel
                                </button>
                                <button class="btn btn-success" id="addButton" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="fa fa-plus"></i> Add Payment
                                </button>
                            @endunless
                        </div>
                    </div>

                    @if (($histories ?? collect())->isEmpty())
                        <p class="text-center mt-3 mb-0">No payment history found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless align-middle mb-0 subcontractor-detail-table">
                                <thead>
                                    <tr>
                                        <th>S.NO</th>
                                        <th>DATE</th>
                                        <th>PAYMENT MODE</th>
                                        <th>REMARKS</th>
                                        <th>PAYMENT</th>
                                        @isset($siteId)
                                            <th class="text-center">Action</th>
                                        @endisset
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $history)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $history->date ? \Carbon\Carbon::parse($history->date)->format('d-m-Y') : '-' }}</td>
                                            <td>{{ $history->payment_mode }}</td>
                                            <td>{{ $history->remarks ?? '-' }}</td>
                                            <td>{{ number_format((float) $history->payment, 2) }}</td>
                                            @isset($siteId)
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-link btn-primary btn-sm editPaymentBtn"
                                                        data-id="{{ $history->id }}"
                                                        data-date="{{ $history->date }}"
                                                        data-payment="{{ $history->payment }}"
                                                        data-payment-mode="{{ $history->payment_mode }}"
                                                        data-remarks="{{ $history->remarks }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPaymentModal">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-link btn-danger btn-sm deletePaymentBtn"
                                                        data-id="{{ $history->id }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deletePaymentModal">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </td>
                                            @endisset
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    </div>
                </div>

                @if (($histories ?? collect())->isNotEmpty())
                    <div class="card subcontractor-history-card mt-4">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3 subcontractor-total-table">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL PAYMENT</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info">{{ number_format((float) ($paidAmount ?? 0), 2) }}</h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            @if (!isset($siteId) && $showPayment)
                <div class="card subcontractor-history-card" id="paymentDetails">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <h4 class="card-title mb-0">SubContractor Payment Detail</h4>
                            <button class="btn btn-success" id="addButton" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fa fa-plus"></i> Add Payment
                            </button>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            {{ session()->forget('success') }}
                        @endif

                        <form id="subcontractorPayDetailForm" action="{{ route('subpaydetail.update') }}" method="POST" class="w-100">
                            @csrf
                            <input type="hidden" name="subcontractor_id" value="{{ $subcontractorId }}">

                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="total_counts" class="fw-bold">Total Counts</label>
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <div class="form-group">
                                        <input type="text" id="total_counts" class="form-control"
                                            value="{{ $totalCounts ?? 0 }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mt-5">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="total_amount" class="fw-bold">Total Amount</label>
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <div class="form-group">
                                        <input type="text" id="total_amount" name="total_amount" class="form-control"
                                            value="{{ $totalAmount }}" readonly>
                                    </div>
                                    @error('total_amount')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="paid_amount" class="fw-bold">Paid Amount</label>
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <div class="form-group">
                                        <input type="text" id="paid_amount" name="paid_amount" class="form-control"
                                            value="{{ $paidAmount ?? 0 }}" readonly>
                                    </div>
                                    @error('paid_amount')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="balance_amount" class="fw-bold">Balance Amount</label>
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <div class="form-group">
                                        <input type="text" id="balance_amount" name="balance_amount" class="form-control"
                                            value="{{ $balanceAmount ?? 0 }}" readonly>
                                    </div>
                                    @error('balance_amount')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row justify-content-center mt-4">
                                <div class="col-lg-10 offset-lg-2">
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-primary px-5">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="fw-mediumbold" id="modalTitle">Add Payment</span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="paymentForm" action="{{ route('subpayment.add') }}" method="POST">
                        @csrf
                        <input type="hidden" id="subcontractor_id" name="subcontractor_id" value="{{ $subcontractorId }}">
                        @isset($siteId)
                            <input type="hidden" id="site_id" name="site_id" value="{{ $siteId }}">
                        @endisset

                        <!-- Name -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="date">Date</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="date" name="date" type="date" class="form-control" />
                                @error('date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Mobile -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="payment">Payment</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="payment" name="payment" type="text" class="form-control" 
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '');"/>
                                @error('payment')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Site Utilities -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="payment_mode">Payment Mode</label>
                            </div>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <select class="form-select form-control" name="payment_mode" id="payment_mode">
                                        <option value="">Select Payment Mode</option>
                                        <option value="Online">Online</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Net Banking">Net Banking</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                </div>
                                @error('payment_mode')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="remarks">Remarks</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea id="remarks" name="remarks" class="form-control" rows="3"></textarea>
                                @error('remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary" id="saveButton">Add</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @isset($siteId)
        <!-- Export History Modal -->
        <div class="modal fade" id="exportHistoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="GET" action="{{ $exportRoute ?? route('subcontractor.pettyCash.export', ['siteId' => $siteId]) }}" class="js-export-modal-form">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $exportModalTitle ?? 'Export Petty Cash' }}</h5>
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
    @endisset

    @isset($siteId)
        <!-- Edit Payment Modal -->
        <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="editPaymentForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header border-0">
                            <h5 class="modal-title">Edit Payment</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-2">
                                    <label for="edit_date">Date</label>
                                </div>
                                <div class="col-lg-10">
                                    <input id="edit_date" name="date" type="date" class="form-control" required>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-2">
                                    <label for="edit_payment">Payment</label>
                                </div>
                                <div class="col-lg-10">
                                    <input id="edit_payment" name="payment" type="text" class="form-control"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '');" required>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-2">
                                    <label for="edit_payment_mode">Payment Mode</label>
                                </div>
                                <div class="col-lg-10">
                                    <select class="form-select form-control" name="payment_mode" id="edit_payment_mode" required>
                                        <option value="">Select Payment Mode</option>
                                        <option value="Online">Online</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Net Banking">Net Banking</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-2">
                                    <label for="edit_remarks">Remarks</label>
                                </div>
                                <div class="col-lg-10">
                                    <textarea id="edit_remarks" name="remarks" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Payment Modal -->
        <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="deletePaymentForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this payment?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endisset

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 300);
            }

        });

        $(document).ready(function() {
            $('.editPaymentBtn').on('click', function() {
                const id = $(this).data('id');
                $('#editPaymentForm').attr('action', '/admin/subpayment-update/' + id);
                $('#edit_date').val($(this).data('date'));
                $('#edit_payment').val($(this).data('payment'));
                $('#edit_payment_mode').val($(this).data('payment-mode'));
                $('#edit_remarks').val($(this).data('remarks') || '');
            });

            $('.deletePaymentBtn').on('click', function() {
                const id = $(this).data('id');
                $('#deletePaymentForm').attr('action', '/admin/subpayment-delete/' + id);
            });

            $('#paymentForm').on('submit', function(e) {
                e.preventDefault();
                $('#loadingSpinner').removeClass('d-none');

                let form = $(this);
                let formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#loadingSpinner').addClass('d-none');
                        if (response.status === 'success') {
                             // Close the Bootstrap 5 modal using native JS
                            const modalEl = document.getElementById('addModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            modalInstance.hide();
                            // Open WhatsApp tabs with delay
                            //window.open(response.whatsapp_url, '_blank');
                            form[0].reset();
                            // Optionally reload the page (if needed)
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        $('#loadingSpinner').addClass('d-none');
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let message = Object.values(errors).map(e => e[0]).join("\n");
                            alert("Validation Errors:\n" + message);
                        } else {
                            alert(xhr.responseJSON?.message || "Payment could not be saved. Please try again.");
                        }
                    }
                });
            });
        });
    </script>
    <style>
        .subcontractor-history-card {
            border-radius: 8px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        }

        .subcontractor-detail-table th,
        .subcontractor-detail-table td {
            padding: 16px 24px;
        }

        .subcontractor-detail-table thead th {
            color: #000;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .subcontractor-total-table {
            max-width: 760px;
        }

        .subcontractor-total-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header .breadcrumbs {
            align-items: center;
        }

    </style>
@endsection
