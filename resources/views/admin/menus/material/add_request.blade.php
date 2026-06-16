@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Add {{ ucfirst($materialType) }} Request</h3>
            </div>
            
        </div>
        <div class="row">
            <div class="col-lg-11">
                <div class="card shadow-lg p-4 ms-4">

                    <!-- Blade alert for success -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif

                    <form id="requestForm" action="{{ route('add.request') }}" method="POST" class="container" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="site_id" value="{{ $siteId }}">

                        <input type="hidden" name="vendor_id" id="vendor_id">

                        <input type="hidden" name="material_type" value="{{ ucfirst($materialType) }}">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="vendor_name" class="fw-bold">Vendor Name</label>
                                </div>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <div class="form-group">
                                    <input type="text" id="vendor_name" name="vendor_name" class="form-control"
                                        placeholder="Type Vendor Name..." autocomplete="off">
                                    <div id="vendor_suggestions" class="list-group position-absolute w-100"
                                        style="z-index: 1000; display: none;"></div>
                                </div>
                                @error('vendor_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="vendor_mobile" class="fw-bold">Requestor Mobile No</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" id="vendor_mobile" name="vendor_mobile" class="form-control"
                                        placeholder="Mobile Number">
                                </div>
                                @error('vendor_mobile')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="vendor_address" class="fw-bold">Requestor Address</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <textarea id="vendor_address" name="vendor_address" class="form-control" rows="2" placeholder="Vendor Address"></textarea>
                                </div>
                                @error('vendor_address')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


<div class="row align-items-center mt-5">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="material_type" class="fw-bold">Items</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <input type="text" class="form-control" name="items"
                    placeholder="Enter material type" value="{{ ucfirst($materialType) }}">
            </div>
            @error('items')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>


                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="quantity" class="fw-bold">Quantity</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="quantity" placeholder="Enter quantity">
                                </div>
                                @error('quantity')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                      
                   <div id="dynamic-fields"></div>

                        
                            <!-- <div class="col-md-4">
                                <div class="form-group">
                                   
                                    <select class="form-select form-control" name="delivery_needed_by"
                                        id="delivery_needed_by">
                                        <option value="">Select Delivery Needed By</option>
                                        <option value="Immediate">Immediate</option>
                                        <option value="Later">Later</option>
                                        <option value="One week">One week</option>
                                    </select>
                                </div>
                                @error('delivery_needed_by')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div> -->
                         <div class="row align-items-cente\r mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="delivery_needed_by" class="fw-bold">date of delivery</label>
                                </div>
                            </div>
                         <div class="col-md-4">
                                <div class="form-group">
                                    <input type="date" class="form-control" name="date_of_delivery"
                    value="" >
                                    
                                </div>
                                @error('date_of_delivery')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <!--<div class="row align-items-center mt-4">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="amount" class="fw-bold">Amount</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="number" class="form-control no-arrow" min="0" step="0.01" name="amount" id="amount"
                                        placeholder="Enter amount">
                                </div>
                                @error('amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>-->
                       <div class="row align-items-center mt-4">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="supervisor_name" class="fw-bold">Supervisor Name</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <input type="text" class="form-control" name="supervisor_name" id="supervisor_name"
                placeholder="Enter supervisor name" 
                value="{{ old('supervisor_name', $supervisor->name ?? '') }}">
        </div>
        @error('supervisor_name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row align-items-center mt-4">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="supervisor_phone" class="fw-bold">Supervisor Phone No</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <input type="text" class="form-control" name="supervisor_phone" id="supervisor_phone"
                placeholder="Enter phone number"
                value="{{ old('supervisor_phone', $supervisor->mobile_no ?? '') }}">
                
        </div>
        @error('supervisor_phone')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row align-items-center mt-4">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="supervisor_phone" class="fw-bold">Price</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <input type="text" class="form-control" name="price" id="supervisor_phone"
                placeholder="Enter Price"
                value="">
                
        </div>
        @error('price')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

 <!-- <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="remarks" class="fw-bold">Remarks</label>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter remarks here..."></textarea>
                                </div>
                                @error('remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group text-center">
                            @if (session('pdf_download_link'))
                                <p>Click the link below to download the PDF:</p>
                                <a href="{{ session('pdf_download_link') }}" target="_blank"
                                    class="btn btn-primary">Download PDF</a>
                            @endif
                        </div> -->
                        <div class="row justify-content-center mt-4">
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary w-100">Send Request to Vendor WhatsApp
                                         <i class="fab fa-whatsapp me-1"></i>  </button>
                                </div>
                            </div>
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

function loadUnitField(materialType) {
    let html = '';

    switch (materialType) {

        // 🧱 Bricks
        case 'bricks':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2">
                    <label class="fw-bold">Category Name</label>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category_name" id="category_name">
                        <option value="">Select Category</option>
                        <option value="Red Brick">Red Brick</option>
                        <option value="Fly Ash Bricks">Fly Ash Bricks</option>
                        <option value="AAC block">AAC Block</option>
                        <option value="Solid block">Solid Block</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="fw-bold">Unit</label>
                </div>
                <div class="col-md-4">
                     <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="Units">Units</option>
                        <option value="Kg">Kg</option>
                         <option value="Ton">Ton</option>
                         <option value="Size">Size</option>
                        <option value="Inch">Inch</option>
                        <option value="Dia">Dia</option>
                         <option value="no">no</option>
                    </select>
                </div>
            </div>`;
            break;

        // 🧰 Steel
        case 'steel':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="Units">Units</option>
                        <option value="Kg">Kg</option>
                         <option value="Ton">Ton</option>
                         <option value="Size">Size</option>
                        <option value="Inch">Inch</option>
                        <option value="Dia">Dia</option>
                    </select>
                </div>
            </div>`;
            break;

        // ⚡ Electrical Wire, 🚰 Plumber, 🎨 Painting — file upload + optional unit
        case 'electricalwire':
        case 'plumber':
        case 'painting':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2">
                    <label class="fw-bold">File Attachment</label>
                </div>
                <div class="col-md-4">
                    <input type="file" name="attachment" id="attachment" class="form-control">
                </div>
              
            </div>`;
            break;

               case 'cement':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="In Bags">In Bags</option>
                       
                    </select>
                </div>
            </div>`;
            break;

        // 🧱 RMC
        case 'rmc':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="M Cube">M Cube</option>
                    </select>
                </div>
            </div>`;
            break;

        // 🪨 Aggregate
        case 'aggregate':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="CFT">CFT</option>
                        <option value="Tons">Tons</option>
                        <option value="20mm">20mm</option>
                        <option value="40mm">40mm</option>
                        <option value="m sand">m sand</option>
                        <option value="p sand">p sand</option>

                    </select>
                </div>
            </div>`;
            break;

        // ☕ Others
        case 'others':
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="Load">Load</option>
                        <option value="Pack">Pack</option>
                        <option value="Ltr">Ltr</option>
                        <option value="Kg">Kg</option>
                        <option value="Pieces">Pieces</option>
                        <option value="M Cube">M Cube</option>
                        <option value="CFT">CFT</option>
                        <option value="Unit">Unit</option>
                        <option value="Bag">Bag</option>
                        <option value="Tons">Tons</option>
                        <option value="Numbers">Numbers</option>
                    </select>
                </div>
            </div>`;
            break;

        default:
            html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">
                        <option value="">Select Unit</option>
                        <option value="Load">Load</option>
                        <option value="Pack">Pack</option>
                        <option value="Ltr">Ltr</option>
                        <option value="Kg">Kg</option>
                        <option value="Pieces">Pieces</option>
                        <option value="M Cube">M Cube</option>
                        <option value="CFT">CFT</option>
                        <option value="Unit">Unit</option>
                        <option value="Bag">Bag</option>
                        <option value="Tons">Tons</option>
                        <option value="Numbers">Numbers</option>
                    </select>
                </div>
            </div>`;
    }

    $("#dynamic-fields").html(html);
}

</script>
@endsection
