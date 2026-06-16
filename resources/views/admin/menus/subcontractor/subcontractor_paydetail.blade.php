@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="px-3"> {{-- Added padding on both left and right --}}
            <div class="row align-items-center mb-4 mt-3">
                <div class="col-lg-6">
                    <h3 class="pb-2">{{ $title ?? 'SubContractor Payment Detail' }}</h3>
                </div>
                <div class="col-lg-6 text-end">
                    @isset($backRoute)
                        <a href="{{ $backRoute }}" class="btn btn-outline-primary me-2 mb-2">
                            Back
                        </a>
                    @endisset
                    <button class="btn btn-success me-2 mb-2" id="addButton" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fa fa-plus"></i> Add Payment
                    </button>
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

                    <div class="card-header d-flex align-items-center justify-content-between bg-white px-0 pt-0">
                        <h4 class="card-title mb-0">Payment History</h4>
                    </div>

                    @if (($histories ?? collect())->isEmpty())
                        <p class="text-center mt-3 mb-0">No payment history found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Date</th>
                                        <th>Payment Mode</th>
                                        <th>Remarks</th>
                                        <th class="text-end">Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $history)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $history->date }}</td>
                                            <td>{{ $history->payment_mode }}</td>
                                            <td>{{ $history->remarks ?? '-' }}</td>
                                            <td class="text-end">{{ number_format((float) $history->payment, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total Payment</th>
                                        <th class="text-end">{{ number_format((float) ($paidAmount ?? 0), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
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
                    <form id="paymentForm" action="{{ route('subpayment.add') }}" method="POST">
                        @csrf
                        <input type="hidden" id="subcontractor_id" name="subcontractor_id" value="{{ $subcontractorId }}">

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

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 300);
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
@endsection
