@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="px-3">
            <div class="row align-items-center mb-4 mt-3">
                <div class="col-lg-6">
                    <h3 class="pb-2">Payment Detail</h3>
                    <ul class="breadcrumbs mb-0">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item"><a href="{{ route('sitemanagement.list') }}">Site</a></li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item"><a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a></li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item"><a href="#">Payment Detail</a></li>
                    </ul>
                </div>
                <div class="col-lg-6 text-end">
                    <button class="btn btn-success me-2 mb-2" type="button" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fa fa-plus"></i> Add Payment
                    </button>
                    <a href="{{ route('site.payment.history', $site->id) }}" class="btn btn-primary mb-2">
                        <i class="fa fa-history"></i> Payment History
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-11">
                <div class="card shadow-lg p-4 ms-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                    <form class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="budget_amount" class="fw-bold">Budget Amount</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" id="budget_amount" class="form-control"
                                        value="{{ number_format($budgetAmount, 2, '.', '') }}" readonly>
                                </div>
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
                                    <input type="text" id="paid_amount" class="form-control"
                                        value="{{ number_format($paidAmount, 2, '.', '') }}" readonly>
                                </div>
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
                                    <input type="text" id="balance_amount" class="form-control"
                                        value="{{ number_format($balanceAmount, 2, '.', '') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sitePaymentForm" action="{{ route('site.payment.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="site_id" value="{{ $site->id }}">

                        <div class="row align-items-center mb-3">
                            <div class="col-lg-3">
                                <label for="payment_date">Date</label>
                            </div>
                            <div class="col-lg-9">
                                <input id="payment_date" name="date" type="date" class="form-control">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-lg-3">
                                <label for="payment_amount">Payment</label>
                            </div>
                            <div class="col-lg-9">
                                <input id="payment_amount" name="payment" type="text" class="form-control"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, ''); document.getElementById('payment_amount_words').innerText = numberToWordsIndian(this.value);">
                                <small id="payment_amount_words" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-lg-3">
                                <label for="payment_mode">Payment Mode</label>
                            </div>
                            <div class="col-lg-9">
                                <select class="form-select form-control" id="payment_mode" name="payment_mode">
                                    <option value="">Select Payment Mode</option>
                                    <option value="Online">Online</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Net Banking">Net Banking</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cash-B">Cash-B</option>
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-lg-3">
                                <label for="remarks">Remarks</label>
                            </div>
                            <div class="col-lg-9">
                                <textarea id="remarks" name="remarks" class="form-control" rows="3"></textarea>
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

    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#sitePaymentForm').on('submit', function(e) {
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
                            window.location.assign(response.redirect_url || "{{ route('site.paymentDetail', $site->id) }}");
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
@endsection
