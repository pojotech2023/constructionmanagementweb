@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Update Customer</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.list') }}">Customer Management</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Update Customer</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h4 class="card-title">Customer Form</h4>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Blade alert for success -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                                {{ session()->forget('success') }} {{-- Clear session --}}
                            @endif

                            <div class="row">
                                <form id="customerForm" action="{{ route('customer.update', $customer->id) }}"
                                    method="POST" class="container">
                                    @csrf

                                    @method('PATCH')

                                    <!-- SiteId hidden pass -->
                                    <input type="hidden" name="site_id" value="{{ old('site_id', $customer->site_id) }}">

                                    <!-- Name & Mobile -->
                                    <div class="row align-items-center mt-5">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ old('name', $customer->name) }}">
                                            </div>
                                            @error('name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Mobile Number -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="mobile_no">Mobile Number</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" name="mobile_no" id="mobile_no" class="form-control"
                                                    value="{{ old('mobile_no', $customer->mobile_no) }}" maxlength="10"
                                                    minlength="10" pattern="\d{10}"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                                            </div>
                                            @error('mobile_no')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Email & DOB -->
                                    <div class="row align-items-center mt-5">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" name="email" class="form-control"
                                                    value="{{ old('email', $customer->email) }}">
                                            </div>
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- DOB -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="dob">Date of Birth</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="date" name="dob" id="dob" class="form-control"
                                                    value="{{ old('dob', $customer->dob) }}">
                                            </div>
                                            @error('dob')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                   

                                        <!-- Address -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <textarea id="address" name="address" class="form-control" rows="4">{{ old('address', $customer->address) }}</textarea>
                                            </div>
                                            @error('address')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="card-action text-end">
                                        <button class="btn btn-success">Submit</button>
                                        <button class="btn btn-danger">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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


    <script>
        //Spinner and Alert
        document.addEventListener("DOMContentLoaded", function() {
            let alert = document.querySelector('.alert');
            const form = document.getElementById('customerForm');
            const spinner = document.getElementById('loadingSpinner');

            //Success alert handling
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    window.location.href = "{{ route('customer.list') }}";
                }, 500);
            }

            //Show spinner only on site form submission
            if (form && spinner) {
                form.addEventListener('submit', function(event) {
                    spinner.classList.remove('d-none'); //Show spinner
                });
            }
        });
    </script>
@endsection
