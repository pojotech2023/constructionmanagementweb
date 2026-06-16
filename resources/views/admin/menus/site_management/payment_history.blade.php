@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-3">Site</h3>
                    <ul class="breadcrumbs mb-0">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="icon-home"></i>
                            </a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('site.paymentDetail', $site->id) }}">Payment Detail</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">Payment History</a>
                        </li>
                    </ul>
                </div>
                {{-- Back button removed as requested --}}
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="card-title mb-0">Site Payment History</h4>
                            </div>
                            <div class="fw-bold text-primary">
                                Budget Amount: {{ number_format($budgetAmount, 2) }}
                            </div>
                        </div>

                        @if ($histories->isEmpty())
                            <p class="text-center mt-3">No payment history found.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Date</th>
                                                <th>Payment</th>
                                                <th>Payment Mode</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($histories as $history)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $history->date }}</td>
                                                    <td>{{ number_format((float) $history->payment, 2) }}</td>
                                                    <td>{{ $history->payment_mode }}</td>
                                                    <td>{{ $history->remarks ?: '-' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-link btn-primary btn-sm me-1 edit-payment-btn" title="Edit"
                                                            data-id="{{ $history->id }}"
                                                            data-payment="{{ $history->payment }}"
                                                            data-date="{{ \Carbon\Carbon::parse($history->date)->format('Y-m-d') }}"
                                                            data-mode="{{ $history->payment_mode }}"
                                                            data-remarks="{{ $history->remarks }}"
                                                            data-update-url="{{ route('site.payment.update', $history->id) }}"
                                                            data-bs-toggle="modal" data-bs-target="#editPaymentModal">
                                                            <i class="fa fa-edit"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-link btn-danger btn-sm delete-payment-btn" title="Delete"
                                                            data-delete-url="{{ route('site.payment.delete', $history->id) }}"
                                                            data-bs-toggle="modal" data-bs-target="#deletePaymentModal">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3" style="width: 50%">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">BALANCE AMOUNT</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info">
                                                {{ number_format($balanceAmount, 2) }}
                                            </h5>
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
@endsection

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPaymentForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Payment</label>
                            <input type="number" step="0.01" name="payment" id="edit_payment" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Mode</label>
                            <input type="text" name="payment_mode" id="edit_mode" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="edit_remarks" class="form-control" rows="2"></textarea>
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

    <!-- Delete Confirmation Modal (centered) -->
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
                        <div class="mb-3">
                            <label for="delete_reason" class="form-label">Reason (optional)</label>
                            <input type="text" id="delete_reason" name="delete_reason" class="form-control" placeholder="Enter reason">
                        </div>
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
        // Use vanilla JS so script works even if jQuery loads later
        document.addEventListener('click', function (e) {
            var editBtn = e.target.closest && e.target.closest('.edit-payment-btn');
            if (editBtn) {
                var updateUrl = editBtn.getAttribute('data-update-url');
                var payment = editBtn.getAttribute('data-payment') || '';
                var rawDate = editBtn.getAttribute('data-date') || '';
                var cleanDate = rawDate.toString().split(' ')[0];
                var mode = editBtn.getAttribute('data-mode') || '';
                var remarks = editBtn.getAttribute('data-remarks') || '';

                var editForm = document.getElementById('editPaymentForm');
                if (editForm) editForm.action = updateUrl;
                var inputPayment = document.getElementById('edit_payment');
                if (inputPayment) inputPayment.value = payment;
                var inputDate = document.getElementById('edit_date');
                if (inputDate) inputDate.value = cleanDate;
                var inputMode = document.getElementById('edit_mode');
                if (inputMode) inputMode.value = mode;
                var inputRemarks = document.getElementById('edit_remarks');
                if (inputRemarks) inputRemarks.value = remarks;
            }

            var delBtn = e.target.closest && e.target.closest('.delete-payment-btn');
            if (delBtn) {
                var deleteUrl = delBtn.getAttribute('data-delete-url');
                var deleteForm = document.getElementById('deletePaymentForm');
                if (deleteForm) {
                    deleteForm.action = deleteUrl;
                    // clear reason input each time
                    var reasonInput = document.getElementById('delete_reason');
                    if (reasonInput) reasonInput.value = '';
                }
            }
        });
    </script>
