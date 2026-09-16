@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="text-center fw-bold pb-2 mb-0">Add {{ ucfirst($subcontractorType) }} Service</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm p-4">

                    <!-- Blade alert for success -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif

                    <form id="requestForm" action="{{ route('add.service') }}" method="POST">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <input type="hidden" name="subcontractor_id" id="subcontractor_id">

                        <input type="hidden" name="subcontractor_type" value="{{ ucfirst($subcontractorType) }}">

                        <div id="dynamic-fields"></div>

                        <div class="row mb-3 align-items-center">
                            <label for="subcontractor_name" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Subcontractor Name</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap position-relative">
                                <input type="text" id="subcontractor_name" name="subcontractor_name" class="form-control"
                                    placeholder="Type Subcontractor Name..." autocomplete="off">
                                <div id="subcontractor_suggestions" class="list-group position-absolute w-100"
                                    style="z-index: 1000; display: none;"></div>
                                @error('subcontractor_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="subcontractor_mobile" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Subcontractor Mobile No</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" id="subcontractor_mobile" name="subcontractor_mobile" class="form-control"
                                    placeholder="Mobile Number" maxlength="10" minlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                                @error('subcontractor_mobile')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="no_counts" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">No Of Counts</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" id="no_counts" name="no_counts" class="form-control"
                                    placeholder="No Of Counts">
                                @error('no_counts')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-start">
                            <label for="subcontractor_address" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end pt-2">Subcontractor Address</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <textarea id="subcontractor_address" name="subcontractor_address" class="form-control" rows="2" placeholder="Subcontractor Address"></textarea>
                                @error('subcontractor_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                        @endphp
                        <div class="row mb-3 align-items-center">
                            <label for="date" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Date</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="date" class="form-control" name="date" id="date">
                                @error('date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="row mb-3 align-items-center">
                            <label for="amount" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Amount</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input id="amount" name="amount" type="number" class="form-control no-arrow"
                                    min="0" step="0.01" placeholder="Enter Amount"
                                    oninput="document.getElementById('amount_words').innerText = numberToWordsIndian(this.value);" />
                                <small id="amount_words" class="form-text text-muted"></small>
                                @error('amount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-start">
                            <label for="remarks" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end pt-2">Remarks</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <textarea id="remarks" name="remarks" class="form-control" rows="3"
                                    placeholder="Enter remarks">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-sm-8 col-md-8 col-lg-6 offset-sm-4 offset-md-3 offset-lg-2 form-input-wrap text-center">
                                <button type="submit" class="btn btn-primary px-4">Send Service to SubContractor WhatsApp
                                    <i class="fab fa-whatsapp me-1"></i>
                                </button>
                            </div>
                        </div>
                    </form>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        //Subcontractor search
        $(document).ready(function() {
            $('#subcontractor_name').on('input', function() {
                let query = $(this).val();
                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('subcontractors.search') }}",
                        type: 'GET',
                        data: {
                            name: query
                        },
                        success: function(data) {
                            let suggestions = '';
                            data.forEach(function(subcontractor) {
                                suggestions += `
                        <a href="#" 
                            class="list-group-item list-group-item-action subcontractor-option" 
                            data-id="${subcontractor.id}" 
                            data-name="${subcontractor.name}" 
                            data-mobile="${subcontractor.mobile_no}"
                            data-address="${subcontractor.address}">
                            ${subcontractor.name}
                        </a>`;
                            });
                            $('#subcontractor_suggestions').html(suggestions).show();
                        }
                    });
                } else {
                    $('#subcontractor_suggestions').hide();
                }
            });

            // Select subcontractor from suggestion
            $(document).on('click', '.subcontractor-option', function(e) {
                e.preventDefault();
                $('#subcontractor_name').val($(this).data('name'));
                $('#subcontractor_mobile').val($(this).data('mobile'));
                $('#subcontractor_id').val($(this).data('id'));
                $('#subcontractor_address').val($(this).data('address'));
                $('#subcontractor_suggestions').hide();
            });

            // Hide suggestions when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#subcontractor_name, #subcontractor_suggestions').length) {
                    $('#subcontractor_suggestions').hide();
                }
            });

            // Form submit handler for material request form
            $(document).ready(function() {
                $('#requestForm').on('submit', function(e) {
                    e.preventDefault();
                    $('#loadingSpinner').removeClass('d-none');
                    let form = $(this);
                    let formData = form.serialize();

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            $('#loadingSpinner').addClass('d-none');

                            if (response.status === 'success') {
                                if (response.whatsapp_url) {
                                    window.open(response.whatsapp_url, '_blank');
                                }
                                form[0].reset();

                                // Redirect
                                setTimeout(function() {
                                    const siteId = "{{ $siteId }}";
                                    const subcontractorType = "{{ $subcontractorType }}";
                                    const enteredDate = form.find('[name="date"]').val();
                                    let url = "/admin/public/admin/subcontractor/" +
                                        siteId + "/" + subcontractorType;
                                    if (enteredDate) {
                                        url += "?month=" + enteredDate.slice(0, 7);
                                    }
                                    window.location.href = url;
                                }, 500);
                            }
                        },
                        error: function(xhr) {
                            $('#loadingSpinner').addClass('d-none');
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let message = Object.values(errors).map(e => e[0]).join(
                                    "\n");
                                // alert("Validation Errors:\n" + message);
                            } else {
                                alert("Something went wrong!");
                            }
                        }
                    });
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let subcontractorType = "{{ $subcontractorType }}";
            loadCategoryField(subcontractorType);
        });

        // Sub-work-type options per subcontractor trade (construction subcontractor catalog)
        const subcontractorCategoryConfig = {
            civilworks: {
                label: 'Work Type',
                options: ['Excavation', 'Earthwork', 'Foundation', 'RCC works', 'Concrete works'],
            },
            masonworks: {
                label: 'Work Type',
                options: ['Brickwork', 'Blockwork', 'Stone masonry'],
            },
            steelstructural: {
                label: 'Work Type',
                options: ['Structural steel', 'Steel fabrication', 'Steel erection', 'Reinforcement steel work'],
            },
            carpenter: {
                label: 'Work Type',
                options: ['Formwork / shuttering', 'Doors', 'Wooden works', 'Interior carpentry'],
            },
            plumber: {
                label: 'Work Type',
                options: ['Water supply', 'Drainage', 'Sanitary works', 'Plumbing fixtures'],
            },
            electrician: {
                label: 'Work Type',
                options: ['Electrical wiring', 'DB/panel installation', 'Lighting', 'Power systems', 'Earthing'],
            },
            painter: {
                label: 'Work Type',
                options: ['Interior painting', 'Exterior painting', 'Texture coating', 'Waterproof coating'],
            },
            waterproofingcontractor: {
                label: 'Work Type',
                options: ['Terrace waterproofing', 'Basement waterproofing', 'Toilet waterproofing', 'Swimming pool waterproofing'],
            },
            flooringcontractor: {
                label: 'Work Type',
                options: ['Tiles', 'Marble', 'Granite', 'Vinyl', 'Epoxy flooring'],
            },
            falseceiling: {
                label: 'Work Type',
                options: ['Gypsum ceiling', 'Grid ceiling', 'PVC ceiling', 'Decorative ceiling'],
            },
            aluminiumglass: {
                label: 'Work Type',
                options: ['Aluminium windows', 'Glass doors', 'Curtain walls', 'Glass partitions'],
            },
            hvac: {
                label: 'Work Type',
                options: ['Air-conditioning', 'Ducting', 'Ventilation', 'HVAC maintenance'],
            },
            firesafety: {
                label: 'Work Type',
                options: ['Fire alarm', 'Fire fighting system', 'Sprinkler system', 'Fire extinguishers'],
            },
            fabricationcontractor: {
                label: 'Work Type',
                options: ['Gates', 'Railings', 'Grills', 'Staircase fabrication'],
            },
            roadpaving: {
                label: 'Work Type',
                options: ['Asphalt works', 'Concrete roads', 'Interlock paving', 'Kerb works'],
            },
            landscaping: {
                label: 'Work Type',
                options: ['Garden works', 'Irrigation', 'Plantation', 'Outdoor landscaping'],
            },
            interiorworkscontractor: {
                label: 'Work Type',
                options: ['Modular furniture', 'Partitions', 'Interior finishing', 'Office interiors'],
            },
            demolitioncontractor: {
                label: 'Work Type',
                options: ['Building demolition', 'Concrete breaking', 'Debris removal'],
            },
            heavyequipment: {
                label: 'Work Type',
                options: ['Excavator', 'JCB', 'Crane', 'Bulldozer', 'Machinery rental'],
            },
            mep: {
                label: 'Work Type',
                options: ['Mechanical', 'Electrical', 'Plumbing', 'Building services'],
            },
        };

        function loadCategoryField(subcontractorType) {
            const config = subcontractorCategoryConfig[subcontractorType];

            if (!config) {
                document.getElementById('dynamic-fields').innerHTML = '';
                return;
            }

            const optionsHtml = config.options.map(o => `<option value="${o}">${o}</option>`).join('');
            const html = `
                <div class="row mb-3 align-items-center">
                    <label for="category_name" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">${config.label}</label>
                    <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                        <select class="form-select" name="category_name" id="category_name">
                            <option value="">Select ${config.label}</option>
                            ${optionsHtml}
                        </select>
                    </div>
                </div>`;

            document.getElementById('dynamic-fields').innerHTML = html;
        }
    </script>

    <style>
        .form-input-wrap {
            max-width: 540px;
        }
    </style>
@endsection
