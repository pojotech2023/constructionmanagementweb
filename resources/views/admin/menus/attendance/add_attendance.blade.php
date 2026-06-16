@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Add Attendance</h3>
            </div>
            
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-lg p-4 ms-4">

                    <!-- Success Alert -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                    <form id="requestForm" action="{{ route('add.attendance') }}" method="POST" class="container">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <!-- Date Picker -->
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <div class="form-group">
                                    <label for="todayDate" class="fw-bold">Date</label>
                                    <input type="date" id="todayDate"
                                           class="form-control w-auto d-inline-block"
                                           name="date"
                                           value="{{ old('date') }}"
                                           required>
                                </div>
                                @error('date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Labels -->
                        <div class="row align-items-center mt-4">
                            <div class="col-md-4">
                                <label class="fw-bold">Category</label>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Count</label>
                            </div>
                        </div>

                        <!-- Category & Count Inputs -->
                        @foreach (['Mason', 'Helper', 'Fitter', 'Centring Helper'] as $category)
                            <div class="row align-items-center mt-3">
                                <div class="col-lg-4">
                                    <input type="text" class="form-control"
                                           value="{{ ucfirst($category) }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control no-arrow"
                                           min="0" step="1"
                                           name="count_{{ $category }}"
                                           value="{{ old('count_' . $category) }}"
                                           placeholder="Enter Count">
                                    @error('count_' . $category)
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach

                        <!-- Submit -->
                        <div class="col-lg-2 ms-auto mt-4">
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
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
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('requestForm');
            const spinner = document.getElementById('loadingSpinner');
            const alert = document.querySelector('.alert');

            // ✅ Spinner show on submit
            if (form && spinner) {
                form.addEventListener('submit', function() {
                    spinner.classList.remove('d-none');
                });
            }

            // ✅ Success alert fade and redirect
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    const siteId = "{{ $siteId }}";
                    window.location.href = "/admin/public/admin/attendance/" + siteId;
                }, 1000);
            }
        });
    </script>
@endsection
