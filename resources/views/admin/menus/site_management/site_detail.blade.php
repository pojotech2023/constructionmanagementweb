@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Site Details</h3>
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
                        <a href="#">Site Details</a>
                    </li>
                </ul>
            </div>
            <div class="row g-4">
                <!-- Blade alert for success -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    {{ session()->forget('success') }} {{-- Clear session --}}
                @endif
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('attendance', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/attendance.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted mb-3">Today Attendance</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('material.detail', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/material.jpg') }}" class="w-100">
                            </div>
                            <div class="text-muted mb-3">Materials</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('subcontractor.detail', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/subcontractor.jpg') }}" class="w-100">
                            </div>
                            <div class="text-muted">SubContractor</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('site.paymentDetail', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/payment.png') }}" class="w-100">
                            </div>
                            <div class="text-muted">Payment Status</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('checklist', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/checklist.png') }}" class="w-100">
                            </div>
                            <div class="text-muted">checklist</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('ticket', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/tickets.png') }}" class="w-100">
                            </div>
                            <div class="text-muted">Tickets</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card" data-route="{{ route('drawing', $site->id) }}" onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/drawing.jpg') }}" class="w-100">
                            </div>
                            <div class="text-muted">Drawing</div>
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
        //redirect to leads detail page
        function redirectToDetails(event, card) {
            event.stopPropagation(); // Prevents unintended clicks
            // Remove styles from all cards
            document.querySelectorAll('.site-card').forEach(item => {
                item.classList.remove('selected-card');
            });
            // Add active class to clicked card
            card.classList.add('selected-card');
            // Redirect after small delay
            let route = card.getAttribute('data-route');
            if (route) {
                window.location.href = route;
            }
        }
    </script>    
      <style>
        .site-card {
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            border: 2px solid #dee2e6;
            background: #fff;
            border-radius: 8px;
        }

        /* Hover Effect */
        .site-card:hover {
            transform: scale(1.02);
            border-color: #007bff;
            box-shadow: 0px 5px 15px rgba(0, 123, 255, 0.3);
        }
    </style>
@endsection
