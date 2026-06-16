@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3>Edit for Date: {{ $date }}</h3>
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

                   <h4>Update Attendance</h4>
<form action="{{ route('update.attendance') }}" method="POST">
    @csrf
    <input type="hidden" name="site_id" value="{{ $siteId }}">
    <input type="hidden" name="date" value="{{ $date }}">

    @foreach ($categories as $cat)
        <div class="row mt-2">
            <div class="col-md-4">
                <label>{{ $cat }}</label>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control"
                       name="count_{{ str_replace(' ', '_', $cat) }}"
                       value="{{ $attendance[$cat] ?? 0 }}">
            </div>
        </div>
    @endforeach

    <button class="btn btn-primary mt-3">Update Attendance</button>
</form>

<hr>

<h4>Update Wages</h4>
<form action="{{ route('update.wages') }}" method="POST">
    @csrf
    
    <input type="hidden" name="site_id" value="{{ $siteId }}">
    <input type="hidden" name="date" value="{{ $date }}">

    @foreach ($categories as $cat)
        <div class="row mt-2">
            <div class="col-md-4">
                <label>{{ $cat }}</label>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control"
                       name="amount_{{ str_replace(' ', '_', $cat) }}"
                       value="{{ $wages[$cat] ?? 0 }}">
            </div>
        </div>
    @endforeach

    <button class="btn btn-success mt-3">Update Wages</button>
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

            // ✅ Spinner on submit
            if (form && spinner) {
                form.addEventListener('submit', function() {
                    spinner.classList.remove('d-none');
                });
            }

            // ✅ Success message fade & redirect
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    const siteId = "{{ $siteId }}";
                    // window.location.href = "/admin/attendance/" + siteId;
                }, 1000);
            }
        });
    </script>
@endsection
