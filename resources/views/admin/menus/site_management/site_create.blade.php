@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Add Site</h3>
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
                        <a href="{{ route('sitemanagement.list') }}">Site</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Site Form</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h4 class="card-title">Site Details</h4>
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
                                <form id="siteForm" action="{{ route('sitemanagement.add') }}" method="POST"
                                    enctype="multipart/form-data" class="container">
                                    @csrf
                                    <!-- Site Name -->
                                    <div class="row align-items-center mt-5">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="site_name">Site Name</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="site_name" value="{{ old('site_name') }}">
                                            </div>
                                            @error('site_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Site Images -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="site_img">Site Image</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                @if (old('temp_site_img'))
                                                    <input type="hidden" name="temp_site_img" value="{{ old('temp_site_img') }}">
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . old('temp_site_img')) }}" alt="Selected Site Image"
                                                            style="max-width: 140px; max-height: 100px; object-fit: contain;">
                                                    </div>
                                                @endif
                                                <input type="file" name="site_img" id="site_img" class="form-control">
                                            </div>
                                            @error('site_img')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Location & Value -->
                                    <div class="row align-items-center">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="location">Location</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <textarea id="location" name="location" class="form-control" rows="4">{{ old('location') }}</textarea>
                                            </div>
                                            @error('location')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="budget_amount">Budget Amount</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="number" class="form-control" name="budget_amount" id="budget_amount" min="0" step="0.01" value="{{ old('budget_amount') }}">
                                            </div>
                                            @error('budget_amount')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="flat_area">Flat Area</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="flat_area" value="{{ old('flat_area') }}">
                                            </div>
                                            @error('flat_area')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                           <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="flat_area">Built-up Area</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="built_up_area" value="{{ old('built_up_area') }}">
                                            </div>
                                            @error('built_up_area')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                          <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="duration">Duration</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" name="duration" id="duration" class="form-control" value="{{ old('duration') }}">
                                            </div>
                                            @error('duration')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        
                        <div class="col-lg-2"><label>Assigned by Supervisor</label></div>
                        <div class="col-lg-4">
                            <select name="supervisor_id" class="form-control">
                                <option value="">Select Supervisor</option>
                                @foreach ($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supervisor_id')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                                        <!-- Valaue -->
                                        <!--<div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="value">Value</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="number" name="value" id="value"
                                                    class="form-control no-arrow" min="0" step="1">
                                            </div>
                                            @error('value')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>-->

                                    <!-- Duration & Settled Amount -->
                                    <div class="row align-items-center">
                                       
                                        <!-- Settled Amount -->
                                        <!--<div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="settled_amnt">Settled Amount</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="number" name="settled_amnt" id="value"
                                                    class="form-control no-arrow" min="0" step="1">
                                            </div>
                                            @error('settled_amnt')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>-->

                                    <!-- Pending Amount -->
                                   <!-- <div class="row align-items-center">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="pending_amnt">Pending Amount</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="number" name="pending_amnt" class="form-control no-arrow"
                                                    min="0" step="1">
                                            </div>
                                            @error('pending_amnt')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>-->

                                    {{-- Customer Form --}}
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <h4 class="card-title">Customer Details</h4>
                                        </div>
                                    </div>

                                    <!-- Name & Mobile -->
                                    <div class="row align-items-center mt-5">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="name">Name <span style="color:red">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                                            </div>
                                            @error('name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Mobile Number -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="mobile_no">Mobile Number <span style="color:red">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="text" name="mobile_no" id="mobile_no"
                                                    class="form-control" maxlength="10" minlength="10" pattern="\d{10}"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" value="{{ old('mobile_no') }}">
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
                                                <input type="text" name="email" class="form-control" value="{{ old('email') }}">
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
                                                <input type="date" name="dob" id="dob"
                                                    class="form-control" value="{{ old('dob') }}">
                                            </div>
                                            @error('dob')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Marriage date & Address -->
                                   <!-- <div class="row align-items-center mt-5">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="marriage_date">Marriage Date</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <input type="date" name="marriage_date" class="form-control">
                                            </div>
                                            @error('marriage_date')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>-->

                                        <!-- Address -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <textarea id="address" name="address" class="form-control" rows="4">{{ old('address') }}</textarea>
                                            </div>
                                            @error('address')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="card-action text-end">
                                        <button class="btn btn-success" id="saveButton">Submit</button>
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
        document.addEventListener("DOMContentLoaded", function() {
            const siteForm = document.getElementById('siteForm');
            const saveButton = document.getElementById("saveButton");
            const spinner = document.getElementById("loadingSpinner");

            //Show Spinner and Disable Form on Submit
            siteForm.addEventListener("submit", function() {
                spinner.classList.remove("d-none"); // Show spinner
                saveButton.disabled = true; // Disable button to prevent multiple clicks
            });

            //Auto-hide success alert after 3 seconds
            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                    window.location.href = "/admin/public/admin/site-management";

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
