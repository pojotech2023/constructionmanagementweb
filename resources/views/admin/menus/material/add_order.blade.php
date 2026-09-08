@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <h3 class="text-center pb-3 mt-3 mb-0">Inward {{ ucfirst($materialType) }} Order</h3>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow-lg p-4 mt-2">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                    <form id="requestForm" action="{{ route('add.order') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">
                        <input type="hidden" name="vendor_id" id="vendor_id">
                        <input type="hidden" name="material_type" value="{{ ucfirst($materialType) }}">

                        <div class="row mb-3 align-items-center">
                            <label for="vendor_name" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Vendor Name</label>
                            <div class="col-sm-8 col-md-8 position-relative">
                                <input type="text" id="vendor_name" name="vendor_name" class="form-control"
                                    placeholder="Type Vendor Name..." autocomplete="off">
                                <div id="vendor_suggestions" class="list-group position-absolute w-100"
                                    style="z-index: 1000; display: none;"></div>
                                @error('vendor_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="vendor_mobile" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Vendor Mobile No</label>
                            <div class="col-sm-8 col-md-8">
                                <input type="text" id="vendor_mobile" name="vendor_mobile" class="form-control"
                                    placeholder="Auto-filled on selecting vendor" maxlength="10" minlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" readonly>
                                @error('vendor_mobile')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-start">
                            <label for="vendor_address" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end pt-2">Vendor Address</label>
                            <div class="col-sm-8 col-md-8">
                                <textarea id="vendor_address" name="vendor_address" class="form-control" rows="2" placeholder="Auto-filled on selecting vendor" readonly></textarea>
                                @error('vendor_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="vendor_gstin" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Vendor GST</label>
                            <div class="col-sm-8 col-md-8">
                                <input id="vendor_gstin" name="vendor_gstin" type="text" class="form-control"
                                    placeholder="Auto-filled on selecting vendor" readonly />
                            </div>
                        </div>

                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                        @endphp
                        <div class="row mb-3 align-items-center">
                            <label for="date" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Date</label>
                            <div class="col-sm-8 col-md-8">
                                <input type="date" class="form-control" id="date" name="date">
                                @error('date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="quantity" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Quantity</label>
                            <div class="col-sm-8 col-md-8">
                                <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity">
                                @error('quantity')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="dynamic-fields"></div>

                        <div class="row mb-3 align-items-center">
                            <label for="supervisor_name" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Supervisor Name</label>
                            <div class="col-sm-8 col-md-8">
                                <input type="text" class="form-control" name="supervisor_name" id="supervisor_name"
                                    placeholder="Enter supervisor name"
                                    value="{{ old('supervisor_name', $supervisor->name ?? '') }}">
                                @error('supervisor_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="supervisor_phone" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Supervisor Phone No</label>
                            <div class="col-sm-8 col-md-8">
                                <input type="text" class="form-control" name="supervisor_phone" id="supervisor_phone"
                                    placeholder="Enter phone number" maxlength="10" minlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                    value="{{ old('supervisor_phone', $supervisor->mobile_no ?? '') }}">
                                @error('supervisor_phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="price" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Total Price</label>
                            <div class="col-sm-8 col-md-8">
                                <input id="price" name="price" type="number" class="form-control no-arrow"
                                    min="0" step="0.01" placeholder="Enter total price"
                                    oninput="document.getElementById('price_words').innerText = numberToWordsIndian(this.value);" />
                                <small id="price_words" class="form-text text-muted"></small>
                                @error('price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="attachment" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Invoice</label>
                            <div class="col-sm-8 col-md-8">
                                <input type="file" name="attachment" id="attachment" class="form-control" accept="image/*,.pdf">
                                @error('attachment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-sm-8 col-md-8 offset-sm-4 offset-md-3">
                                <button type="submit" class="btn btn-primary w-100">Inward Order</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="statusModal" class="custom-status-modal d-none">
        <div class="custom-status-backdrop"></div>
        <div class="custom-status-dialog">
            <div class="custom-status-icon">✓</div>
            <h4 id="statusModalTitle" class="custom-status-title">Success</h4>
            <p id="statusModalMessage" class="custom-status-message"></p>
            <button type="button" id="statusModalButton" class="btn btn-primary px-4">OK</button>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showStatusModal(message, options = {}) {
            const modal = document.getElementById('statusModal');
            const title = document.getElementById('statusModalTitle');
            const messageBox = document.getElementById('statusModalMessage');
            const button = document.getElementById('statusModalButton');
            const icon = modal.querySelector('.custom-status-icon');

            title.textContent = options.title || 'Success';
            messageBox.textContent = message;
            button.textContent = options.buttonText || 'OK';

            modal.classList.remove('d-none', 'is-error');

            if (options.type === 'error') {
                modal.classList.add('is-error');
                icon.textContent = '!';
            } else {
                modal.classList.remove('is-error');
                icon.textContent = '✓';
            }

            button.onclick = function() {
                modal.classList.add('d-none');
                if (typeof options.onConfirm === 'function') {
                    options.onConfirm();
                }
            };
        }

        $(document).ready(function() {
            $('#vendor_name').on('input', function() {
                let query = $(this).val();
                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('vendors.search') }}",
                        type: 'GET',
                        data: {
                            name: query
                        },
                        success: function(data) {
                            let suggestions = '';
                            data.forEach(function(vendor) {
                                suggestions += `
                        <a href="#"
                            class="list-group-item list-group-item-action vendor-option"
                            data-id="${vendor.id}"
                            data-name="${vendor.name}"
                            data-mobile="${vendor.mobile_no}"
                            data-address="${vendor.address}"
                            data-gst="${vendor.gst ?? ''}">
                            ${vendor.name}
                        </a>`;
                            });
                            $('#vendor_suggestions').html(suggestions).show();
                        }
                    });
                } else {
                    $('#vendor_suggestions').hide();
                }
            });

            $(document).on('click', '.vendor-option', function(e) {
                e.preventDefault();
                $('#vendor_name').val($(this).data('name'));
                $('#vendor_mobile').val($(this).data('mobile'));
                $('#vendor_id').val($(this).data('id'));
                $('#vendor_address').val($(this).data('address'));
                $('#vendor_gstin').val($(this).data('gst'));
                $('#vendor_suggestions').hide();
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('#vendor_name, #vendor_suggestions').length) {
                    $('#vendor_suggestions').hide();
                }
            });

            $('#requestForm').on('submit', function(e) {
                e.preventDefault();
                $('#loadingSpinner').removeClass('d-none');
                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#loadingSpinner').addClass('d-none');
                        if (response.status === 'success') {
                            showStatusModal(response.message, {
                                title: 'Order Added',
                                buttonText: 'OK',
                                onConfirm: function() {
                                    form[0].reset();
                                    const siteId = "{{ $siteId }}";
                                    const materialType = "{{ $materialType }}";
                                    const enteredDate = formData.get('date');
                                    let url = "/admin/public/admin/material/" + siteId + "/" + materialType;
                                    if (enteredDate) {
                                        url += "?month=" + enteredDate.slice(0, 7);
                                    }
                                    window.location.href = url;
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#loadingSpinner').addClass('d-none');
                        let message = "Something went wrong!";
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(e => e[0]).join("\n");
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showStatusModal(message, {
                            title: 'Error',
                            type: 'error',
                            buttonText: 'Close'
                        });
                    }
                });
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let materialType = "{{ $materialType }}";
            loadUnitField(materialType);
        });

        // Category/type options per material, built from the construction-materials list.
        // Anything not listed here just gets the plain Unit dropdown.
        const materialCategoryConfig = {
            // 1. Basic Construction Materials
            bricks:         { label: 'Category Name', options: ['Red Brick', 'Fly Ash Bricks', 'AAC Blocks', 'Concrete Blocks', 'Solid Block', 'Stone'] },
            sand:           { label: 'Sand Type', options: ['River Sand', 'M-Sand', 'P-Sand', 'Filling Sand'] },
            cement:         { label: 'Cement Grade', options: ['OPC 43 Grade', 'OPC 53 Grade', 'PPC', 'White Cement'] },
            aggregate:      { label: 'Aggregate Size', options: ['6mm Aggregate', '12mm Aggregate', '20mm Aggregate', '40mm Aggregate'] },

            // 2. Steel & Metal Materials
            steel:          { label: 'Item Type', options: ['TMT Steel Bars', 'Mild Steel (MS) Rods', 'Binding Wire', 'GI Wire', 'Steel Angles', 'Steel Channels', 'Steel Plates', 'MS Pipes', 'GI Pipes', 'Stainless Steel'] },

            // 3. Concrete & Structural Materials
            rmc:            { label: 'Item Type', options: ['Ready-Mix Concrete (RMC)', 'Precast Concrete', 'Concrete Admixtures', 'Waterproofing Admixtures', 'Concrete Curing Compounds', 'Shuttering/Plywood', 'Formwork Materials', 'Reinforcement Mesh', 'M15', 'M20', 'M25', 'M30', 'M35'] },

            // 4. Wood & Carpentry Materials
            woodcarpentry:  { label: 'Item Type', options: ['Plywood', 'MDF', 'Particle Board', 'Hardwood', 'Softwood', 'Teak Wood', 'Wooden Frames', 'Wooden Doors', 'Laminates', 'Veneers'] },

            // 5. Doors & Windows
            doorswindows:   { label: 'Item Type', options: ['Wooden Doors', 'UPVC Doors', 'UPVC Windows', 'Aluminium Windows', 'Aluminium Doors', 'Glass', 'Toughened Glass', 'Laminated Glass', 'Door Frames', 'Window Frames', 'Locks', 'Hinges', 'Door Handles'] },

            // 6. Flooring & Wall Materials
            tiles:          { label: 'Item Type', options: ['Floor Tiles', 'Wall Tiles', 'Vitrified Tiles', 'Ceramic Tiles', 'Paver Blocks', 'Interlocking Blocks', 'Skirting Tiles'] },
            granite:        { label: 'Item Type', options: ['Granite', 'Black Granite', 'Colour Granite', 'Marble', 'Kota Stone', 'Kitchen Platform Slab'] },

            // 7. Painting Materials
            painting:       { label: 'Item Type', options: ['Wall Putty', 'Primer', 'Interior Paint', 'Exterior Paint', 'Enamel Paint', 'Emulsion Paint', 'Waterproof Paint', 'Texture Paint', 'Thinner', 'Paint Brushes', 'Rollers'] },

            // 8. Plumbing Materials
            plumber:        { label: 'Item Type', options: ['PVC Pipes', 'CPVC Pipes', 'UPVC Pipes', 'HDPE Pipes', 'GI Pipes', 'PPR Pipes', 'Pipe Fittings', 'Elbows', 'Tees', 'Couplers', 'Valves', 'Water Tanks', 'Wash Basins', 'Toilets', 'Faucets/Taps'] },

            // 9. Electrical Materials
            electricalwire: { label: 'Item Type', options: ['0.75 sq mm Wire', '1 sq mm Wire', '1.5 sq mm Wire', '2.5 sq mm Wire', '4 sq mm Wire', '6 sq mm Wire', '10 sq mm Wire', 'Cables', 'Conduits', 'Junction Boxes', 'Switches', 'Sockets', 'MCB', 'RCCB', 'Distribution Boards', 'LED Lights', 'Ceiling Fans', 'Exhaust Fans', 'Cable Trays'] },

            // 10. Hardware Materials
            hardware:       { label: 'Item Type', options: ['Nails', 'Screws', 'Nuts & Bolts', 'Washers', 'Anchors', 'Wall Plugs', 'Brackets', 'Clamps', 'Fasteners', 'Metal Fittings'] },

            // 11. Waterproofing & Insulation
            waterproofinginsulation: { label: 'Item Type', options: ['Waterproofing Chemicals', 'Waterproofing Membrane', 'Bitumen', 'Bituminous Coating', 'PVC Waterproofing Sheet', 'Sealants', 'Silicone', 'Expansion Joint Materials', 'Thermal Insulation', 'Acoustic Insulation'] },

            // 12. Roofing Materials
            roofing:        { label: 'Item Type', options: ['Roofing Sheets', 'GI Sheets', 'Colour-Coated Sheets', 'Polycarbonate Sheets', 'Roof Tiles', 'Bitumen Sheets', 'Roof Insulation', 'Ridge Caps', 'Gutter & Downpipes'] },

            // 13. Finishing Materials
            finishingmaterials: { label: 'Item Type', options: ['Wall Putty', 'Plaster', 'Gypsum', 'Gypsum Boards', 'Cement Boards', 'False Ceiling Materials', 'Decorative Panels', 'PVC Panels', 'Adhesives', 'Grout', 'Sealants'] },

            // 14. External/Outdoor Materials
            externaloutdoor: { label: 'Item Type', options: ['Kerb Stones', 'Paver Blocks', 'Compound Wall Blocks', 'Fencing Materials', 'Gate Materials', 'Drainage Pipes', 'Manhole Covers', 'Landscaping Stones', 'Cement Poles'] },

            // Other existing grid materials
            jally:          { label: 'Jally Size', options: ['6mm Jally', '12mm Jally', '20mm Jally', '40mm Jally'] },
            welding:        { label: 'Item Type', options: ['MS Rod', 'Welding Electrode', 'MS Angle', 'MS Channel'] },
            lift:           { label: 'Lift Type', options: ['Passenger Lift', 'Goods Lift', 'Hydraulic Lift'] },
            rcconcrete:     { label: 'Concrete Grade', options: ['M15', 'M20', 'M25', 'M30', 'M35'] },
            transport:      { label: 'Vehicle Type', options: ['Lorry', 'Tipper', 'JCB', 'Trailer'] },
            interior:       { label: 'Work Type', options: ['Woodwork', 'False Ceiling', 'Modular Kitchen', 'Wardrobe'] },
            gravel:         { label: 'Gravel Size', options: ['10mm', '20mm', '40mm'] },
            watercan:       { label: 'Can Size', options: ['20 Litre Can', '25 Litre Can'] },
            lorrywater:     { label: 'Tanker Size', options: ['5000 Litre', '8000 Litre', '12000 Litre'] },
            tea:            { label: 'Item Type', options: ['Tea', 'Coffee', 'Snacks'] },
        };

        function loadUnitField(materialType) {
            let unitOptions = '<option value="">Select Unit</option>' +
                `@foreach ($sharedUnits as $unitOption)<option value="{{ $unitOption }}">{{ $unitOption }}</option>@endforeach`;

            const config = materialCategoryConfig[materialType];
            let categoryHtml = '';
            if (config) {
                let optionsHtml = config.options.map(o => `<option value="${o}">${o}</option>`).join('');
                categoryHtml = `
            <div class="row mb-3 align-items-center">
                <label for="category_name" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">${config.label}</label>
                <div class="col-sm-8 col-md-8">
                    <select class="form-select" name="category_name" id="category_name">
                        <option value="">Select ${config.label}</option>
                        ${optionsHtml}
                    </select>
                </div>
            </div>`;
            }

            let unitHtml = `
            <div class="row mb-3 align-items-center">
                <label for="unit" class="col-sm-4 col-md-3 col-form-label fw-bold text-sm-end">Unit</label>
                <div class="col-sm-8 col-md-8">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;

            $("#dynamic-fields").html(categoryHtml + unitHtml);
        }
    </script>

    <style>
        .custom-status-modal {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-status-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
        }

        .custom-status-dialog {
            position: relative;
            width: min(420px, calc(100vw - 32px));
            background: #ffffff;
            border-radius: 20px;
            padding: 28px 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            text-align: center;
            border: 1px solid #dbeafe;
            animation: modalPop 0.2s ease-out;
        }

        .custom-status-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            font-size: 30px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(34, 197, 94, 0.25);
        }

        .custom-status-title {
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 700;
        }

        .custom-status-message {
            margin-bottom: 20px;
            color: #475569;
            white-space: pre-line;
        }

        .custom-status-modal.is-error .custom-status-dialog {
            border-color: #fecaca;
        }

        .custom-status-modal.is-error .custom-status-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 12px 24px rgba(239, 68, 68, 0.2);
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

@endsection
