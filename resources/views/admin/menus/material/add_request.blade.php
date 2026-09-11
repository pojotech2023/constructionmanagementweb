@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="text-center fw-bold pb-2 mb-0">Add {{ ucfirst($materialType) }} Request</h3>
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

                    <form id="requestForm" action="{{ route('add.request') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">
                        <input type="hidden" name="vendor_id" id="vendor_id">
                        <input type="hidden" name="material_type" value="{{ ucfirst($materialType) }}">

                        <div class="row mb-3 align-items-center">
                            <label for="vendor_name" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Vendor Name</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap position-relative">
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
                            <label for="vendor_mobile" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Vendor Mobile No</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" id="vendor_mobile" name="vendor_mobile" class="form-control"
                                    placeholder="Mobile Number" maxlength="10" minlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                                @error('vendor_mobile')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-start">
                            <label for="vendor_address" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end pt-2">Vendor Address</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <textarea id="vendor_address" name="vendor_address" class="form-control" rows="2" placeholder="Vendor Address"></textarea>
                                @error('vendor_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="items" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Items</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" class="form-control" id="items" name="items"
                                    placeholder="Enter material type" value="{{ ucfirst($materialType) }}">
                                @error('items')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="quantity" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Quantity</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity">
                                @error('quantity')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="dynamic-fields"></div>

                        <div class="row mb-3 align-items-center">
                            <label for="date_of_delivery" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Date of Delivery</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="date" class="form-control" id="date_of_delivery" name="date_of_delivery" value="">
                                @error('date_of_delivery')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="supervisor_name" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Supervisor Name</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" class="form-control" name="supervisor_name" id="supervisor_name"
                                    placeholder="Enter supervisor name" 
                                    value="{{ old('supervisor_name', $supervisor->name ?? '') }}">
                                @error('supervisor_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label for="supervisor_phone" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Supervisor Phone No</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
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
                            <label for="price" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Price</label>
                            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                                <input type="text" class="form-control" name="price" id="price"
                                    placeholder="Enter Price"
                                    value=""
                                    oninput="document.getElementById('price_words').innerText = numberToWordsIndian(this.value);">
                                <small id="price_words" class="form-text text-muted"></small>
                                @error('price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-sm-8 col-md-8 col-lg-6 offset-sm-4 offset-md-3 offset-lg-2 form-input-wrap">
                                <button type="submit" class="btn btn-primary w-100">Send Request to Vendor WhatsApp
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
        //Vendor search
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
                            data-address="${vendor.address}">
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

            // Select vendor from suggestion
            $(document).on('click', '.vendor-option', function(e) {
                e.preventDefault();
                $('#vendor_name').val($(this).data('name'));
                $('#vendor_mobile').val($(this).data('mobile'));
                $('#vendor_id').val($(this).data('id'));
                $('#vendor_address').val($(this).data('address'));
                $('#vendor_suggestions').hide();
            });

            // Hide suggestions when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#vendor_name, #vendor_suggestions').length) {
                    $('#vendor_suggestions').hide();
                }
            });

            // Form submit handler for material request form
            $(document).ready(function() {
    $('#requestForm').on('submit', function(e) {
        e.preventDefault();
        $('#loadingSpinner').removeClass('d-none');
        
        let form = $(this);
        let formData = new FormData(this); // ✅ use FormData for file uploads

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#loadingSpinner').addClass('d-none');

                if (response.status === 'success') {
                    window.open(response.whatsapp_url, '_blank');
                    form[0].reset();

                    setTimeout(function() {
                        const siteId = "{{ $siteId }}";
                        const materialType = "{{ $materialType }}";
                        window.location.href = "/admin/public/admin/material/" + siteId + "/" + materialType;
                    }, 500);
                }
            },
            error: function(xhr) {
                $('#loadingSpinner').addClass('d-none');

                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    let message = Object.values(errors).map(e => e[0]).join("\n");
                    alert("Validation Error:\n" + message);

                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    // Laravel error message (like exception message)
                    alert("Server Error:\n" + xhr.responseJSON.message);

                } else {
                    // Raw response (fallback)
                    alert("Unexpected Error:\n" + xhr.responseText);
                    console.log("Error details:", xhr);
                }
            }
        });
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

// Comprehensive Construction Materials Configuration
const materialCategoryConfig = {
    // 1. Basic & Masonry Materials
    bricks: {
        label: 'Brick / Block Type',
        options: ['Red Clay Brick', 'Fly Ash Bricks', 'AAC Blocks', 'Solid Concrete Blocks', 'Hollow Concrete Blocks', 'Wire Cut Bricks', 'Clay Paver Bricks', 'Stone'],
        specLabel: 'Size / Specification',
        specOptions: ['9" x 4" x 3"', '4" AAC Block (600x200x100mm)', '6" AAC Block (600x200x150mm)', '8" AAC Block (600x200x200mm)', '4" Solid Block', '6" Solid Block', '9" Solid Block', 'Chamber Bricks', 'Country Bricks'],
        units: ['Nos', 'Pieces', 'Load', 'Thousand'],
        attachment: false
    },
    sand: {
        label: 'Sand Type',
        options: ['River Sand', 'M-Sand (Manufactured Sand - Concreting)', 'P-Sand (Plastering Sand)', 'Filling Sand / Pit Sand'],
        specLabel: 'Zone / Source',
        specOptions: ['Zone II Concreting Sand', 'Double Washed M-Sand', 'Fine Plastering P-Sand', 'Pit Sand / Earth Filling'],
        units: ['CFT', 'Ton', 'Brass', 'Units', 'Load'],
        attachment: false
    },
    cement: {
        label: 'Cement Grade / Type',
        options: ['OPC 53 Grade', 'OPC 43 Grade', 'PPC (Portland Pozzolana)', 'PSC (Portland Slag Cement)', 'White Cement', 'Waterproof Cement'],
        specLabel: 'Brand / Manufacturer',
        specOptions: ['UltraTech', 'ACC', 'Ambuja', 'Dalmia', 'Ramco', 'Chettinad', 'Birla A1', 'Priya Cement', 'Maha Cement', 'Zuari', 'Coromandel', 'JK Cement'],
        units: ['Bags', 'Ton', 'Kg'],
        attachment: false
    },
    aggregate: {
        label: 'Aggregate Size',
        options: ['6mm Aggregate', '10mm Aggregate', '12mm Aggregate', '20mm Aggregate', '40mm Aggregate', 'GSB (Granular Sub Base)', 'WMM (Wet Mix Macadam)', 'Quarry Dust / Stone Dust'],
        specLabel: 'Stone Type / Spec',
        specOptions: ['Blue Metal Crushed Stone', 'Hard Granite Aggregate', 'Washed Aggregate', 'Quarry Dust'],
        units: ['CFT', 'Ton', 'Brass', 'Units', 'Load'],
        attachment: false
    },
    jally: {
        label: 'Jally Size',
        options: ['6mm Jally', '12mm Jally', '20mm Jally', '40mm Jally', 'Stone Dust'],
        specLabel: 'Type',
        specOptions: ['Blue Metal Jally', 'Hand Broken Jally', 'Crusher Run'],
        units: ['CFT', 'Ton', 'Brass', 'Units', 'Load'],
        attachment: false
    },
    gravel: {
        label: 'Material Type',
        options: ['Gravel', 'Red Earth / Soil', 'Moorum', 'Quarry Dust', 'Filling Soil'],
        specLabel: 'Application',
        specOptions: ['Foundation Filling', 'Plinth Filling', 'Road Base Compaction', 'Landscaping'],
        units: ['CFT', 'Ton', 'Brass', 'Load', 'Trips'],
        attachment: false
    },

    // 2. Structural, Steel & Concrete
    steel: {
        label: 'Item Type',
        options: ['TMT Steel Bars', 'Binding Wire', 'Mild Steel (MS) Rods', 'GI Wire', 'Steel Angles', 'Steel Channels', 'Steel Plates', 'MS Pipes', 'GI Pipes', 'Stainless Steel (SS)'],
        specLabel: 'Diameter / Grade',
        specOptions: ['8mm TMT Bar', '10mm TMT Bar', '12mm TMT Bar', '16mm TMT Bar', '20mm TMT Bar', '25mm TMT Bar', '32mm TMT Bar', 'Fe 500D', 'Fe 550D', '18 Gauge Binding Wire', '20 Gauge Binding Wire'],
        units: ['Ton', 'Tons', 'Kg', 'Bundles', 'Nos'],
        attachment: false
    },
    rmc: {
        label: 'Concrete Grade',
        options: ['M10', 'M15', 'M20', 'M25', 'M30', 'M35', 'M40', 'M45', 'M50', 'Ready-Mix Concrete (RMC)', 'Precast Concrete'],
        specLabel: 'Pour Structure / Slump',
        specOptions: ['Footing / Raft Foundation', 'Columns', 'Plinth Beams', 'Roof Slab & Beams', 'Retaining Wall', 'Slump 100-120mm', 'Slump 120-150mm'],
        units: ['M Cube', 'Load'],
        attachment: false
    },
    rcconcrete: {
        label: 'Concrete Grade',
        options: ['M15', 'M20', 'M25', 'M30', 'M35'],
        specLabel: 'Structure Element',
        specOptions: ['Columns', 'Beams', 'Roof Slab', 'Lintel & Sunshade', 'Foundation'],
        units: ['M Cube', 'CFT'],
        attachment: false
    },

    // 3. Wood, Carpentry, Doors & Windows
    woodcarpentry: {
        label: 'Item Type',
        options: ['Plywood (Commercial MR)', 'Waterproof Plywood (BWP/BWR)', 'Marine Plywood', 'MDF Board', 'HDF Board', 'Particle Board', 'Teak Wood', 'Sal Wood', 'Neem Wood', 'Pine Wood', 'Wooden Frames', 'Wooden Doors', 'Laminates (Mica)', 'Veneers'],
        specLabel: 'Thickness / Size',
        specOptions: ['6mm (8x4 ft)', '8mm (8x4 ft)', '12mm (8x4 ft)', '16mm (8x4 ft)', '18mm (8x4 ft)', '1mm Laminate', '0.8mm Laminate', '5x3 inch Frame', '4x2.5 inch Frame'],
        units: ['Sqft', 'CFT', 'Sheets', 'Rft', 'Nos'],
        attachment: true
    },
    doorswindows: {
        label: 'Item Type',
        options: ['Main Entrance Wooden Door', 'Bedroom Flush Door', 'UPVC Sliding Windows', 'UPVC Casement Windows', 'Aluminium Sliding Windows', 'Aluminium Doors', 'Glass Partition', 'Toughened Glass', 'Laminated Glass', 'Door Frames', 'Window Frames', 'Mosquito Mesh Doors', 'Bathroom PVC Doors'],
        specLabel: 'Dimension / Glass Spec',
        specOptions: ['7\' x 3\'6" (Main Door)', '7\' x 3\' (Bedroom)', '7\' x 2\'6" (Toilet)', '4\' x 4\' (Window)', '5\' x 4\' (Window)', '6\' x 4\' (Window)', '5mm Clear Glass', '8mm Toughened Glass', '12mm Toughened Glass'],
        units: ['Nos', 'Sets', 'Sqft'],
        attachment: true
    },

    // 4. Flooring, Tiles & Granite/Stone
    tiles: {
        label: 'Tile Type',
        options: ['Vitrified Floor Tiles', 'Ceramic Wall Tiles', 'Anti-skid Bathroom Tiles', 'Parking Paver Tiles', 'Kitchen Glazed Tiles', 'Subway / Decorative Tiles', 'Step & Riser Tiles', 'Interlocking Paver Blocks'],
        specLabel: 'Size / Finish',
        specOptions: ['2x2 ft (600x600mm)', '4x2 ft (1200x600mm)', '2x1 ft (600x300mm)', '1x1 ft (300x300mm)', '800x1600mm', 'Glossy Finish', 'Matt Finish', 'Satin Finish', 'Rustic Finish', 'High-Gloss'],
        units: ['Boxes', 'Sqft', 'Pieces'],
        attachment: true
    },
    granite: {
        label: 'Stone Type',
        options: ['Granite', 'Black Galaxy Granite', 'Jet Black Granite', 'Steel Grey Granite', 'Tan Brown Granite', 'Indian White Marble', 'Italian Marble', 'Kota Stone', 'Kadappa Stone', 'Sandstone'],
        specLabel: 'Thickness / Application',
        specOptions: ['18mm Polished Slab', '20mm Polished Slab', 'Kitchen Platform Slab', 'Staircase Treads & Risers', 'Door / Window Frame Jambs', 'Leather / Flamed Finish'],
        units: ['Sqft', 'Slabs', 'Rft'],
        attachment: true
    },

    // 5. Paint & Finishes
    painting: {
        label: 'Paint / Finish Type',
        options: ['Wall Putty', 'Interior Primer', 'Exterior Primer', 'Interior Acrylic Emulsion', 'Exterior Weatherproof Emulsion', 'Enamel Paint (Oil Based)', 'PU / Wood Polish', 'Waterproof Paint', 'Texture Paint', 'Thinner', 'Paint Brushes', 'Rollers'],
        specLabel: 'Brand / Shade',
        specOptions: ['Asian Paints Royale', 'Asian Paints Apcolite', 'Asian Paints Apex Ultima', 'Asian Paints Tractor Emulsion', 'Berger WeatherCoat', 'Nerolac Beauty', 'Dulux Velvet', 'Birla White Putty', 'JK WallMaxx Putty'],
        units: ['Litres', 'Ltr', 'Kg', 'Pack', 'Buckets', 'Tins'],
        attachment: true
    },

    // 6. Plumbing & Sanitary
    plumber: {
        label: 'Item Category',
        options: ['CPVC Pipes', 'CPVC Fittings (Elbow/Tee/Coupler)', 'UPVC Pipes', 'UPVC Fittings', 'PVC SWR Drainage Pipes', 'HDPE Pipes', 'GI Pipes', 'Ball Valves / Concealed Valves', 'Overhead Water Tanks', 'Wash Basins', 'Toilets (EWC/IWC)', 'Faucets / Taps', 'CP Bath Fittings'],
        specLabel: 'Diameter / Size',
        specOptions: ['1/2" (15mm)', '3/4" (20mm)', '1" (25mm)', '1.25" (32mm)', '1.5" (40mm)', '2" (50mm)', '2.5" (65mm)', '3" (75mm)', '4" (110mm)', '6" (160mm)', '500 Litre Tank', '1000 Litre Tank', '2000 Litre Tank'],
        units: ['Nos', 'Meter', 'Bundles', 'Pieces', 'Sets'],
        attachment: true
    },

    // 7. Electrical & Wiring
    electricalwire: {
        label: 'Item Category',
        options: ['House Wires (FR/FRLS)', 'Armoured Power Cables', 'PVC Conduits & Accessories', 'Modular Switches & Sockets', 'Distribution Boards (DB)', 'Miniature Circuit Breakers (MCB)', 'RCCB / ELCB', 'LED Ceiling Lights', 'Tube Lights', 'Ceiling Fans', 'Exhaust Fans', 'Cable Trays', 'Junction Boxes'],
        specLabel: 'Spec / Gauge / Rating',
        specOptions: ['0.75 sq mm Wire', '1.0 sq mm Wire', '1.5 sq mm Wire', '2.5 sq mm Wire', '4.0 sq mm Wire', '6.0 sq mm Wire', '10 sq mm Wire', '16 sq mm Wire', '6A Modular Switch', '16A Power Socket', '32A DP MCB', '63A 4-Pole MCB'],
        units: ['Meter', 'Nos', 'Roll', 'Coil', 'Pack', 'Boxes', 'Sets'],
        attachment: true
    },

    // 8. Hardware & Fasteners
    hardware: {
        label: 'Item Type',
        options: ['Drywall Screws', 'Wood Screws', 'Self-Tapping Screws', 'Wire Nails', 'Concrete Nails', 'Nuts & Bolts', 'Washers', 'Anchor Fasteners / Rawlplugs', 'Tower Bolts', 'Mortise Locks & Handles', 'Padlocks', 'Hinges (Butt/Concealed)', 'Drawer Slides / Telescopic Channels', 'Brackets & Clamps', 'Metal Fittings'],
        specLabel: 'Size / Spec',
        specOptions: ['1 inch', '1.5 inch', '2 inch', '2.5 inch', '3 inch', '4 inch', '5 inch', '6 inch', '8 inch', '10 inch', '12 inch', 'SS 304 Grade', 'MS Zinc Plated'],
        units: ['Pack', 'Boxes', 'Nos', 'Kg', 'Pairs', 'Pieces'],
        attachment: false
    },

    // 9. Waterproofing, Chemicals & Insulation
    waterproofinginsulation: {
        label: 'Item Type',
        options: ['Integral Liquid Waterproofing (LW+)', '2K Acrylic Polymer Coating', 'Bitumen Sheet / Membrane', 'APP Membrane', 'Bituminous Primer / Coating', 'PVC Waterproofing Sheet', 'Tile Adhesive', 'Epoxy Tile Grout', 'Silicone Sealant', 'PU Sealant', 'Concrete Curing Compound', 'Thermal Insulation', 'Acoustic Insulation'],
        specLabel: 'Brand / Spec',
        specOptions: ['Dr. Fixit 101 LW+', 'Dr. Fixit Fastflex', 'Dr. Fixit Pidifin 2K', 'Fosroc Nitoproof', 'SikaTop Seal 107', 'Roff Tile Adhesive', 'Asian Paints SmartCare Damp Block'],
        units: ['Litres', 'Kg', 'Pack', 'Bags', 'Roll', 'Buckets'],
        attachment: true
    },

    // 10. Roofing Materials
    roofing: {
        label: 'Roofing Type',
        options: ['Colour-Coated Galvalume Sheets', 'GI Corrugated Sheets', 'Polycarbonate Multiwall Sheets', 'Polycarbonate Corrugated Sheets', 'Mangalore Clay Roof Tiles', 'UPVC Roofing Sheets', 'Bitumen Roofing Sheets', 'Fibre Cement Sheets', 'Ridge Caps', 'Gutter & Downpipes'],
        specLabel: 'Thickness / Length',
        specOptions: ['0.35mm', '0.40mm', '0.45mm', '0.50mm', '8 Feet Length', '10 Feet Length', '12 Feet Length', '14 Feet Length', '16 Feet Length', '18 Feet Length', '20 Feet Length'],
        units: ['Rft', 'Meter', 'Sheets', 'Sqft', 'Nos'],
        attachment: true
    },

    // 11. Finishing Materials & False Ceiling
    finishingmaterials: {
        label: 'Item Type',
        options: ['Wall Putty', 'Gypsum Plaster (One Coat)', 'Gypsum Ceiling Boards', 'Cement Fibre Boards', 'GI False Ceiling Perimeter Channel', 'GI Ceiling Section', 'POP (Plaster of Paris)', 'PVC Wall & Ceiling Panels', 'Acoustic Ceiling Tiles', 'Corner Beads & Joint Tape', 'Tile Adhesive', 'Grout'],
        specLabel: 'Brand / Thickness',
        specOptions: ['Saint-Gobain Gyproc', 'USG Boral', 'Armstrong', 'Birla White', '12.5mm Gypsum Board', '9.5mm Gypsum Board', '8x4 ft Sheet', '6x4 ft Sheet'],
        units: ['Bags', 'Sheets', 'Bundles', 'Sqft', 'Nos'],
        attachment: true
    },

    // 12. External, Outdoor & Civil Infrastructure
    externaloutdoor: {
        label: 'Item Type',
        options: ['Interlocking Paver Blocks', 'Kerb Stones', 'Precast Compound Wall Slabs & Posts', 'Chain Link Fencing', 'Barbed Wire', 'RCC Hume Drainage Pipes', 'Manhole Covers & Frames', 'Landscaping Stones', 'Cement Poles', 'Grass Pavers'],
        specLabel: 'Grade / Dimension',
        specOptions: ['60mm Paver (M30)', '80mm Heavy Paver (M40)', '100mm Kerb Stone', '150mm Kerb Stone', '300mm Hume Pipe (NP2)', '450mm Hume Pipe (NP2)', '600mm Hume Pipe (NP3)', 'Heavy Duty SFRC Manhole Cover'],
        units: ['Sqft', 'Nos', 'Rft', 'Meter', 'Pairs'],
        attachment: false
    },

    // 13. Scaffolding & Formwork (Shuttering)
    scaffoldingformwork: {
        label: 'Equipment / Material Type',
        options: ['MS Adjustable Props', 'Cuplock Verticals', 'Cuplock Ledgers', 'Film-Faced Shuttering Plywood', 'MS Shuttering Plates', 'Tie Rods & Wing Nuts', 'Scaffolding Swivel / Fixed Couplers', 'Adjustable Base Jacks', 'U-Head Jacks', 'Scaffolding Pipes (40mm)', 'Steel Scaffolding Spans'],
        specLabel: 'Size / Specification',
        specOptions: ['2m x 3m Adjustable Props', '2m x 3.5m Adjustable Props', '12mm Film-Faced Plywood (8x4 ft)', '900 x 600mm MS Shuttering Plates', '1200 x 600mm MS Shuttering Plates', '2.5m Cuplock Vertical', '1.5m Cuplock Ledger', 'Tie Rod with Water Barrier'],
        units: ['Nos', 'Sheets', 'Sets', 'Sqft', 'Ton'],
        attachment: true
    },

    // 14. Safety & PPE
    safetyppe: {
        label: 'Safety Item',
        options: ['Safety Helmets (ISI Mark)', 'Safety Shoes (Steel Toe)', 'High-Visibility Reflective Jackets', 'Full Body Safety Harness', 'Safety Fall Protection Nets', 'Heavy Duty Cotton / Leather Gloves', 'Rubber Electrical Gloves', 'Safety Protective Goggles', 'Caution / Barricade Tape', 'Fire Extinguishers'],
        specLabel: 'Specification / Rating',
        specOptions: ['ISI Marked HDPE Shell', 'Class A / Class B', 'Steel Toe Cap (Size 7-11)', 'Double Lanyard Safety Harness', '50mm Retroreflective Tape', '4kg ABC Powder Fire Extinguisher'],
        units: ['Nos', 'Pairs', 'Roll', 'Sets'],
        attachment: false
    },

    // 15. Sanitaryware & Bath Fittings
    sanitarybathfittings: {
        label: 'Sanitary Item',
        options: ['European Water Closet (EWC)', 'Indian Water Closet (IWC / Orissa Pan)', 'Wall Hung Toilet with Concealed Cistern', 'Countertop Wash Basin', 'Pedestal Wash Basin', 'Urinals & Sensors', 'Health Faucets', 'Basin Mixers', 'Wall Mixers / Diverters', 'Overhead Showers', 'Bath Spouts', 'Bottle Traps', 'CP Towel Rods & Accessories'],
        specLabel: 'Brand / Model',
        specOptions: ['Jaquar', 'Parryware', 'Hindware', 'Cera', 'Kohler', 'Grohe', 'White Ceramic', 'Chrome Finish'],
        units: ['Nos', 'Sets', 'Pieces'],
        attachment: true
    },

    // 16. Glass & Aluminium Works
    glassaluminium: {
        label: 'Item Type',
        options: ['Toughened Glass', 'Laminated Safety Glass', 'Clear Float Glass', 'Frosted / Tinted Glass', 'Aluminium Partition Sections', 'Aluminium Window Sections', 'Structural Glazing Aluminium Profiles', 'Spider Glazing Fittings', 'Floor Springs & Patch Fittings', 'Silicone Weather Sealants'],
        specLabel: 'Thickness / Profile Spec',
        specOptions: ['5mm Clear Glass', '6mm Toughened Glass', '8mm Toughened Glass', '10mm Toughened Glass', '12mm Toughened Glass', '63.5 x 38.1mm Alu Section (1.5mm)', '100 x 44.5mm Alu Section (2.0mm)', 'Black Powder Coated', 'Anodized Silver'],
        units: ['Sqft', 'Sheets', 'Nos', 'Rft'],
        attachment: true
    },

    // 17. Welding & Fabrication
    welding: {
        label: 'Item Type',
        options: ['Welding Electrodes / Rods', 'MS Rods', 'MS Angles', 'MS Channels', 'MS Flats', 'MS Square Tubes', 'MS Round Pipes', 'Cutting Wheels', 'Grinding Wheels', 'Industrial Gas Cylinders (Oxygen/Acetylene)'],
        specLabel: 'Size / Gauge',
        specOptions: ['8 SWG Electrode (6013)', '10 SWG Electrode (6013)', '12 SWG Electrode (7018)', '25x25x3 mm Angle', '40x40x5 mm Angle', '50x50x6 mm Angle', '75x40 mm Channel', '100x50 mm Channel', '14 inch Cutting Wheel', '4 inch Grinding Wheel'],
        units: ['Kg', 'Pack', 'Nos', 'Ton', 'Pieces'],
        attachment: false
    },

    // 18. Lift / Elevator
    lift: {
        label: 'Lift Type',
        options: ['Passenger Lift (6 Passengers)', 'Passenger Lift (8 Passengers)', 'Goods Lift / Material Hoist', 'Hydraulic Home Lift', 'Capsule Glass Lift', 'Dumbwaiter Lift', 'Lift Spare Parts & Maintenance'],
        specLabel: 'Stops / Capacity',
        specOptions: ['G+1 (2 Stops)', 'G+2 (3 Stops)', 'G+3 (4 Stops)', 'G+4 (5 Stops)', 'G+5 (6 Stops)', '408 Kg Capacity', '544 Kg Capacity', '1000 Kg Goods Capacity'],
        units: ['Nos', 'Sets'],
        attachment: true
    },

    // 19. Transport & Heavy Machinery Rental
    transport: {
        label: 'Vehicle / Machine Type',
        options: ['Tipper / Lorry (6 Wheeler)', 'Tipper / Lorry (10 Wheeler)', 'JCB Excavator (3DX)', 'Hitachi / Poclain Heavy Excavator', 'Bobcat Compact Loader', 'Concrete Boom Pump', 'Tractor with Trolley', 'Hydra Mobile Crane (12T/14T)', 'Vibratory Road Roller', 'Trailer / Flatbed'],
        specLabel: 'Billing Basis / Shift',
        specOptions: ['Per Hour (with Diesel & Driver)', 'Per Day (8 Hours)', 'Per Trip / Load', 'Per Month Contract', 'Demolition & Debris Disposal'],
        units: ['Load', 'Unit', 'Hours', 'Days', 'Trips'],
        attachment: false
    },

    // 20. Interior Works
    interior: {
        label: 'Work Type',
        options: ['Modular Kitchen Cabinets', 'Bedroom Wardrobes', 'TV Unit & Wall Panelling', 'False Ceiling Design', 'Glass Partitions', 'Loose Furniture & Bed Units', 'Shoe Racks & Storage', 'Wall Paper & Texture Finish'],
        specLabel: 'Finish / Material Spec',
        specOptions: ['High-Gloss Acrylic Finish', 'Anti-Fingerprint Laminate', 'PU Polish Finish', 'Natural Wood Veneer', 'Soft-Close Hardware (Hettich/Blum)', 'Toughened Fluted Glass'],
        units: ['Sqft', 'Rft', 'Sets', 'Ls'],
        attachment: true
    },

    // 21. Water & Refreshments
    watercan: {
        label: 'Can Size',
        options: ['20 Litre Can', '25 Litre Can'],
        specLabel: 'Type',
        specOptions: ['Commercial RO Purified Water', 'Packaged Drinking Water'],
        units: ['Nos', 'Pack', 'Units'],
        attachment: false
    },
    lorrywater: {
        label: 'Tanker Size',
        options: ['6,000 Litre Tanker', '8,000 Litre Tanker', '12,000 Litre Tanker', '18,000 Litre Tanker'],
        specLabel: 'Water Purpose',
        specOptions: ['Construction Curing & Concrete Mixing Water', 'Ground Raw Water', 'Drinking / Kitchen Supply'],
        units: ['Load', 'Litres', 'Unit'],
        attachment: false
    },
    tea: {
        label: 'Refreshment Type',
        options: ['Morning Tea / Coffee', 'Afternoon Tea / Coffee', 'Snacks & Biscuits', 'Drinking Water Supply'],
        specLabel: 'Supply Schedule',
        specOptions: ['Daily Site Supply', 'Weekly Supply', 'Overtime Snacks'],
        units: ['Cups', 'Nos', 'Pack', 'Days'],
        attachment: false
    }
};

function syncItemsField() {
    let catVal = $('#category_name').val() || '';
    let specVal = $('#spec').val() || '';
    let baseType = "{{ ucfirst($materialType) }}";

    let combined = baseType;
    if (catVal) {
        combined += ' - ' + catVal;
    }
    if (specVal) {
        combined += ' (' + specVal + ')';
    }
    $('#items').val(combined);
}

function loadUnitField(materialType) {
    const allUnits = [
        @foreach ($sharedUnits as $unitOption)
            "{{ $unitOption }}",
        @endforeach
    ];

    const config = materialCategoryConfig[materialType];
    let categoryHtml = '';
    let specHtml = '';
    let unitHtml = '';
    let attachmentHtml = '';

    if (config) {
        // 1. Primary Category / Item dropdown
        let optionsHtml = config.options.map(o => `<option value="${o}">${o}</option>`).join('');
        categoryHtml = `
        <div class="row mb-3 align-items-center">
            <label for="category_name" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">${config.label}</label>
            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                <select class="form-select" name="category_name" id="category_name" onchange="syncItemsField()">
                    <option value="">Select ${config.label}</option>
                    ${optionsHtml}
                </select>
            </div>
        </div>`;

        // 2. Secondary Spec / Brand / Grade / Size input with datalist
        if (config.specLabel) {
            let specDatalistOptions = (config.specOptions || []).map(s => `<option value="${s}"></option>`).join('');
            specHtml = `
            <div class="row mb-3 align-items-center">
                <label for="spec" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">${config.specLabel}</label>
                <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                    <input type="text" class="form-control" name="spec" id="spec" list="specList"
                        placeholder="Select or enter ${config.specLabel}" autocomplete="off" oninput="syncItemsField()">
                    <datalist id="specList">
                        ${specDatalistOptions}
                    </datalist>
                </div>
            </div>`;
        }

        // 3. Filtered Unit dropdown: Recommended units on top, followed by other units
        let recommendedUnits = config.units || [];
        let recommendedOptionsHtml = '';
        let otherOptionsHtml = '';

        recommendedUnits.forEach((u, idx) => {
            let selected = (idx === 0) ? 'selected' : '';
            recommendedOptionsHtml += `<option value="${u}" ${selected}>${u}</option>`;
        });

        allUnits.forEach(u => {
            if (!recommendedUnits.includes(u)) {
                otherOptionsHtml += `<option value="${u}">${u}</option>`;
            }
        });

        unitHtml = `
        <div class="row mb-3 align-items-center">
            <label for="unit" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Unit</label>
            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                <select class="form-select" name="unit" id="unit">
                    <option value="">Select Unit</option>
                    ${recommendedUnits.length > 0 ? `<optgroup label="Recommended Units">${recommendedOptionsHtml}</optgroup>` : ''}
                    <optgroup label="All Units">${otherOptionsHtml}</optgroup>
                </select>
            </div>
        </div>`;

        // 4. File Attachment
        if (config.attachment) {
            attachmentHtml = `
            <div class="row mb-3 align-items-center">
                <label for="attachment" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">File Attachment / Drawing</label>
                <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                    <input type="file" name="attachment" id="attachment" class="form-control" accept="image/*,.pdf">
                    <div class="form-text text-muted">Upload drawing, specifications, or sample image (optional)</div>
                </div>
            </div>`;
        }
    } else {
        // Fallback for custom unmapped materials
        let plainOptionsHtml = allUnits.map(u => `<option value="${u}">${u}</option>`).join('');
        unitHtml = `
        <div class="row mb-3 align-items-center">
            <label for="unit" class="col-sm-4 col-md-3 col-lg-2 col-form-label fw-bold text-sm-end">Unit</label>
            <div class="col-sm-8 col-md-8 col-lg-6 form-input-wrap">
                <select class="form-select" name="unit" id="unit">
                    <option value="">Select Unit</option>
                    ${plainOptionsHtml}
                </select>
            </div>
        </div>`;
    }

    $("#dynamic-fields").html(categoryHtml + specHtml + unitHtml + attachmentHtml);
}

</script>

<style>
    .form-input-wrap {
        max-width: 540px;
    }
</style>
@endsection
