@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Add {{ ucfirst($subcontractorType) }} Service</h3>
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

                    <form id="requestForm" action="{{ route('add.service') }}" method="POST" class="container">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <input type="hidden" name="subcontractor_id" id="subcontractor_id">

                        <input type="hidden" name="subcontractor_type" value="{{ ucfirst($subcontractorType) }}">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="subcontractor_name" class="fw-bold">Subcontractor Name</label>
                                </div>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <div class="form-group">
                                    <input type="text" id="subcontractor_name" name="subcontractor_name" class="form-control"
                                        placeholder="Type Subcontractor Name..." autocomplete="off">
                                    <div id="subcontractor_suggestions" class="list-group position-absolute w-100"
                                        style="z-index: 1000; display: none;"></div>
                                </div>
                                @error('subcontractor_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="subcontractor_mobile" class="fw-bold">Subcontractor Mobile No</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" id="subcontractor_mobile" name="subcontractor_mobile" class="form-control"
                                        placeholder="Mobile Number">
                                </div>
                                @error('subcontractor_mobile')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="no_counts" class="fw-bold">No Of Counts</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" id="no_counts" name="no_counts" class="form-control"
                                        placeholder="No Of Counts">
                                </div>
                                @error('no_counts')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="subcontractor_address" class="fw-bold">Subcontractor Address</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <textarea id="subcontractor_address" name="subcontractor_address" class="form-control" rows="2" placeholder="Subcontractor Address"></textarea>
                                </div>
                                @error('subcontractor_address')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                        @endphp
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

                        <!-- Price -->
                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="amount" name="amount" type="number" class="form-control no-arrow"
                                        min="0" step="0.01" placeholder="Enter Amount"
                                        oninput="document.getElementById('amount_words').innerText = numberToWordsIndian(this.value);" />
                                    <small id="amount_words" class="form-text text-muted"></small>
                                </div>
                                @error('amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="remarks" class="fw-bold">Remarks</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <textarea id="remarks" name="remarks" class="form-control" rows="3"
                                        placeholder="Enter remarks">{{ old('remarks') }}</textarea>
                                </div>
                                @error('remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary w-100">Send Service to SubContractor WhatsApp
                                        <i class="fab fa-whatsapp me-1"></i> </button>
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
        //Subcontractor search
        $(document).ready(function() {
            $('#subcontractor_name').on('input', function() {
                let query = $(this).val();
                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('subcontractors.search') }}",
                        type: 'GET',
                        data: {
                            name: query
                        },
                        success: function(data) {
                            let suggestions = '';
                            data.forEach(function(subcontractor) {
                                suggestions += `
                        <a href="#" 
                            class="list-group-item list-group-item-action subcontractor-option" 
                            data-id="${subcontractor.id}" 
                            data-name="${subcontractor.name}" 
                            data-mobile="${subcontractor.mobile_no}"
                            data-address="${subcontractor.address}">
                            ${subcontractor.name}
                        </a>`;
                            });
                            $('#subcontractor_suggestions').html(suggestions).show();
                        }
                    });
                } else {
                    $('#subcontractor_suggestions').hide();
                }
            });

            // Select subcontractor from suggestion
            $(document).on('click', '.subcontractor-option', function(e) {
                e.preventDefault();
                $('#subcontractor_name').val($(this).data('name'));
                $('#subcontractor_mobile').val($(this).data('mobile'));
                $('#subcontractor_id').val($(this).data('id'));
                $('#subcontractor_address').val($(this).data('address'));
                $('#subcontractor_suggestions').hide();
            });

            // Hide suggestions when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#subcontractor_name, #subcontractor_suggestions').length) {
                    $('#subcontractor_suggestions').hide();
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
                                if (response.whatsapp_url) {
                                    window.open(response.whatsapp_url, '_blank');
                                }
                                form[0].reset();

                                // Redirect
                                setTimeout(function() {
                                    const siteId = "{{ $siteId }}";
                                    const subcontractorType = "{{ $subcontractorType }}";
                                    const enteredDate = form.find('[name="date"]').val();
                                    let url = "/admin/public/admin/subcontractor/" +
                                        siteId + "/" + subcontractorType;
                                    if (enteredDate) {
                                        url += "?month=" + enteredDate.slice(0, 7);
                                    }
                                    window.location.href = url;
                                }, 500);
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
