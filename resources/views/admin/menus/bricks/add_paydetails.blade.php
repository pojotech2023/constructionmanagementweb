@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-10">
                <h3 class="text-center pb-4 mt-3">Add Payment Details</h3>
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

                    <form id="requestForm" action="{{ route('add.payment') }}" method="POST" class="container">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <input type="hidden" name="vendor_id" id="vendor_id">

                        <input type="hidden" name="material_type" value="Bricks">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="vendor_name" class="fw-bold">Vendor Name</label>
                                </div>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <div class="form-group">
                                    <input type="text" id="vendor_name" name="vendor_name" class="form-control"
                                        placeholder="Type Vendor Name..." autocomplete="off">
                                    <div id="vendor_suggestions" class="list-group position-absolute w-100"
                                        style="z-index: 1000; display: none;"></div>
                                </div>
                                @error('vendor_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="date" class="fw-bold">Date</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="date" class="form-control" name="date">
                                </div>
                                @error('date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <!-- Quantity -->
                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="quantity" class="fw-bold">Quantity</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="quantity" name="quantity" type="text" class="form-control"
                                        placeholder="Enter quantity" />
                                </div>
                                @error('quantity')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Total Amount -->
                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="total_amount">Total Amount </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="total_amount" name="total_amount" type="text" class="form-control"
                                        placeholder="Enter price" oninput="calculatePending()" />
                                    <small id="total_amount_words" class="form-text text-muted"></small>
                                </div>
                                @error('total_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Settled Amount -->
                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="settled_amount">Settled Amount </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="settled_amount" name="settled_amount" type="text" class="form-control"
                                        placeholder="Enter price" oninput="calculatePending()" />
                                    <small id="settled_amount_words" class="form-text text-muted"></small>
                                </div>
                                @error('settled_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Pending Amount -->
                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pending_amount">Pending Amount </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pending_amount" name="pending_amount" type="text" class="form-control"
                                        placeholder="Pending price" readonly />
                                    <small id="pending_amount_words" class="form-text text-muted"></small>
                                </div>
                                @error('pending_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="remarks" class="fw-bold">Remarks</label>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter remarks here..."></textarea>
                                </div>
                                @error('remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-2 ms-auto">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
                            </div>
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
        // document.addEventListener("DOMContentLoaded", function() {
        //     let alert = document.querySelector('.alert');
        //     const form = document.getElementById('requestForm');
        //     const spinner = document.getElementById('loadingSpinner');

        //     if (alert) {
        //         setTimeout(() => {
        //             alert.classList.remove('show');
        //             alert.classList.add('fade');
        //             window.location.href = "";
        //         }, 500);
        //     }

        //     if (form && spinner) {
        //         form.addEventListener('submit', function(event) {
        //             spinner.classList.remove('d-none'); 
        //         });
        //     }
        // });

        //Vendor search
        $(document).ready(function() {

            $('#vendor_name').on('input', function() {
                let query = $(this).val();
                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('vendors.search') }}",
                        type: 'GET',
                        data: {
                            name: query
                        },
                        success: function(data) {
                            let suggestions = '';
                            data.forEach(function(vendor) {
                                suggestions += `
                        <a href="#" 
                            class="list-group-item list-group-item-action vendor-option" 
                            data-id="${vendor.id}" 
                            data-name="${vendor.name}">
                            ${vendor.name}
                        </a>`;
                            });
                            $('#vendor_suggestions').html(suggestions).show();
                        }
                    });
                } else {
                    $('#vendor_suggestions').hide();
                }
            });

            // Select vendor from suggestion
            $(document).on('click', '.vendor-option', function(e) {
                e.preventDefault();
                $('#vendor_name').val($(this).data('name'));
                $('#vendor_id').val($(this).data('id'));
                $('#vendor_suggestions').hide();
            });

            // Hide suggestions when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#vendor_name, #vendor_suggestions').length) {
                    $('#vendor_suggestions').hide();
                }
            });

            // Form submit handler for material request form
            $(document).ready(function() {
                $('#requestForm').on('submit', function(e) {
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
                                // alert(response.message);
                                window.open(response.whatsapp_url, '_blank');
                                form[0].reset();
                            }
                        },
                        error: function(xhr) {
                            $('#loadingSpinner').addClass('d-none');
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let message = Object.values(errors).map(e => e[0]).join(
                                    "\n");
                                // alert("Validation Errors:\n" + message);
                            } else {
                                alert("Something went wrong!");
                            }
                        }
                    });
                });
            });

        });
        //pending amount
        function calculatePending() {
            var total = parseFloat(document.getElementById('total_amount').value) || 0;
            var settled = parseFloat(document.getElementById('settled_amount').value) || 0;
            var pending = total - settled;
            if (pending < 0) pending = 0; // in case settled > total
            document.getElementById('pending_amount').value = pending.toFixed(2);

            document.getElementById('total_amount_words').innerText = numberToWordsIndian(total);
            document.getElementById('settled_amount_words').innerText = numberToWordsIndian(settled);
            document.getElementById('pending_amount_words').innerText = numberToWordsIndian(pending);
        }
    </script>
@endsection
