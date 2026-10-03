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
            @if(strtolower($materialType) === 'all')
                <a href="{{ route('material.allForm', ['siteId' => $siteId]) }}" class="btn btn-primary w-100 w-md-auto">Inward Order</a>
            @else
                <a href="{{ route('material.requestForm', ['siteId' => $siteId, 'materialType' => $materialType]) }}" class="btn btn-info w-100 w-md-auto">Request</a>
                <a href="{{ route('material.orderForm', ['siteId' => $siteId, 'materialType' => $materialType]) }}" class="btn btn-primary w-100 w-md-auto">Inward Order</a>
            @endif

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
                                    <table id="add-row" class="display table table-striped table-hover align-middle custom-material-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 45px;">S.No</th>
                                                <th class="text-center" style="width: 100px; white-space: nowrap;">Date</th>
                                                <th class="text-center" style="width: 120px; white-space: nowrap;">Material Type</th>
                                                <th style="min-width: 150px;">Category</th>
                                                <th style="min-width: 130px;">Specification</th>
                                                <th class="text-center" style="width: 90px; white-space: nowrap;">Quantity</th>
                                                <th class="text-end" style="width: 105px; white-space: nowrap;">Price / Item</th>
                                                <th class="text-end" style="width: 110px; white-space: nowrap;">Price</th>
                                                <th class="text-end" style="width: 95px; white-space: nowrap;">GST</th>
                                                <th class="text-end" style="width: 115px; white-space: nowrap;">Total Amount</th>
                                                <th style="min-width: 160px;">Vendor</th>
                                                <th class="text-center" style="width: 140px; white-space: nowrap;">Vendor GSTIN</th>
                                                <th class="text-center" style="width: 70px;">Invoice</th>
                                                <th class="text-center" style="width: 110px; white-space: nowrap;">Action</th>
                                                {{-- <th>Available</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="bricksTableBody">
                                            @php
                                                // Group items by order_no (or individual item id) and determine the last item of each order
                                                $lastItemIds = [];
                                                foreach ($materials as $m) {
                                                    $groupKey = !empty($m->order_no) ? $m->order_no : ('single_' . $m->id);
                                                    $lastItemIds[$groupKey] = $m->id;
                                                }
                                                $isAllOverview = strtolower($materialType) === 'all';
                                            @endphp
                                            @foreach ($materials as $index => $brick)
                                                @php
                                                    $groupKey = !empty($brick->order_no) ? $brick->order_no : ('single_' . $brick->id);
                                                    $totalInOrder = !empty($brick->order_no) ? ($orderItemCounts[$brick->order_no] ?? 1) : 1;
                                                    $isIndividual = ($totalInOrder === 1);
                                                    $isLastInOrder = isset($lastItemIds[$groupKey]) && $lastItemIds[$groupKey] == $brick->id;

                                                    // Check if this order was placed from All Materials form
                                                    $isFromAllMaterials = \Illuminate\Support\Str::startsWith($brick->order_no ?? '', 'ALL-') || \Illuminate\Support\Str::startsWith($brick->order_group ?? '', 'ALL-');

                                                    // If created in All Materials:
                                                    // - In individual material view, hide actions ($showAction = false)
                                                    // - In All Materials overview, show actions on order action row
                                                    // If created in individual material view:
                                                    // - In both individual material view AND All Materials overview, show actions
                                                    if (!$isAllOverview && $isFromAllMaterials) {
                                                        $showAction = false;
                                                    } else {
                                                        $showAction = $isIndividual || $isLastInOrder;
                                                    }

                                                    $itemQty = (float) $brick->quantity;
                                                    $itemPrice = (float) $brick->price;
                                                    $itemRate = ($itemQty > 0 && $itemPrice > 0) ? ($itemPrice / $itemQty) : 0;
                                                    $itemGst = !is_null($brick->gst) ? (float) $brick->gst : 0;
                                                    $itemTotal = !is_null($brick->total_amount) && (float) $brick->total_amount > 0 
                                                        ? (float) $brick->total_amount 
                                                        : ($itemPrice + $itemGst);

                                                    // Prepare complete order items for edit modal
                                                    $orderItems = (!empty($brick->order_no) && isset($allOrderItems[$brick->order_no]))
                                                        ? $allOrderItems[$brick->order_no]
                                                        : collect([$brick]);

                                                    $itemsJsonData = $orderItems->map(function($it) {
                                                        $qty = (float) $it->quantity;
                                                        $price = (float) $it->price;
                                                        $rate = ($qty > 0 && $price > 0) ? round($price / $qty, 2) : 0;
                                                        $gstAmt = !is_null($it->gst) ? (float) $it->gst : 0;
                                                        $tot = !is_null($it->total_amount) && (float) $it->total_amount > 0 ? (float) $it->total_amount : ($price + $gstAmt);
                                                        $gstPct = ($price > 0 && $gstAmt > 0) ? round(($gstAmt / $price) * 100) : 0;

                                                        return [
                                                            'id' => $it->id,
                                                            'material_type' => $it->material_type_display,
                                                            'category' => $it->category_display,
                                                            'spec' => $it->spec ?: '',
                                                            'unit' => $it->unit_display != '-' ? $it->unit_display : ($it->unit ?: ''),
                                                            'quantity' => $qty,
                                                            'rate' => $rate,
                                                            'price' => $price,
                                                            'gst_percent' => $gstPct,
                                                            'gst_amount' => $gstAmt,
                                                            'total_amount' => $tot,
                                                        ];
                                                    })->values();
                                                @endphp
                                                <tr>
                                                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                                                    <td class="text-center" style="white-space: nowrap;">{{ $brick->date ? \Carbon\Carbon::parse($brick->date)->format('d-m-Y') : '-' }}</td>
                                                    <td class="text-center" style="white-space: nowrap;">
                                                        <span class="badge bg-secondary-subtle text-dark fw-bold px-2 py-1" style="font-size: 12px;">
                                                            {{ $brick->material_type_display }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size: 12px; white-space: normal; text-align: left; display: inline-block;">
                                                            {{ $brick->category_display }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted" style="font-size: 13px;">{{ $brick->spec ?: '-' }}</span>
                                                    </td>
                                                    <td class="text-center" style="white-space: nowrap;">
                                                        <span class="fw-semibold">{{ $brick->quantity }}</span> {{ $brick->unit_display != '-' ? $brick->unit_display : '' }}
                                                    </td>
                                                    <td class="text-end" style="white-space: nowrap;">
                                                        @if($itemRate > 0)
                                                            <span class="text-dark">₹&nbsp;{{ number_format($itemRate, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end" style="white-space: nowrap;">
                                                        <span class="fw-semibold text-dark">₹&nbsp;{{ number_format($itemPrice, 2) }}</span>
                                                    </td>
                                                    <td class="text-end" style="white-space: nowrap;">
                                                        @if($itemGst > 0)
                                                            <span class="text-success fw-semibold">₹&nbsp;{{ number_format($itemGst, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end" style="white-space: nowrap;">
                                                        <span class="fw-bold text-dark">₹&nbsp;{{ number_format($itemTotal, 2) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-dark">{{ optional($brick->vendor)->name ?? '-' }}</span>
                                                    </td>
                                                    <td class="text-center" style="white-space: nowrap;">
                                                        <span class="text-muted font-monospace" style="font-size: 12px;">{{ optional($brick->vendor)->gst ?? '-' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($brick->image_url)
                                                            @if (str_ends_with(strtolower($brick->image_url), '.pdf'))
                                                                <a href="{{ $brick->image_url }}" target="_blank" class="btn btn-link btn-danger btn-sm p-0" title="View invoice PDF">
                                                                    <i class="fa fa-file-pdf fa-lg"></i>
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
                                                    <td class="text-center" style="white-space: nowrap;">
                                                        @if ($showAction)
                                                            <div class="form-button-action d-inline-flex align-items-center justify-content-center gap-1">
                                                                <button type="button" class="btn btn-link btn-primary btn-sm p-1 editOrderBtn"
                                                                    data-id="{{ $brick->id }}"
                                                                    data-order-no="{{ $brick->order_no ?? '' }}"
                                                                    data-date="{{ $brick->date }}"
                                                                    data-vendor-name="{{ optional($brick->vendor)->name ?? '-' }}"
                                                                    data-gst="{{ optional($brick->vendor)->gst ?? '-' }}"
                                                                    data-image="{{ $brick->image_url }}"
                                                                    data-items='@json($itemsJsonData)'
                                                                    data-bs-toggle="modal" data-bs-target="#editOrderModal"
                                                                    title="Edit Order">
                                                                    <i class="fa fa-edit fa-lg"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-link btn-danger btn-sm p-1 deleteOrderBtn"
                                                                    data-id="{{ $brick->id }}" data-bs-toggle="modal" data-bs-target="#deleteOrderModal"
                                                                    title="Delete Order">
                                                                    <i class="fa fa-times fa-lg"></i>
                                                                </button>
                                                                <a href="{{ route('material.order.pdf', $brick->id) }}" class="btn btn-link btn-danger btn-sm p-1" title="Download Order PDF" target="_blank">
                                                                    <i class="fa fa-file-pdf fa-lg"></i>
                                                                </a>
                                                            </div>
                                                        @endif
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
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <form id="editOrderForm" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fa fa-edit text-primary me-2"></i>Edit Order
                                            <span id="edit_modal_order_badge" class="badge bg-primary ms-2" style="font-size: 13px;"></span>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3 mb-3 p-3 bg-light rounded border">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold text-muted small mb-1">Order Date <span class="text-danger">*</span></label>
                                                <input type="date" name="date" id="edit_order_date" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold text-muted small mb-1">Vendor Name</label>
                                                <input type="text" id="edit_order_vendor_name" class="form-control form-control-sm" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold text-muted small mb-1">Vendor GSTIN</label>
                                                <input type="text" id="edit_order_gst" class="form-control form-control-sm" readonly>
                                            </div>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-2"><i class="fa fa-boxes me-1 text-secondary"></i> Order Items</h6>
                                        <div class="table-responsive mb-2">
                                            <table class="table table-bordered table-sm align-middle mb-0" id="editOrderItemsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="min-width: 180px;">Material & Item</th>
                                                        <th class="text-center" style="width: 105px;">Quantity</th>
                                                        <th class="text-center" style="width: 80px;">Unit</th>
                                                        <th class="text-end" style="width: 120px;">Price / Item (₹)</th>
                                                        <th class="text-end" style="width: 130px;">Price (₹)</th>
                                                        <th class="text-center" style="width: 100px;">GST</th>
                                                        <th class="text-end" style="width: 140px;">Total incl. GST (₹)</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit_order_items_tbody">
                                                    <!-- Dynamic items populated by JS -->
                                                </tbody>
                                                <tfoot class="table-light fw-bold">
                                                    <tr>
                                                        <td colspan="4" class="text-end">Subtotal:</td>
                                                        <td class="text-end" id="edit_modal_subtotal">₹ 0.00</td>
                                                        <td class="text-center text-muted" id="edit_modal_gst_total">GST: ₹ 0.00</td>
                                                        <td class="text-end text-primary" id="edit_modal_grand_total">₹ 0.00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mb-3 px-1">
                                            <small id="edit_order_price_words" class="form-text text-success fw-bold d-block"></small>
                                        </div>

                                        <div class="p-3 bg-light rounded border">
                                            <label class="form-label fw-bold small text-muted mb-1">Invoice Attachment</label>
                                            <div id="edit_order_current_image" class="mb-2"></div>
                                            <input type="file" name="attachment" id="edit_order_attachment" class="form-control form-control-sm" accept="image/*,.pdf">
                                            <small class="text-muted d-block mt-1">Upload a new file (Image or PDF) to update the invoice for this order.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Save Changes</button>
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
                                            <h6 class="fw-bold text-info" id="totalAmount">₹ {{ number_format((float)$totalAmount, 2) }}</h6>
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

            function escapeHtml(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function numberToWordsIndian(num) {
                if (!num || isNaN(num) || num <= 0) return '';
                const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ',
                    'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
                const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

                function inWords(n) {
                    if (n === 0) return '';
                    let str = '';
                    if (Math.floor(n / 10000000) > 0) {
                        str += inWords(Math.floor(n / 10000000)) + 'Crore ';
                        n %= 10000000;
                    }
                    if (Math.floor(n / 100000) > 0) {
                        str += inWords(Math.floor(n / 100000)) + 'Lakh ';
                        n %= 100000;
                    }
                    if (Math.floor(n / 1000) > 0) {
                        str += inWords(Math.floor(n / 1000)) + 'Thousand ';
                        n %= 1000;
                    }
                    if (Math.floor(n / 100) > 0) {
                        str += inWords(Math.floor(n / 100)) + 'Hundred ';
                        n %= 100;
                    }
                    if (n > 0) {
                        if (n < 20) {
                            str += a[n];
                        } else {
                            str += b[Math.floor(n / 10)] + ' ' + a[n % 10];
                        }
                    }
                    return str;
                }

                const whole = Math.floor(num);
                const paise = Math.round((num - whole) * 100);
                let result = '₹ ' + inWords(whole).trim() + ' Rupees';
                if (paise > 0) {
                    result += ' and ' + inWords(paise).trim() + ' Paise';
                }
                result += ' Only';
                return result;
            }

            function updateEditModalCalculations() {
                let subtotal = 0;
                let totalGst = 0;
                let grandTotal = 0;

                document.querySelectorAll('.edit-item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.edit-item-qty').value) || 0;
                    const rateInput = row.querySelector('.edit-item-rate');
                    const priceInput = row.querySelector('.edit-item-price');
                    const gstPctSelect = row.querySelector('.edit-item-gst-pct');
                    const gstAmtInput = row.querySelector('.edit-item-gst-amt');
                    const totalInput = row.querySelector('.edit-item-total');

                    let rate = parseFloat(rateInput.value) || 0;
                    let price = parseFloat(priceInput.value) || 0;
                    const gstPct = parseFloat(gstPctSelect.value) || 0;

                    // If rate is present and price wasn't manually set, or on live change
                    if (qty > 0 && rate > 0 && (!priceInput.dataset.manual || priceInput.dataset.manual === 'false')) {
                        price = +(qty * rate).toFixed(2);
                        priceInput.value = price;
                    } else if (qty > 0 && price > 0 && rate === 0) {
                        rate = +(price / qty).toFixed(2);
                        rateInput.value = rate;
                    }

                    const gstAmt = +(price * (gstPct / 100)).toFixed(2);
                    const itemTotal = +(price + gstAmt).toFixed(2);

                    gstAmtInput.value = gstAmt;
                    totalInput.value = itemTotal > 0 ? itemTotal : '';

                    subtotal += price;
                    totalGst += gstAmt;
                    grandTotal += itemTotal;
                });

                const subtotalEl = document.getElementById('edit_modal_subtotal');
                if (subtotalEl) {
                    subtotalEl.innerText = '₹ ' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                const gstTotalEl = document.getElementById('edit_modal_gst_total');
                if (gstTotalEl) {
                    gstTotalEl.innerText = 'GST: ₹ ' + totalGst.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                const grandTotalEl = document.getElementById('edit_modal_grand_total');
                if (grandTotalEl) {
                    grandTotalEl.innerText = '₹ ' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                const editWordsEl = document.getElementById('edit_order_price_words');
                if (editWordsEl) {
                    editWordsEl.innerText = grandTotal > 0 ? numberToWordsIndian(grandTotal) : '';
                }
            }

            // Event delegation for edit/delete buttons
            document.addEventListener('click', function (e) {
                const editBtn = e.target.closest('.editOrderBtn');
                const delBtn = e.target.closest('.deleteOrderBtn');

                if (editBtn) {
                    const id = editBtn.getAttribute('data-id');
                    const orderNo = editBtn.getAttribute('data-order-no');
                    const date = editBtn.getAttribute('data-date');
                    const vendorName = editBtn.getAttribute('data-vendor-name');
                    const gst = editBtn.getAttribute('data-gst');
                    const image = editBtn.getAttribute('data-image');
                    let items = [];
                    try {
                        items = JSON.parse(editBtn.getAttribute('data-items') || '[]');
                    } catch (err) {
                        console.error('Error parsing data-items:', err);
                    }

                    const form = document.getElementById('editOrderForm');
                    form.action = '/admin/material-order-update/' + id;

                    const badgeEl = document.getElementById('edit_modal_order_badge');
                    if (badgeEl) {
                        badgeEl.textContent = orderNo || ('PO #' + id);
                    }

                    document.getElementById('edit_order_date').value = toIsoDate(date);
                    document.getElementById('edit_order_vendor_name').value = vendorName || '-';
                    document.getElementById('edit_order_gst').value = (gst && gst !== '-') ? gst : '-';
                    document.getElementById('edit_order_attachment').value = '';

                    const currentImageDiv = document.getElementById('edit_order_current_image');
                    if (image) {
                        if (image.toLowerCase().endsWith('.pdf')) {
                            currentImageDiv.innerHTML = '<a href="' + image + '" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i> View current invoice PDF</a>';
                        } else {
                            currentImageDiv.innerHTML = '<a href="' + image + '" target="_blank"><img src="' + image + '" class="material-photo-thumb" alt="Current invoice"> <span class="small text-muted ms-2">Click to view full image</span></a>';
                        }
                    } else {
                        currentImageDiv.innerHTML = '<span class="text-muted small">No invoice file uploaded</span>';
                    }

                    const tbody = document.getElementById('edit_order_items_tbody');
                    tbody.innerHTML = '';

                    if (items.length > 0) {
                        items.forEach(function(item) {
                            const itemId = item.id;
                            const specText = item.spec ? `<br><small class="text-muted">${escapeHtml(item.spec)}</small>` : '';
                            const itemLabel = `<div><strong>${escapeHtml(item.category || item.material_type)}</strong>${specText}<br><span class="badge bg-secondary-subtle text-dark mt-1" style="font-size: 11px;">${escapeHtml(item.material_type)}</span></div>`;

                            const gstOptions = [0, 5, 12, 18, 28].map(rate => {
                                const isSel = Math.round(item.gst_percent) === rate ? 'selected' : '';
                                return `<option value="${rate}" ${isSel}>${rate}%</option>`;
                            }).join('');

                            const tr = document.createElement('tr');
                            tr.className = 'edit-item-row';
                            tr.dataset.id = itemId;
                            tr.innerHTML = `
                                <td>
                                    ${itemLabel}
                                    <input type="hidden" name="items[${itemId}][id]" value="${itemId}">
                                </td>
                                <td>
                                    <input type="number" step="any" min="0" class="form-control form-control-sm text-center edit-item-qty" name="items[${itemId}][quantity]" value="${item.quantity}" required>
                                </td>
                                <td class="text-center text-muted small fw-semibold">
                                    ${escapeHtml(item.unit || '-')}
                                    <input type="hidden" name="items[${itemId}][unit]" value="${escapeHtml(item.unit || '')}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end edit-item-rate" name="items[${itemId}][rate]" value="${item.rate > 0 ? item.rate : ''}" placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end edit-item-price" name="items[${itemId}][price]" value="${item.price > 0 ? item.price : ''}" placeholder="0.00" required>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm edit-item-gst-pct" name="items[${itemId}][gst_percent]">
                                        ${gstOptions}
                                    </select>
                                    <input type="hidden" class="edit-item-gst-amt" name="items[${itemId}][gst_amount]" value="${item.gst_amount || 0}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end edit-item-total" name="items[${itemId}][total_amount]" value="${item.total_amount > 0 ? item.total_amount : ''}" placeholder="0.00" readonly>
                                </td>
                            `;
                            tbody.appendChild(tr);

                            const qtyInput = tr.querySelector('.edit-item-qty');
                            const rateInput = tr.querySelector('.edit-item-rate');
                            const priceInput = tr.querySelector('.edit-item-price');
                            const gstSelect = tr.querySelector('.edit-item-gst-pct');

                            qtyInput.addEventListener('input', function() {
                                priceInput.dataset.manual = 'false';
                                const q = parseFloat(this.value) || 0;
                                const r = parseFloat(rateInput.value) || 0;
                                const p = parseFloat(priceInput.value) || 0;
                                if (q > 0 && r > 0) {
                                    priceInput.value = +(q * r).toFixed(2);
                                } else if (q > 0 && p > 0 && r === 0) {
                                    rateInput.value = +(p / q).toFixed(2);
                                }
                                updateEditModalCalculations();
                            });

                            rateInput.addEventListener('input', function() {
                                priceInput.dataset.manual = 'false';
                                const r = parseFloat(this.value) || 0;
                                const q = parseFloat(qtyInput.value) || 0;
                                if (q > 0 && r > 0) {
                                    priceInput.value = +(q * r).toFixed(2);
                                }
                                updateEditModalCalculations();
                            });

                            priceInput.addEventListener('input', function() {
                                priceInput.dataset.manual = 'true';
                                const p = parseFloat(this.value) || 0;
                                const q = parseFloat(qtyInput.value) || 0;
                                if (q > 0 && p > 0) {
                                    rateInput.value = +(p / q).toFixed(2);
                                }
                                updateEditModalCalculations();
                            });

                            gstSelect.addEventListener('change', function() {
                                updateEditModalCalculations();
                            });
                        });
                    }

                    updateEditModalCalculations();
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

        /* Enforce perfect alignment in material table */
        .custom-material-table th,
        .custom-material-table td {
            vertical-align: middle !important;
            padding: 10px 12px !important;
        }

        .custom-material-table thead th {
            background-color: #f8fafc !important;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0 !important;
            vertical-align: middle !important;
        }

        .custom-material-table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.04) !important;
        }

        .custom-material-table .form-button-action {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .custom-material-table .form-button-action .btn-link {
            text-decoration: none;
        }
    </style>
@endsection
