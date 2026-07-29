@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Add New property</h3>
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

                    <form id="propertyForm" action="{{ route('property.add') }}" method="POST"
                        enctype="multipart/form-data" class="container">
                        @csrf

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="name" class="fw-bold">Property Name</label>
                                </div>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <div class="form-group">
                                    <input type="text" id="name" name="name" class="form-control"
                                        placeholder="Enter name">
                                </div>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="location" class="fw-bold">Property Location</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <textarea id="location" name="location" class="form-control" rows="4" placeholder="Enter location here..."></textarea>
                                </div>
                                @error('location')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="type" class="fw-bold">Property Type</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-select form-control" name="type" id="type">
                                        <option value="">Select Property Type</option>
                                        <option value="Plot">Plot</option>
                                        <option value="Land">Land</option>
                                        {{-- <option value="One week">One week</option> --}}
                                    </select>
                                </div>
                                @error('type')
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
                                    <input type="number" class="form-control no-arrow" min="0" step="0.01"
                                        name="amount" id="amount" placeholder="Enter amount"
                                        oninput="document.getElementById('amount_words').innerText = numberToWordsIndian(this.value);">
                                    <small id="amount_words" class="form-text text-muted"></small>
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
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter remarks here..."></textarea>
                                </div>
                                @error('remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-4">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="image" class="fw-bold">Image</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="file" class="form-control" name="image" id="image"
                                        accept=".jpg,.jpeg,.png,.webp ">
                                </div>
                                @error('image')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="row justify-content-center mt-4">
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary w-100">Share on Whatsapp
                                        <i class="fab fa-whatsapp me-1"></i>
                                    </button>
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
        $(document).ready(function() {
            $('#propertyForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let formData = new FormData(this); // needed for file upload

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Open WhatsApp tabs with delay
                        // response.whatsapp_urls.forEach(function(url, index) {
                        //     setTimeout(() => {
                        //         window.open(url, '_blank');
                        //     }, index * 500);
                        // });
                        window.open(response.whatsapp_url, '_blank');
                        // Reset form (including file input)
                        form[0].reset();

                        // Optionally, focus back on the first field
                        $('#name').focus();

                        // Redirect
                        setTimeout(function() {
                            window.location.href = "/admin/property-management";
                        }, 500);
                    },
                    error: function(xhr) {
                        $('#loadingSpinner').addClass('d-none');
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON?.errors || {};
                            let message = Object.values(errors).map(e => e[0]).join("\n");
                            alert("Validation Errors:\n" + message);
                        } else {
                            alert(xhr.responseJSON?.message || "Something went wrong!");
                        }
                    }
                });
            });
        });
    </script>
@endsection
