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
                            <a href="{{ route('vendor.dashboard') }}">Vendor Dashboard</a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item">
                            <a href="#">{{ $vendor->site_utilities ?: 'Site Utilities' }}</a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item">
                            <a href="{{ route('vendor.payDetailForm', ['vendorId' => $vendorId]) }}">{{ $vendor->name }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <div>
                    <a href="{{ route('vendor.payDetailForm', ['vendorId' => $vendorId]) }}?show_payment=1" class="btn btn-success me-2 mb-2">
                        <i class="fa fa-credit-card"></i> Payment
                    </a>
                    <a href="{{ route('payment.history', ['vendorId' => $vendorId]) }}" class="btn btn-primary mb-2">
                        <i class="fa fa-history"></i> Payment History
                    </a>
                </div>
            </div>

            @unless ($showPayment)
                <div class="card vendor-history-card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <h4 class="card-title mb-0">Material Order Details</h4>
                        </div>

                        @if ($orders->isEmpty())
                            <p class="text-center mt-3 mb-0">No material orders found for this vendor.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-borderless align-middle mb-0 vendor-detail-table">
                                    <thead>
                                        <tr>
                                            <th>S.NO</th>
                                            <th>DATE</th>
                                            <th>SITE NAME</th>
                                            <th>QUANTITY</th>
                                            <th>AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $order->date ? \Carbon\Carbon::parse($order->date)->format('d-m-Y') : '-' }}</td>
                                                <td>{{ optional($order->site)->site_name ?? '-' }}</td>
                                                <td>{{ $order->quantity }}</td>
                                                <td>{{ number_format((float) $order->price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                @unless ($orders->isEmpty())
                    <div class="card vendor-history-card mt-4">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3 vendor-total-table">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL UNITS</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info">{{ $totalUnits }}</h5>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL AMOUNT</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info">{{ number_format((float) $totalAmount, 2) }}</h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endunless
            @endunless

            @if ($showPayment)
                <div class="card vendor-history-card" id="paymentDetails">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <h4 class="card-title mb-0">Vendor Payment Detail</h4>
                            <button class="btn btn-success" id="addButton" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fa fa-plus"></i> Add Payment
                            </button>
                        </div>

                    <!-- Blade alert for success -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif

                    <form id="vendorPayDetailForm" action="{{ route('paydetail.update') }}" method="POST"
                        class="w-100">

                        @csrf

                        <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $vendorId }}">

                        <div class="row align-items-center mt-4">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="total_units" class="fw-bold">Total Units</label>
                                </div>
                            </div>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <input type="text" id="total_units" name="total_units" class="form-control"
                                        value="{{ $totalUnits }}" readonly>
                                </div>
                                @error('total_units')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="total_unit_price" class="fw-bold">Total Amount</label>
                                </div>
                            </div>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="total_unit_price" id="total_unit_price"
                                        value="{{ $totalAmount }}" readonly>
                                    <small id="total_unit_price_words" class="form-text text-muted"></small>
                                </div>
                                @error('total_unit_price')
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
                                    <input type="text" class="form-control" name="paid_amount" id="paid_amount"
                                        value="{{ $paidAmount ?? 0 }}" readonly>
                                    <small id="paid_amount_words" class="form-text text-muted"></small>
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
                                    <input type="text" class="form-control" name="balance_amount" id="balance_amount"
                                        value="{{ $balanceAmount ?? 0 }}" readonly>
                                    <small id="balance_amount_words" class="form-text text-muted"></small>
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
    <div id="openingBalanceConfirmModal" class="opening-balance-modal d-none">
        <div class="opening-balance-backdrop"></div>
        <div class="opening-balance-dialog">
            <div class="opening-balance-icon">!</div>
            <h4 class="opening-balance-title">Confirm Opening Balance</h4>
            <p class="opening-balance-message" id="openingBalanceMessage">
                Are you sure you want to add this opening balance? Once added, this amount will be shown as the opening balance for this vendor account.
            </p>
            <div class="opening-balance-actions">
                <button type="button" class="btn btn-outline-secondary" id="cancelOpeningBalance">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmOpeningBalance">Yes, Add Opening Balance</button>
            </div>
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
                    <form id="paymentForm" action="{{ route('payment.add') }}" method="POST">
                        @csrf
                        <input type="hidden" id="vendor_id" name="vendor_id" value="{{ $vendorId }}">

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
                                oninput="this.value = this.value.replace(/[^0-9.]/g, ''); document.getElementById('payment_words').innerText = numberToWordsIndian(this.value);"/>
                                <small id="payment_words" class="form-text text-muted"></small>
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
                                        <option value="Check">Check</option>
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

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const vendorPayDetailForm = document.getElementById('vendorPayDetailForm');
            const openingBalanceInput = document.getElementById('opening_balance');
            const confirmModal = document.getElementById('openingBalanceConfirmModal');
            const confirmButton = document.getElementById('confirmOpeningBalance');
            const cancelButton = document.getElementById('cancelOpeningBalance');
            let allowVendorPayDetailSubmit = false;

            ['total_unit_price', 'paid_amount', 'balance_amount'].forEach(function(fieldId) {
                const field = document.getElementById(fieldId);
                const wordsEl = document.getElementById(fieldId + '_words');
                if (field && wordsEl) {
                    wordsEl.innerText = numberToWordsIndian(field.value);
                }
            });

            // Auto-hide success alert
            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 300);
            }

            if (vendorPayDetailForm && openingBalanceInput) {
                vendorPayDetailForm.addEventListener('submit', function(event) {
                    const openingBalance = openingBalanceInput.value.trim();

                    if (allowVendorPayDetailSubmit || openingBalance === '') {
                        return;
                    }

                    event.preventDefault();
                    confirmModal.classList.remove('d-none');
                });
            }

            if (confirmButton) {
                confirmButton.addEventListener('click', function() {
                    allowVendorPayDetailSubmit = true;
                    confirmModal.classList.add('d-none');
                    vendorPayDetailForm.submit();
                });
            }

            if (cancelButton) {
                cancelButton.addEventListener('click', function() {
                    confirmModal.classList.add('d-none');
                });
            }
        });

        $(document).ready(function() {
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
                            //Close the modal
                            $('#addModal').modal('hide');
                            // Open WhatsApp tabs with delay
                            //window.open(response.whatsapp_url, '_blank');
                            form[0].reset();
                            //Refresh the page after 1 second
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
                            alert("Something went wrong!");
                        }
                    }
                });
            });
        });
    </script>
    <style>
        .opening-balance-modal {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .opening-balance-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
        }

        .opening-balance-dialog {
            position: relative;
            width: min(520px, calc(100vw - 32px));
            padding: 28px;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            text-align: center;
            border: 1px solid #dbeafe;
        }

        .opening-balance-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 30px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(245, 158, 11, 0.24);
        }

        .opening-balance-title {
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .opening-balance-message {
            color: #475569;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .opening-balance-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .vendor-detail-table th,
        .vendor-detail-table td {
            padding: 16px 24px;
        }

        .vendor-detail-table thead th {
            color: #000;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .vendor-total-table {
            max-width: 760px;
        }

        .vendor-total-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header .breadcrumbs {
            align-items: center;
        }

        .vendor-history-card {
            border-radius: 8px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        }
    </style>
@endsection
