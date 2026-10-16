@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <!-- Header matching screenshot -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <h3 class="fw-bold mb-0 text-dark">Property</h3>
                    <ul class="breadcrumbs mb-0 d-flex align-items-center list-unstyled gap-2 text-muted" style="font-size: 13px;">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted"><i class="icon-home"></i></a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item">Property</li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item text-primary fw-semibold">Property List</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <a href="{{ route('agent.list') }}" class="btn btn-primary px-3 py-2 btn-round d-flex align-items-center gap-2">
                        <i class="fa fa-users"></i>
                        <span>View Agents</span>
                    </a>
                    <a href="{{ route('property.form') }}" class="btn btn-primary px-3 py-2 btn-round d-flex align-items-center gap-2">
                        <i class="fa fa-plus"></i>
                        <span>Add Property</span>
                    </a>
                </div>
            </div>

            <!-- Flash alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show w-100 mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show w-100 mb-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Property Cards List -->
            @if ($properties->isEmpty())
                <div class="card p-5 text-center text-muted border-0 shadow-sm" style="border-radius: 12px;">
                    <i class="fa fa-building fa-3x mb-3 text-secondary"></i>
                    <h5>No Property Found</h5>
                    <p class="mb-3">You haven't added any properties yet.</p>
                    <div>
                        <a href="{{ route('property.form') }}" class="btn btn-primary btn-round">
                            <i class="fa fa-plus me-1"></i> Add Property
                        </a>
                    </div>
                </div>
            @else
                <div class="row justify-content-center">
                    <div class="col-12 col-xl-10">
                        @foreach ($properties as $property)
                            <div class="card property-card mb-4 border shadow-sm">
                                <!-- Card Header -->
                                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom-0">
                                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                        <span class="fw-bold text-dark fs-6">Property ID: #{{ $property->id }}</span>
                                        <span class="text-muted small">
                                            {{ $property->created_at ? $property->created_at->format('M, d Y h:i A') : '' }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge property-badge">{{ $property->type ?: 'Property' }}</span>
                                        <button type="button" class="btn btn-link p-1 text-primary view-btn"
                                            data-id="{{ $property->id }}"
                                            data-name="{{ $property->name }}"
                                            data-location="{{ $property->location }}"
                                            data-type="{{ $property->type }}"
                                            data-amount="{{ $property->amount }}"
                                            data-amount-formatted="₹ {{ number_format((float)$property->amount, 2) }}"
                                            data-remarks="{{ $property->remarks }}"
                                            data-date="{{ $property->created_at ? $property->created_at->format('M, d Y h:i A') : '' }}"
                                            data-image="{{ $property->image ? asset('storage/' . $property->image) : '' }}"
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-link p-1 text-primary edit-btn"
                                            data-id="{{ $property->id }}"
                                            data-name="{{ $property->name }}"
                                            data-location="{{ $property->location }}"
                                            data-type="{{ $property->type }}"
                                            data-amount="{{ $property->amount }}"
                                            data-remarks="{{ $property->remarks }}"
                                            data-image="{{ $property->image ? asset('storage/' . $property->image) : '' }}"
                                            title="Edit Property">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-link p-1 text-danger delete-btn"
                                            data-id="{{ $property->id }}"
                                            data-name="{{ $property->name }}"
                                            title="Delete Property">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="card-body px-4 pt-1 pb-4">
                                    <!-- Property Photo -->
                                    <div class="text-center my-3">
                                        @if ($property->image)
                                            <img src="{{ asset('storage/' . $property->image) }}" alt="{{ $property->name }}"
                                                class="property-image img-fluid shadow-sm">
                                        @else
                                            <div class="property-image-placeholder mx-auto d-flex align-items-center justify-content-center text-muted shadow-sm">
                                                <i class="fa fa-image fa-2x text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 2-Column Property Details -->
                                    <div class="row mt-4 px-lg-3 g-3">
                                        <div class="col-md-6">
                                            <div class="property-info-row mb-2">
                                                <span class="fw-bold text-dark">Property Name:</span>
                                                <span class="text-secondary ms-1">{{ $property->name }}</span>
                                            </div>
                                            <div class="property-info-row mb-2">
                                                <span class="fw-bold text-dark">Property Location:</span>
                                                <span class="text-secondary ms-1">{{ $property->location }}</span>
                                            </div>
                                            <div class="property-info-row mb-2">
                                                <span class="fw-bold text-dark">Property Amount:</span>
                                                <span class="text-secondary ms-1">₹ {{ number_format((float)$property->amount, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="property-info-row mb-2">
                                                <span class="fw-bold text-dark">Property Type:</span>
                                                <span class="text-secondary ms-1">{{ $property->type }}</span>
                                            </div>
                                            <div class="property-info-row mb-2">
                                                <span class="fw-bold text-dark">Remarks:</span>
                                                <span class="text-secondary ms-1">{{ $property->remarks }}</span>
                                            </div>
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

    <!-- View Property Modal -->
    <div class="modal fade" id="viewPropertyModal" tabindex="-1" aria-labelledby="viewPropertyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-light py-3">
                    <h5 class="modal-title fw-bold" id="viewPropertyModalLabel">
                        <i class="fa fa-building text-primary me-2"></i><span id="view_modal_id">Property Details</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4" id="view_img_container">
                        <img id="view_image" src="" alt="Property" class="img-fluid rounded shadow-sm" style="max-height: 260px; object-fit: cover;">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Property Name</label>
                            <p class="fw-semibold text-dark mb-0 fs-6" id="view_name"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Property Type</label>
                            <div>
                                <span class="badge property-badge" id="view_type"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Property Location</label>
                            <p class="fw-semibold text-dark mb-0" id="view_location"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Property Amount</label>
                            <p class="fw-bold text-primary mb-0 fs-6" id="view_amount"></p>
                            <small class="text-muted d-block" id="view_amount_words"></small>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small mb-1">Remarks</label>
                            <p class="text-dark bg-light p-2 rounded mb-0" id="view_remarks"></p>
                        </div>
                        <div class="col-12 text-end text-muted small" id="view_date"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Property Modal -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1" aria-labelledby="editPropertyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form id="editPropertyForm" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-light py-3">
                        <h5 class="modal-title fw-bold" id="editPropertyModalLabel">
                            <i class="fa fa-edit text-primary me-2"></i>Edit Property
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label fw-semibold">Property Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-control" required placeholder="Enter property name">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_type" class="form-label fw-semibold">Property Type <span class="text-danger">*</span></label>
                                <select name="type" id="edit_type" class="form-select" required>
                                    <option value="Plot">Plot</option>
                                    <option value="Land">Land</option>
                                    <option value="Flat">Flat</option>
                                    <option value="House">House</option>
                                    <option value="Commercial">Commercial</option>
                                    <option value="Villa">Villa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_amount" class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="1" min="0" name="amount" id="edit_amount" class="form-control" required placeholder="Enter amount"
                                       oninput="document.getElementById('edit_amount_words').innerText = numberToWordsIndian(this.value);">
                                <small id="edit_amount_words" class="form-text text-muted"></small>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_image" class="form-label fw-semibold">Change Image (Optional)</label>
                                <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep existing photo.</small>
                            </div>
                            <div class="col-12">
                                <label for="edit_location" class="form-label fw-semibold">Property Location <span class="text-danger">*</span></label>
                                <textarea name="location" id="edit_location" class="form-control" rows="2" required placeholder="Enter location"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="edit_remarks" class="form-label fw-semibold">Remarks</label>
                                <textarea name="remarks" id="edit_remarks" class="form-control" rows="2" placeholder="Enter remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save me-1"></i> Update Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Property Confirmation Modal -->
    <div class="modal fade" id="deletePropertyModal" tabindex="-1" aria-labelledby="deletePropertyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form id="deletePropertyForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger-subtle py-3">
                        <h5 class="modal-title fw-bold text-danger" id="deletePropertyModalLabel">
                            <i class="fa fa-exclamation-triangle me-2"></i>Confirm Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-6 mb-2">Are you sure you want to delete this property?</p>
                        <p class="fw-bold text-dark fs-5 mb-0" id="delete_property_name"></p>
                        <small class="text-muted d-block mt-2">This action cannot be undone.</small>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa fa-trash-alt me-1"></i> Yes, Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View Property Modal Handler
            $(document).on('click', '.view-btn', function() {
                const btn = $(this);
                const id = btn.data('id');
                const name = btn.data('name');
                const location = btn.data('location');
                const type = btn.data('type');
                const amount = btn.data('amount');
                const amountFormatted = btn.data('amount-formatted');
                const remarks = btn.data('remarks');
                const date = btn.data('date');
                const image = btn.data('image');

                $('#view_modal_id').text('Property ID: #' + id);
                $('#view_name').text(name || '-');
                $('#view_type').text(type || 'Property');
                $('#view_location').text(location || '-');
                $('#view_amount').text(amountFormatted || ('₹ ' + amount));
                $('#view_amount_words').text(amount ? numberToWordsIndian(amount) : '');
                $('#view_remarks').text(remarks || 'No remarks provided.');
                $('#view_date').text('Created: ' + (date || '-'));

                if (image) {
                    $('#view_image').attr('src', image);
                    $('#view_img_container').show();
                } else {
                    $('#view_img_container').hide();
                }

                $('#viewPropertyModal').modal('show');
            });

            // Edit Property Modal Handler
            $(document).on('click', '.edit-btn', function() {
                const btn = $(this);
                const id = btn.data('id');
                const name = btn.data('name');
                const location = btn.data('location');
                const type = btn.data('type');
                const amount = btn.data('amount');
                const remarks = btn.data('remarks');

                const updateUrl = "{{ route('property.update', ':id') }}".replace(':id', id);
                $('#editPropertyForm').attr('action', updateUrl);

                $('#edit_name').val(name);
                $('#edit_type').val(type);
                $('#edit_amount').val(amount);
                $('#edit_amount_words').text(amount ? numberToWordsIndian(amount) : '');
                $('#edit_location').val(location);
                $('#edit_remarks').val(remarks);
                $('#edit_image').val('');

                $('#editPropertyModal').modal('show');
            });

            // Delete Property Modal Handler
            $(document).on('click', '.delete-btn', function() {
                const btn = $(this);
                const id = btn.data('id');
                const name = btn.data('name');

                const deleteUrl = "{{ route('property.delete', ':id') }}".replace(':id', id);
                $('#deletePropertyForm').attr('action', deleteUrl);
                $('#delete_property_name').text(name || ('Property #' + id));

                $('#deletePropertyModal').modal('show');
            });
        });
    </script>

    <style>
        .property-card {
            border-radius: 10px;
            border-color: #e5e7eb !important;
            background: #ffffff;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .property-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
        }

        .property-badge {
            background-color: #0284c7 !important;
            color: #ffffff !important;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 14px;
            letter-spacing: 0.3px;
        }

        .property-image {
            max-width: 420px;
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .property-image-placeholder {
            max-width: 420px;
            width: 100%;
            height: 200px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
        }

        .property-info-row {
            font-size: 14px;
            line-height: 1.6;
        }

        .property-info-row .fw-bold {
            color: #1e293b;
            font-weight: 600 !important;
        }
    </style>
@endsection
