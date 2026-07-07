@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-10">
                <h3 class="text-center pb-4 mt-3">Update Profile</h3>
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
                        {{ session()->forget('success') }}
                    @endif

                    <form id="profileForm" action="{{ route('profile.update') }}" method="POST"
                        enctype="multipart/form-data" class="row g-3">
                        @csrf

                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        <!-- Left side: inputs -->
                        <div class="col-md-8">
                            <div class="mb-3 row align-items-center">
                                <label for="name" class="col-sm-4 col-form-label fw-bold">Name</label>
                                <div class="col-sm-8">
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name', $user->name) }}" placeholder="Enter name">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row align-items-center">
                                <label for="mobile_no" class="col-sm-4 col-form-label fw-bold">Mobile Number</label>
                                <div class="col-sm-8">
                                    <input type="text" id="mobile_no" name="mobile_no" class="form-control"
                                        value="{{ old('mobile_no', $user->mobile_no) }}" maxlength="10" minlength="10"
                                        pattern="\d{10}" placeholder="Enter mobile number"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" readonly>
                                    @error('mobile_no')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row align-items-center">
                                <label for="email" class="col-sm-4 col-form-label fw-bold">Email</label>
                                <div class="col-sm-8">
                                    <input type="email" id="email" name="email" class="form-control"
                                        value="{{ old('email', $user->email) }}" placeholder="Enter email" readonly>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row align-items-center">
                                <label for="password" class="col-sm-4 col-form-label fw-bold">Password</label>
                                <div class="col-sm-8">
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Enter new password if you want to change">
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row align-items-center">
                                <label for="password_confirmation" class="col-sm-4 col-form-label fw-bold">Confirm
                                    Password</label>
                                <div class="col-sm-8">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control" placeholder="Re-enter password">
                                    @error('password_confirmation')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row justify-content-center mt-4">
                                <div class="col-lg-4">
                                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                                </div>
                            </div>
                        </div>

                        <!-- Right side: profile image -->
                        <div class="col-md-4 text-center">
                            <label class="fw-bold mb-2 d-block">Profile Image</label>
                            <img src="{{ $user->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->image) ? asset('storage/' . $user->image) : asset('images/logo/logo.jpeg') }}"
                                alt="Profile Image" id="imagePreview" class="img-thumbnail mb-3"
                                style="max-width: 200px; height: auto;">

                            <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp"
                                class="form-control" onchange="previewImage(event)">
                            @error('image')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
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

    <script>
        // Live preview of uploaded image
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('imagePreview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener("DOMContentLoaded", function() {
            let alert = document.querySelector('.alert');
            const form = document.getElementById('profileForm');
            const spinner = document.getElementById('loadingSpinner');

            //Success alert handling
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                }, 500);
            }

            //Show spinner only on job form submission
            if (form && spinner) {
                form.addEventListener('submit', function(event) {
                    spinner.classList.remove('d-none'); //Show spinner
                });
            }
        });
    </script>
@endsection
