@extends('layouts.app')

@section('content')
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
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('subcontractor.dashboard') }}">SubContractor Dashboard</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">{{ $subcontractor->subcontractors ?: 'Site Utilities' }}</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('subcontractor.payDetailForm', ['subcontractorId' => $subcontractorId]) }}">{{ $subcontractor->name }}</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">Payment History</a>
                        </li>
                    </ul>
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card payment-history-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="card-title mb-0">SubContractor Payment History</h4>
                            </div>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subPaymentExportModal">
                                Export Excel
                            </button>
                        </div>

                        <!-- Blade alert for success -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            {{ session()->forget('success') }} {{-- Clear session --}}
                        @endif

                        @if ($histories->isEmpty())
                            <p class="text-center mt-3">No SubContractors found. Please add a subContractor.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="table table-striped table-borderless payment-history-table">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Date</th>
                                                <th>Total Payment</th>
                                                <th>Payment Mode</th>
                                                <th>Remarks</th>
                                                <th style="width:10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($histories as $index => $history)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $history->date ? \Carbon\Carbon::parse($history->date)->format('d-m-Y') : '-' }}</td>
                                                    <td>{{ number_format((float) $history->payment, 2) }}</td>
                                                    <td>{{ $history->payment_mode ?? '-' }}</td>
                                                    <td>{{ $history->remarks ?? '-' }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button" class="btn btn-link btn-primary btn-sm editPaymentBtn"
                                                                data-id="{{ $history->id }}"
                                                                data-date="{{ $history->date }}"
                                                                data-payment="{{ $history->payment }}"
                                                                data-payment_mode="{{ $history->payment_mode }}"
                                                                data-remarks="{{ $history->remarks }}"
                                                                data-bs-toggle="modal" data-bs-target="#editPaymentModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-link btn-danger btn-sm deletePaymentBtn"
                                                                data-id="{{ $history->id }}" data-bs-toggle="modal" data-bs-target="#deletePaymentModal">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card payment-history-card mt-4">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3 payment-total-table">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL AMOUNT</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info" id="totalUnits">
                                                {{ number_format($paidAmount, 2) }}</h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="subPaymentExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="GET" action="{{ route('subpayment.history.export', ['subcontractorId' => $subcontractorId]) }}" class="js-export-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Export SubContractor Payment History</h5>
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

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPaymentForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="payment_id" id="edit_payment_id" value="{{ old('payment_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" value="{{ old('date') }}" id="edit_payment_date" class="form-control" required>
                            @error('date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment</label>
                            <input type="text" name="payment" value="{{ old('payment') }}" id="edit_payment_amount" class="form-control"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '');" required>
                            @error('payment')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Mode</label>
                            <select class="form-select" name="payment_mode" id="edit_payment_mode" required>
                                <option value="">Select Payment Mode</option>
                                <option value="Online" {{ old('payment_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                                <option value="Cheque" {{ old('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="Check" {{ old('payment_mode') == 'Check' ? 'selected' : '' }}>Check</option>
                                <option value="Net Banking" {{ old('payment_mode') == 'Net Banking' ? 'selected' : '' }}>Net Banking</option>
                                <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            @error('payment_mode')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="edit_payment_remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
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
                        Are you sure you want to delete this payment?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
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

            document.querySelectorAll('.editPaymentBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const date = this.getAttribute('data-date');
                    const form = document.getElementById('editPaymentForm');
                    form.action = "{{ route('subpayment.update', ':id') }}".replace(':id', id);
                    document.getElementById('edit_payment_id').value = id;
                    document.getElementById('edit_payment_date').value = date ? date.substring(0, 10) : '';
                    document.getElementById('edit_payment_amount').value = this.getAttribute('data-payment');
                    document.getElementById('edit_payment_mode').value = this.getAttribute('data-payment_mode');
                    document.getElementById('edit_payment_remarks').value = this.getAttribute('data-remarks');
                });
            });

            document.getElementById('editPaymentForm').addEventListener('submit', function() {
                const idField = document.getElementById('edit_payment_id');
                if (idField.value) {
                    this.action = "{{ route('subpayment.update', ':id') }}".replace(':id', idField.value);
                }
            });

            document.querySelectorAll('.deletePaymentBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const form = document.getElementById('deletePaymentForm');
                    form.action = "{{ route('subpayment.delete', ':id') }}".replace(':id', id);
                });
            });
        });
    </script>
    <style>
        .payment-history-card {
            border-radius: 8px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        }

        .payment-history-table th,
        .payment-history-table td {
            padding: 16px 24px;
        }

        .payment-history-table thead th {
            color: #000;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .payment-total-table {
            max-width: 760px;
        }

        .payment-total-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
    

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
                myModal.show();
            });
        </script>
    @endif

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
@endsection
