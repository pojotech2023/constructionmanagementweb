@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-3">SubContractor Details</h3>
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
                            <a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">SubContractor Details</a>
                        </li>
                    </ul>
                </div>
                
                
            </div>
            <div class="row">
                <!-- Blade alert for success -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    {{ session()->forget('success') }} {{-- Clear session --}}
                @endif

                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100  site-card"
                        data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'plumber']) }}"
                        onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/plumber.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Plumber</div>
                            <div class="text-success fw-bold">Total Amount -
                                {{ $subcontractors['plumber']['totalAmounts'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card"
                        data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'electrician']) }}"
                        onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/electrician.webp') }}" class="w-75">
                            </div>
                            <div class="text-muted">Electrician</div>
                            <div class="text-success fw-bold">Total Amount -
                                {{ $subcontractors['electrician']['totalAmounts'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card"
                        data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'painter']) }}"
                        onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/painting.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted mb-3">Painter</div>
                            <div class="text-success fw-bold">Total Amount -
                                {{ $subcontractors['painter']['totalAmounts'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card"
                        data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'welder']) }}"
                        onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/welding.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Welder</div>
                            <div class="text-success fw-bold">Total Amount -
                                {{ $subcontractors['welder']['totalAmounts'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card"
                        data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'tileslayer']) }}"
                        onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/tileslayer.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted mb-3">Tiles Layer</div>
                            <div class="text-success fw-bold">Total Amount -
                                {{ $subcontractors['tileslayer']['totalAmounts'] ?? 0 }}</div>

                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card"
                        data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'granitelayer']) }}"
                        onclick="redirectToDetails(event, this)">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/granitelayer.jpeg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Granite Layer</div>
                            <div class="text-success fw-bold">Total Amount -
                                {{ $subcontractors['granitelayer']['totalAmounts'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="card h-100 w-100 site-card"
                            data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'sswelder']) }}"
                            onclick="redirectToDetails(event, this)">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('images/sri/welding.jpg') }}" class="w-75">
                                </div>
                                <div class="text-muted">SS Welder</div>
                                <div class="text-success fw-bold">Total Amount -
                                    {{ $subcontractors['sswelder']['totalAmounts'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="card h-100 w-100 site-card"
                            data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'carpenter']) }}"
                            onclick="redirectToDetails(event, this)">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('images/sri/carpenter.jpg') }}" class="w-75">
                                </div>
                                <div class="text-muted">Carpenter</div>
                                <div class="text-success fw-bold">Total Amount -
                                    {{ $subcontractors['carpenter']['totalAmounts'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="card h-100 w-100 site-card"
                            data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'centeringworks']) }}"
                            onclick="redirectToDetails(event, this)">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('images/sri/tiles.jpg') }}" class="w-75">
                                </div>
                                <div class="text-muted">Centering Works</div>
                                <div class="text-success fw-bold">Total Amount -
                                    {{ $subcontractors['centeringworks']['totalAmounts'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="card h-100 w-100 site-card"
                            data-route="{{ route('subcontractor.detailList', ['siteId' => $site->id, 'subcontractorType' => 'masonworks']) }}"
                            onclick="redirectToDetails(event, this)">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('images/sri/masonworks.jpg') }}" class="w-75">
                                </div>
                                <div class="text-muted">Mason Works</div>
                                <div class="text-success fw-bold">Total Amount -
                                    {{ $subcontractors['masonworks']['totalAmounts'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="card h-100 w-100 site-card"
                            data-route="{{ route('subcontractor.pettyCash', ['siteId' => $site->id]) }}"
                            onclick="redirectToDetails(event, this)">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('images/sri/petty_cash.png') }}" class="w-75">
                                </div>
                                <div class="text-muted">Petty Cash</div>
                                <div class="text-success fw-bold">Payment Detail</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="card h-100 w-100 site-card"
                            data-route="{{ route('subcontractor.rentalManagement', ['siteId' => $site->id]) }}"
                            onclick="redirectToDetails(event, this)">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('images/sri/rent icon.png') }}" class="w-75">
                                </div>
                                <div class="text-muted">Rental Management</div>
                                <div class="text-success fw-bold">Payment Detail</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-4" id="addButton" data-bs-toggle="modal"
                    data-bs-target="#addModal" data-site-id="{{ $site->id }}" style="cursor: pointer;">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="card border border-primary shadow" style="min-height: 140px;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6 text-center">
                                        <img src="{{ asset('images/sri/othershand.webp') }}"
                                            style="width: 200px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="col-6 text-start">
                                        <h3 class="fw-bold mb-0">OTHERS</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            <span class="fw-mediumbold" id="modalTitle">Others</span>
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('site.subutilities', $site->id) }}" class="btn btn-info btn-sm">Others History</a>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                    </div>

                    <div class="modal-body">
                        <form id="utilityForm" action="{{ route('subutilities.add') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="site_id" name="site_id">

                            <div class="row align-items-center">
                                <div class="col-lg-2">
                                    <label for="amount">Amount</label>
                                </div>
                                <div class="col-lg-10">
                                    <input id="amount" name="amount" type="number" class="form-control no-arrow" min="0" step="0.01"
                                        placeholder="Enter Amount" />
                                </div>
                                @error('amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row align-items-center mt-3">
                                <div class="col-lg-2">
                                    <label for="date">Date</label>
                                </div>
                                <div class="col-lg-10">
                                    <input id="date" name="date" type="date" class="form-control" value="{{ old('date') }}" />
                                </div>
                            </div>

                            <div class="row align-items-center mt-3">
                                <div class="col-lg-2">
                                    <label for="image">Image</label>
                                </div>
                                <div class="col-lg-10">
                                    <input id="image" name="image" type="file" class="form-control"
                                        accept=".jpg,.jpeg,.png,.webp" />
                                </div>
                                @error('image')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row align-items-center mt-3">
                                <div class="col-lg-2">
                                    <label for="remarks">Remarks</label>
                                </div>
                                <div class="col-lg-10">
                                    <textarea id="remarks" name="remarks" class="form-control" rows="4"></textarea>
                                </div>
                                @error('remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="modal-footer border-0">
                                <button type="submit" class="btn btn-primary" id="saveButton">Add</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
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
            document.addEventListener("DOMContentLoaded", function() {
                const addButton = document.getElementById('addButton');
                const siteIdInput = document.getElementById('site_id');
                const utilityForm = document.getElementById('utilityForm');
                const spinner = document.getElementById('loadingSpinner');
                const saveButton = document.getElementById('saveButton');
                const closeButton = document.querySelector('#addModal .btn-danger');

                // Set site_id when modal is opened
                if (addButton && siteIdInput) {
                    addButton.addEventListener('click', function() {
                        const siteId = this.getAttribute('data-site-id');
                        siteIdInput.value = siteId;
                    });
                }

                // Show spinner + disable buttons on form submit
                if (utilityForm) {
                    utilityForm.addEventListener('submit', function(e) {
                        // Disable buttons
                        saveButton.disabled = true;
                        closeButton.disabled = true;

                        // Show spinner
                        if (spinner) {
                            spinner.classList.remove('d-none');
                        }
                    });
                }

                // Auto-hide success alert after 3 seconds
                const successAlert = document.querySelector(".alert-success");
                if (successAlert) {
                    setTimeout(() => {
                        successAlert.classList.add("fade");
                        successAlert.classList.remove("show");
                    }, 500);
                }
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
        </style>
    @endsection
