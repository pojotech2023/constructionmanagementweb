@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Update Site</h3>
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
                                <h4 class="card-title">Site Form</h4>
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
                               <form id="siteForm" action="{{ route('sitemanagement.update', $site->id) }}" method="POST"
    enctype="multipart/form-data" class="container">
    @csrf
    @method('PATCH')

    <!-- Site Name -->
    <div class="row align-items-center mt-3">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="site_name">Site Name</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <input type="text" class="form-control" name="site_name"
                    placeholder="Enter Name" value="{{ old('site_name', $site->site_name ?? '') }}">
            </div>
            @error('site_name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Site Image -->
        <div class="col-lg-2">
            <div class="form-group">
                <label for="site_img">Site Image</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <input type="file" name="site_img" id="site_img" class="form-control">
                <img src="{{ $site->site_image_url }}" width="100" class="mt-2 rounded shadow-sm" alt="Site Image Preview">
            </div>
            @error('site_img')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Location & Duration -->
    <div class="row align-items-center">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="location">Location</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <textarea id="location" name="location" class="form-control" rows="3">{{ old('location', $site->location ?? '') }}</textarea>
            </div>
            @error('location')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-2">
            <div class="form-group">
                <label for="budget_amount">Contract Value</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <input type="number" name="budget_amount" id="budget_amount" class="form-control"
                    placeholder="Enter Contract Value" min="0" step="0.01" value="{{ old('budget_amount', $site->budget_amount ?? '') }}"
                    oninput="document.getElementById('budget_amount_words').innerText = numberToWordsIndian(this.value);">
                <small id="budget_amount_words" class="form-text text-muted"></small>
            </div>
            @error('budget_amount')
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
                <input type="text" name="duration" id="duration" class="form-control"
                    placeholder="Enter Duration" value="{{ old('duration', $site->duration ?? '') }}">
            </div>
            @error('duration')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Flat Area & Built-Up Area -->
    <div class="row align-items-center">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="flat_area">Flat Area</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <input type="text" name="flat_area" id="flat_area" class="form-control"
                    placeholder="Enter Flat Area" value="{{ old('flat_area', $site->flat_area ?? '') }}">
            </div>
            @error('flat_area')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-2">
            <div class="form-group">
                <label for="built_up_area">Built-Up Area</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <input type="text" name="built_up_area" id="built_up_area" class="form-control"
                    placeholder="Enter Built-Up Area" value="{{ old('built_up_area', $site->built_up_area ?? '') }}">
            </div>
            @error('built_up_area')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Supervisor -->
    <div class="row align-items-center">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="supervisor_id">Supervisor</label>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <select name="supervisor_id" id="supervisor_id" class="form-select form-control">
                    <option value="">Select Supervisor</option>
                    @foreach ($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" {{ $site->supervisor_id == $supervisor->id ? 'selected' : '' }}>
                            {{ $supervisor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('supervisor_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Status -->
    <div class="card-header mt-4">
        <div class="d-flex align-items-center justify-content-between w-100">
            <h4 class="card-title">Update Status</h4>
        </div>
    </div>
    <div class="row align-items-center mt-3 border-bottom pb-3">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="status">Change Status</label>
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-select form-control" name="status" id="status">
    <option value="">Select Status</option>
    <option value="Ongoing" {{ in_array($site->status, ['Ongoing', 'quoted', 'Quoted', 'New', null], true) ? 'selected' : '' }}>Ongoing</option>
    <option value="Completed" {{ $site->status == 'Completed' ? 'selected' : '' }}>Completed</option>
</select>
        </div>
    </div>

    <!-- Buttons -->
    <div class="card-action text-end"> <button class="btn btn-success">Submit</button> <button class="btn btn-danger">Cancel</button> </div>
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
            const form = document.getElementById('siteForm');
            const spinner = document.getElementById('loadingSpinner');

            //Success alert handling
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    window.location.href = "{{ route('sitemanagement.list') }}";
                }, 500);
            }

            //Show spinner only on site form submission
            if (form && spinner) {
                form.addEventListener('submit', function(event) {
                    spinner.classList.remove('d-none'); //Show spinner
                });
            }

            //Show amount in words for the pre-filled budget amount
            const budgetAmountInput = document.getElementById('budget_amount');
            if (budgetAmountInput && budgetAmountInput.value) {
                document.getElementById('budget_amount_words').innerText = numberToWordsIndian(budgetAmountInput.value);
            }
        });
    </script>
@endsection
