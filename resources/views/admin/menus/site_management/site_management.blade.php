@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header leads-page-header">
                <h3 class="fw-bold mb-3">Site Management</h3>                            <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Site Management</a></li>
                </ul>
            </div>
            <div class="row">
                <div class="col-12 col-md-8">

                    <!-- Blade alert for success and error -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('error') }}
                    @endif

                    <form id="filterform" action="" method="GET" action="{{ route('sitemanagement.list') }}"
                        class="row mb-3">
                     
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <select name="status" id="statusFilter" class="form-select"
                                    onchange="document.getElementById('filterform').submit();">
                                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All</option>
                                    <option value="Ongoing" {{ request('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing
                                    </option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                            </div>

                            <div class="col-md-8 d-flex justify-content-end">
                                @if(isset($activeSiteCount) && isset($siteLimit) && $activeSiteCount >= $siteLimit)
                                    <button type="button" class="btn btn-secondary btn-round" id="addSiteBtn" onclick="showSiteLimitPopup()">
                                        <i class="fa fa-plus"></i> Add Site
                                    </button>
                                @else
                                    <a href="{{ route('site.form') }}" class="btn btn-primary btn-round">
                                        <i class="fa fa-plus"></i> Add Site
                                    </a>
                                @endif
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            @if ($sites->isEmpty())
                <p class="text-center mt-3"> No Site list found. Please create site.</p>
            @else
                <div class="row">
                    <div class="col-12 col-md-8">
                        @foreach ($sites as $site)
                            <div class="card mt-3 site-card"
                                @if ($sharedMenuVisibility['site_detail'] ?? true)
                                    data-route="{{ route('site.detail', $site->id) }}"
                                    onclick="redirectToLeadDetails(event, this)"
                                @endif>
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-6 d-flex align-items-center">
                                            <h6 class="card-title mb-0">Site ID: {{ $site->id }}</h6>
                                            <span class="op-7 ms-3 fw-normal">
                                                {{ \Carbon\Carbon::parse($site->created_at)->format('M, d Y h:i A') }}
                                            </span>
                                        </div>
                                        <div class="col-6 text-end">
                                            @php
    // Get status from database
    $status = $site->status ?? 'Ongoing';

    // Old quoted/new projects are shown as ongoing.
    if (in_array($status, ['New', 'quoted', 'Quoted', null], true)) {
        $status = 'Ongoing';
    }

    // Set badge class based on status
    $badgeClass = match ($status) {
        'Ongoing' => 'badge-warning',
        'Completed' => 'badge-success',
        default => 'badge-secondary',
    };
@endphp

<span class="badge {{ $badgeClass }}">
    {{ ucfirst($status) }}
</span>

                                            <div class="form-button-action">
                                                <a href="{{ route('sitemanagement.edit', ['id' => $site->id]) }}"
                                                    class="btn btn-link btn-primary btn-lg">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-link btn-danger deleteButton"
                                                    data-id="{{ $site->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{-- style="width: 50%; margin: 0 auto;" --}}
                                    <div class="row justify-content-center">
                                        <img src="{{ $site->site_image_url }}" alt="Site Image"
                                            class="mx-auto d-block" style="width: 30%; max-height:200px; object-fit:contain;">
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <p><strong>Site Name:</strong>
                                                <span class="text-muted">{{ $site->site_name }}</span>
                                            </p>
                                            <p><strong>Duration</strong>
                                                <span class="text-muted">{{ $site->duration }}</span>
                                            </p>
                                            <p><strong>Budget Amount:</strong>
                                                <span class="text-muted">
                                                    {{ $site->budget_amount !== null ? 'Rs. ' . number_format((float) $site->budget_amount, 2) : '-' }}
                                                </span>
                                            </p>
                                            {{-- <p><strong>Expenses:</strong>
                                                <span class="text-muted">
                                                    Rs. {{ number_format((float) ($site->expense_amount ?? 0), 2) }}
                                                </span>
                                            </p>
                                            <p><strong>Balance Amount:</strong>
                                                <span class="text-muted">
                                                    Rs. {{ number_format((float) ($site->balance_amount ?? 0), 2) }}
                                                </span>
                                            </p> --}}
                                            
                                             <p><strong>Bulid Up Area:</strong>
                                                <span class="text-muted">{{ $site->built_up_area }}</span>
                                            </p>
                                            <!-- <p><strong>Settled Amount:</strong>
                                                <span class="text-muted">{{ $site->settled_amnt }}</span>
                                            </p>-->
                                           <!-- <p><strong>Settled Amount:</strong>
                                                <span class="text-muted">{{ $site->settled_amnt }}</span>
                                            </p>--> 
                                            <!--<p><strong>Expense:</strong>
                                                <span class="text-muted">{{ $site->expense ?? 0 }}</span>
                                            </p>-->
                                        </div>
                                        <div class="col-6">
                                        <p><strong>Location:</strong>
                                        @php
                                            $location = $site->location;
                                            $isMapLink = Str::startsWith($location, ['http://', 'https://']) && Str::contains($location, 'maps');
                                        @endphp

                                        @if($isMapLink)
                                            <a href="{{ $location }}" target="_blank" 
                                            class="btn btn-sm btn-outline-primary ms-2"
                                            style="padding: 2px 10px; font-size: 13px; border-radius: 8px;">
                                                <i class="bi bi-geo-alt-fill"></i> View on Map
                                            </a>
                                        @else
                                            <span class="text-muted">{{ $location }}</span>
                                        @endif
                                    </p>

                                    <p><strong>Flat Area:</strong>
                                    <span class="text-muted">{{ $site->flat_area }}</span>
                                    </p>
                                     <p><strong>Expenses:</strong>
                                                <span class="text-muted">
                                                    Rs. {{ number_format((float) ($site->expense_amount ?? 0), 2) }}
                                                </span>
                                            </p>
                                            <p><strong>Balance Amount:</strong>
                                                <span class="text-muted">
                                                    Rs. {{ number_format((float) ($site->balance_amount ?? 0), 2) }}
                                                </span>
                                            </p>
                                            
                                        <!-- <p><strong>Value:</strong>
                                                <span class="text-muted">{{ $site->value }}</span>
                                            </p>-->
                                           <!-- <p><strong>Pending Amount:</strong>
                                                <span class="text-muted">{{ $site->pending_amnt }}</span>
                                            </p>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal (Outside Loop) -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End of Delete Confirmation Modal -->

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

        document.addEventListener("DOMContentLoaded", function() {

            //delete site
            document.querySelectorAll(".deleteButton").forEach(button => {
                button.addEventListener("click", function() {
                    event.stopPropagation();
                    const siteId = this.getAttribute("data-id");
                    const action = "{{ route('sitemanagement.delete', ':id') }}".replace(':id',
                        siteId);
                    document.getElementById("deleteForm").setAttribute("action", action);
                });
                //Auto-hide success alert after 3 seconds 
                const successAlert = document.querySelector(".alert-success");
                const form = document.getElementById('deleteForm');
                const spinner = document.getElementById('loadingSpinner');

                if (successAlert) {
                    setTimeout(() => {
                        successAlert.classList.remove("show");
                        successAlert.classList.add("fade");
                    }, 500);
                }
                //Show spinner only on site form submission
                if (form && spinner) {
                    form.addEventListener('submit', function(event) {
                        spinner.classList.remove('d-none'); //Show spinner
                    });
                }
            });

            window.showSiteLimitPopup = function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        text: 'You have reached the limit to add more site.',
                        showCancelButton: true,
                        confirmButtonText: 'View Pricing',
                        cancelButtonText: 'Close',
                        customClass: {
                            popup: 'site-limit-popup',
                            htmlContainer: 'site-limit-popup-text',
                            actions: 'site-limit-popup-actions'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('admin.pricing') }}";
                        }
                    });
                } else if (typeof swal !== 'undefined') {
                    swal({
                        text: 'You have reached the limit to add more site.',
                        icon: 'warning',
                        buttons: {
                            cancel: {
                                text: 'Close',
                                visible: true
                            },
                            confirm: {
                                text: 'View Pricing',
                            }
                        }
                    }).then((willContact) => {
                        if (willContact) {
                            window.location.href = "{{ route('admin.pricing') }}";
                        }
                    });
                } else {
                    if (confirm('You have reached the limit to add more site. Click OK to contact support.')) {
                        window.location.href = "{{ route('admin.pricing') }}";
                    }
                }
            };
        });
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

        .site-limit-popup {
            text-align: center;
        }

        .site-limit-popup-text {
            text-align: center !important;
        }

        .site-limit-popup-actions {
            justify-content: center !important;
        }
    </style>
@endsection
