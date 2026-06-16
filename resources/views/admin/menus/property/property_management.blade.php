@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header leads-page-header">
                <h3 class="fw-bold mb-3">Property</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Property</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Property List</a></li>
                </ul>
            </div>
            <div class="row">
                <div class="col-12 col-md-8">

                    <!-- Blade alert for success -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif

                    <form id="filterform" action="" method="POST" class="row mb-3">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="{{ route('agent.list') }}" class="btn btn-primary btn-round">
                                    <i class="fa fa-users"></i> View Agents
                                </a>
                                <a href="{{ route('property.form') }}" class="btn btn-primary btn-round">
                                    <i class="fa fa-plus"></i> Add Property
                                </a>
                            </div>
                        </div>                        
                    </form>
                </div>
            </div>
            @if ($properties->isEmpty())
                <p class="text-center mt-3"> No Property list found. Please add property.</p>
            @else
                <div class="row">
                    <div class="col-12 col-md-8">
                        @foreach ($properties as $property)
                            <div class="card mt-3 site-card" data-route="" onclick="redirectToLeadDetails(event, this)">
                                <div class="card-body">
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/' . $property->image) }}" alt="Site Image"
                                            class="mx-auto d-block" style="width: 30%;">
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <p><strong>Property Name:</strong>
                                                <span class="text-muted">{{ $property->name }}</span>
                                            </p>
                                            <p><strong>Property Location</strong>
                                                <span class="text-muted">{{ $property->location }}</span>
                                            </p>
                                            <p><strong>Property Amount:</strong>
                                                <span class="text-muted">{{ $property->amount }}</span>
                                            </p>
                                        </div>
                                        {{-- <div class="col-6">
                                            <p><strong>Location:</strong>
                                                <span class="text-muted">{{ $site->location }}</span>
                                            </p>
                                            <p><strong>Value:</strong>
                                                <span class="text-muted">{{ $site->value }}</span>
                                            </p>
                                            <p><strong>Pending Amount:</strong>
                                                <span class="text-muted">{{ $site->pending_amnt }}</span>
                                            </p>
                                        </div> --}}
                                    </div>
                                    {{-- <div class="row">
                                    <div class="col text-end">
                                        <a href="{{ route('leads-tasks', $lead->id) }}"
                                            class="link-primary text-decoration-underline">
                                            View Task
                                        </a>
                                    </div>
                                </div> --}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
        function redirectToLeadDetails(event, card) {
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
