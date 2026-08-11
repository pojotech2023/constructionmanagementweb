@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-3">{{ ucfirst($materialType) }} Details</h3>
                    <ul class="breadcrumbs mb-0">
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
                            <a href="{{ route('site.detail', $siteId) }}">{{ $siteName }}</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">{{ ucfirst($materialType) }} Details</a>
                        </li>
                    </ul>
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6 class="card-title mb-0 fw-bold">Site Name: {{ $siteName }}</h6>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-end pb-3"
                                style="border-bottom: 1px solid rgb(235, 236, 236) !important;">
                                <div class="col-12 col-md-3">
                                    <input type="month" id="monthPicker" class="form-control"
                                           value="{{ $month }}">
                                </div>

                                <div class="col-12">
                                    <div class="row justify-content-center align-items-center" id="weekFilterRow">
                                        <div class="col-auto mb-2">
                                            <span class="badge badge-secondary week-reset-btn {{ empty($week) ? 'active' : '' }}">
                                                Full Month
                                            </span>
                                        </div>
                                        @foreach ([1, 2, 3, 4] as $weekNumber)
                                            <div class="col-auto mb-2">
                                                <span class="badge badge-black week-btn {{ (string) $week === (string) $weekNumber ? 'active' : '' }}"
                                                      data-week="{{ $weekNumber }}">
                                                    Week {{ $weekNumber }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                           <div class="row align-items-center mt-3">
    <!-- Title -->
    <div class="col-12 col-md-6 mb-2 mb-md-0">
        <h4 class="card-title mb-0">
            {{ ucfirst($materialType) }} Overview
        </h4>
    </div>

    <!-- Buttons -->
        <div class="col-12 col-md-6">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
            <a href="{{ route('material.requestForm', ['siteId' => $siteId, 'materialType' => $materialType]) }}" class="btn btn-info w-100 w-md-auto">Request</a>

            <a href="{{ route('material.orderForm', ['siteId' => $siteId, 'materialType' => $materialType]) }}" class="btn btn-primary w-100 w-md-auto">Inward Order</a>

            <button type="button" class="btn btn-success w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#materialExportModal">Export</button>
        </div>
    </div>
</div>


                        <!-- Blade alert for success -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            {{ session()->forget('success') }} {{-- Clear session --}}
                        @endif

                        @if ($materials->isEmpty())
                            <p class="text-center mt-3"> No {{ ucfirst($materialType) }} list found this Site.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Date</th>
                                                <th>Quantity</th>
                                                <th>Vendor</th>
                                                <th>Price</th>
                                                <th>Vendor GST</th>
                                                <th>Invoice</th>
                                                <th style="width:10%">Action</th>
                                                {{-- <th>Available</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="bricksTableBody">
                                            @foreach ($materials as $index => $brick)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $brick->date ? \Carbon\Carbon::parse($brick->date)->format('d-m-Y') : '-' }}</td>
                                                    <td>{{ $brick->quantity }}</td>
                                                    <td>{{ $brick->vendor->name }}</td>
                                                    <td>{{ $brick->price }}</td>
                                                    <td>{{ $brick->vendor->gst ?? '-' }}</td>
                                                    <td>
                                                        @if ($brick->image_url)
                                                            @if (str_ends_with(strtolower($brick->image_url), '.pdf'))
                                                                <a href="{{ $brick->image_url }}" target="_blank" title="View invoice">
                                                                    <i class="fa fa-file-pdf"></i> View
                                                                </a>
                                                            @else
                                                                <a href="{{ $brick->image_url }}" target="_blank" title="View invoice">
                                                                    <img src="{{ $brick->image_url }}" class="material-photo-thumb" alt="Invoice">
                                                                </a>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button" class="btn btn-link btn-primary btn-sm editOrderBtn"
                                                                data-id="{{ $brick->id }}"
                                                                data-date="{{ $brick->date }}"
                                                                data-quantity="{{ $brick->quantity }}"
                                                                data-price="{{ $brick->price }}"
                                                                data-gst="{{ $brick->vendor->gst }}"
                                                                data-image="{{ $brick->image_url }}"
                                                                data-bs-toggle="modal" data-bs-target="#editOrderModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-link btn-danger btn-sm deleteOrderBtn"
                                                                data-id="{{ $brick->id }}" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                            <a href="{{ route('material.order.pdf', $brick->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                                                                <i class="fa fa-file-pdf"></i> PDF
                                                            </a>
                                                        </div>
                                                    </td>
                                                    {{-- <td>{{ $brick->available_unit_count }}</td> --}}
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- Export Modal -->
                    <div class="modal fade" id="materialExportModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="GET" action="{{ route('material.export', ['siteId' => $siteId, 'materialType' => $materialType]) }}" class="js-export-modal-form">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Export {{ ucfirst($materialType) }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">From Date</label>
                                            <input type="date" name="from_date" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">To Date</label>
                                            <input type="date" name="to_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Download</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Order Modal -->
                    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="editOrderForm" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Order</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date" id="edit_order_date" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" step="1" name="quantity" id="edit_order_quantity" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Price</label>
                                            <input type="number" step="0.01" name="price" id="edit_order_price" class="form-control" required
                                                oninput="document.getElementById('edit_order_price_words').innerText = numberToWordsIndian(this.value);">
                                            <small id="edit_order_price_words" class="form-text text-muted"></small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Vendor GST</label>
                                            <input type="text" id="edit_order_gst" class="form-control" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Invoice</label>
                                            <div id="edit_order_current_image" class="mb-2"></div>
                                            <input type="file" name="attachment" id="edit_order_attachment" class="form-control" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Order Modal -->
                    <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="deleteOrderForm" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Delete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this order?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3" style="width: 50%">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h6 class="fw-bold text-info">TOTAL</h6>
                                        </td>
                                        <td>
                                            <h6 class="fw-bold text-info" id="totalUnits">{{ $totalUnits }} Units</h6>
                                        </td>
                                        <td>
                                            <h6 class="fw-bold text-info" id="totalAmount">{{ $totalAmount }}</h6>
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <td>
                                            <p class="text-success fw-bold">Settled Amount</p>
                                        </td>
                                        <td></td>
                                        <td>
                                            <p class="text-success fw-bold" id="settledAmount">{{ $settledAmount }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="text-danger fw-bold">Pending Amount</p>
                                        </td>
                                        <td></td>
                                        <td>
                                            <p class="text-danger fw-bold" id="pendingAmount">{{ $pendingAmount }}</p>
                                        </td>
                                    </tr> --}}
                                </tbody>
                            </table>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.js-export-modal-form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var modalEl = form.closest('.modal');
                    var modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                    if (modal) {
                        modal.hide();
                    }
                });
            });

            const monthPicker = document.getElementById('monthPicker');
            const weekButtons = document.querySelectorAll('.week-btn');
            const weekResetButton = document.querySelector('.week-reset-btn');
            const materialBaseUrl = @json(route('material', ['siteId' => $siteId, 'materialType' => $materialType]));

            let currentSelectedMonth = monthPicker.value;

            // Month change => reload page with selected month
            monthPicker.addEventListener('change', function() {
                currentSelectedMonth = this.value;
                if (currentSelectedMonth) {
                    window.location.href = `${materialBaseUrl}?month=${currentSelectedMonth}`;
                }
            });

            // Week button click => reload page with month + week
            weekButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const wasActive = this.classList.contains('active');
                    const selectedWeek = this.getAttribute('data-week');

                    if (wasActive) {
                        window.location.href = `${materialBaseUrl}?month=${currentSelectedMonth}`;
                        return;
                    }

                    if (currentSelectedMonth && selectedWeek) {
                        window.location.href =
                            `${materialBaseUrl}?month=${currentSelectedMonth}&week=${selectedWeek}`;
                    }
                });
            });

            if (weekResetButton) {
                weekResetButton.addEventListener('click', function() {
                    if (currentSelectedMonth) {
                        window.location.href = `${materialBaseUrl}?month=${currentSelectedMonth}`;
                    }
                });
            }

            function toIsoDate(dateValue) {
                if (!dateValue) return '';
                const parts = dateValue.split('-');
                if (parts.length === 3 && parts[0].length === 2) {
                    return `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
                return dateValue;
            }

            // Event delegation for edit/delete buttons
            document.addEventListener('click', function (e) {
                const editBtn = e.target.closest('.editOrderBtn');
                const delBtn = e.target.closest('.deleteOrderBtn');

                if (editBtn) {
                    const id = editBtn.getAttribute('data-id');
                    const date = editBtn.getAttribute('data-date');
                    const quantity = editBtn.getAttribute('data-quantity');
                    const price = editBtn.getAttribute('data-price');
                    const gst = editBtn.getAttribute('data-gst');
                    const image = editBtn.getAttribute('data-image');
                    const form = document.getElementById('editOrderForm');
                    form.action = '/admin/material-order-update/' + id;
                    document.getElementById('edit_order_date').value = toIsoDate(date);
                    document.getElementById('edit_order_quantity').value = quantity;
                    document.getElementById('edit_order_price').value = price;
                    document.getElementById('edit_order_gst').value = gst || '';
                    document.getElementById('edit_order_attachment').value = '';

                    const currentImageDiv = document.getElementById('edit_order_current_image');
                    if (image) {
                        if (image.toLowerCase().endsWith('.pdf')) {
                            currentImageDiv.innerHTML = '<a href="' + image + '" target="_blank"><i class="fa fa-file-pdf"></i> View current invoice</a>';
                        } else {
                            currentImageDiv.innerHTML = '<a href="' + image + '" target="_blank"><img src="' + image + '" class="material-photo-thumb" alt="Current invoice"></a>';
                        }
                    } else {
                        currentImageDiv.innerHTML = '<span class="text-muted">No image uploaded</span>';
                    }
                }

                if (delBtn) {
                    const id = delBtn.getAttribute('data-id');
                    const form = document.getElementById('deleteOrderForm');
                    form.action = '/admin/material-order-delete/' + id;
                }
            });
        });
    </script>

    <style>
        .week-btn {
            cursor: pointer;
        }

        .week-reset-btn {
            cursor: pointer;
        }

        .week-btn.active {
            background-color: #007bff;
            color: white;
        }

        .week-reset-btn.active {
            background: #198754;
            color: #fff;
        }

        .material-photo-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
@endsection
