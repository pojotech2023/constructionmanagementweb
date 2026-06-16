@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-10">
                <h3 class="text-center pb-4 mt-3">Add Bricks Request</h3>
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

                    <form id="requestForm" action="{{ route('add.request') }}" method="POST" class="container">
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
                                    <label for="vendor_mobile" class="fw-bold">Requestor Mobile No</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" id="vendor_mobile" name="vendor_mobile" class="form-control"
                                        placeholder="Mobile Number">
                                </div>
                                @error('vendor_mobile')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="vendor_address" class="fw-bold">Requestor Address</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <textarea id="vendor_address" name="vendor_address" class="form-control" rows="2" placeholder="Vendor Address"></textarea>
                                </div>
                                @error('vendor_address')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="quantity" class="fw-bold">Quantity</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="quantity" placeholder="Enter quantity">
                                </div>
                                @error('quantity')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="unit" class="fw-bold">Unit</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-select form-control" name="unit"
                                        id="unit">
                                        <option value="">Select Unit</option>
                                        <option value="Load">Load</option>
                                        <option value="Pack">Pack</option>
                                        <option value="Ltr">Ltr</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Pieces"> Pieces</option>
                                        <option value="mqueeb">mqueeb</option>
                                        <option value="CFT">CFT</option>
                                        <option value="unit">unit</option>
                                        <option value="Bag">Bag</option>
                                        <option value="Tons">Tons</option>
                                        <option value="numbers">numbers</option>
                                        


                                    </select>
                                </div>
                                @error('unit')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="delivery_needed_by" class="fw-bold">Delivery Needed By</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-select form-control" name="delivery_needed_by"
                                        id="delivery_needed_by">
                                        <option value="">Select Delivery Needed By</option>
                                        <option value="Immediate">Immediate</option>
                                        <option value="Later">Later</option>
                                        <option value="One week">One week</option>
                                    </select>
                                </div>
                                @error('delivery_needed_by')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-4">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="amount" class="fw-bold">Amount</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="amount" id="amount"
                                        placeholder="Enter amount">
                                </div>
                                @error('amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                      

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
                        <div class="form-group text-center">
                            @if (session('pdf_download_link'))
                                <p>Click the link below to download the PDF:</p>
                                <a href="{{ session('pdf_download_link') }}" target="_blank"
                                    class="btn btn-primary">Download PDF</a>
                            @endif
                        </div>
                        <div class="row justify-content-center mt-4">
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary w-100">Send WhatsApp to Vendor</button>
                                </div>
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
                            data-name="${vendor.name}" 
                            data-mobile="${vendor.mobile_no}"
                            data-address="${vendor.address}">
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
                $('#vendor_mobile').val($(this).data('mobile'));
                $('#vendor_id').val($(this).data('id'));
                $('#vendor_address').val($(this).data('address'));
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
    </script>
@endsection
