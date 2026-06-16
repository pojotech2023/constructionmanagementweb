@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="px-3"> {{-- Added padding on both left and right --}}
            <div class="row align-items-center mb-4 mt-3">
                <div class="col-lg-6">
                    <h3 class="pb-2">Vendor Payment Detail</h3>
                </div>
                <div class="col-lg-6 text-end">
                    <button class="btn btn-success me-2 mb-2" id="addButton" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fa fa-plus"></i> Add Payment
                    </button>
                    <a href="{{ route('payment.history', ['vendorId' => $vendorId]) }}" class="btn btn-primary mb-2">
                        <i class="fa fa-history"></i> Payment History
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-11">
                <div class="card shadow-lg p-4 ms-4">

                    <!-- Blade alert for success -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif

                    <form id="vendorPayDetailForm" action="{{ route('paydetail.update') }}" method="POST"
                        class="container">

                        @csrf

                        <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $vendorId }}">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="opening_balance" class="fw-bold">Opening Balance</label>
                                </div>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <div class="form-group">
                                    <input type="text" id="opening_balance" name="opening_balance" class="form-control"
                                        value="{{ $paydetail->opening_balance ?? '' }}"  oninput="this.value = this.value.replace(/[^0-9.]/g, '');">                                                                                                             
                                </div>
                                @error('opening_balance')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="total_units" class="fw-bold">Total Units</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
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
                                    <label for="total_unit_price" class="fw-bold">Total Unit Price</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="total_unit_price"
                                        value="{{ $totalAmount }}" readonly>
                                </div>
                                @error('total_unit_price')
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="balance_amount" id="balance_amount"
                                        value="{{ $paydetail->balance_amount ?? 0 }}" readonly>
                                </div>
                                @error('balance_amount')
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="paid_amount" id="paid_amount"
                                        value="{{ $paydetail->paid_amount ?? 0 }}" readonly>
                                </div>
                                @error('paid_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="row justify-content-center mt-4">
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
    </style>
@endsection
