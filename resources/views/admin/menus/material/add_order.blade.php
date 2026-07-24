@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-10 d-flex justify-content-center">
                <h3 class="pb-4 mt-3 mb-0">Inward {{ ucfirst($materialType) }} Order</h3>
            </div>
            
        </div>
        <div class="row">
            <div class="col-lg-11">
                <div class="card shadow-lg p-4 ms-4">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }}
                    @endif

                    <form id="requestForm" action="{{ route('add.order') }}" method="POST" enctype="multipart/form-data" class="container">
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

                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                        @endphp
                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="date" class="fw-bold">Date</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="date" class="form-control" name="date">
                                </div>
                                @error('date')
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

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="gst">GST</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                        <input id="gst" name="gst" type="number" class="form-control no-arrow"
                                            min="0" step="0.01" placeholder="Enter GST amount" />
                                </div>
                                @error('gst')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="price">Total Price</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                        <input id="price" name="price" type="number" class="form-control no-arrow"
                                            min="0" step="0.01" placeholder="Enter total price" />
                                </div>
                                @error('price')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mt-5">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="attachment">Upload Image</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="file" name="attachment" id="attachment" class="form-control" accept="image/*,.pdf">
                                </div>
                                @error('attachment')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary w-100">Inward Order</button>
                                </div>
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

            $(document).on('click', '.vendor-option', function(e) {
                e.preventDefault();
                $('#vendor_name').val($(this).data('name'));
                $('#vendor_mobile').val($(this).data('mobile'));
                $('#vendor_id').val($(this).data('id'));
                $('#vendor_address').val($(this).data('address'));
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

        function loadUnitField(materialType) {
            let html = '';
            let unitOptions = '<option value="">Select Unit</option>' +
                `@foreach ($sharedUnits as $unitOption)<option value="{{ $unitOption }}">{{ $unitOption }}</option>@endforeach`;

            switch (materialType) {
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
                    <label class="fw-bold">Inch</label>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
                    break;

                case 'steel':
                    html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
                    break;

                case 'electricalwire':
                case 'plumber':
                case 'painting':
                    html = '';
                    break;

                case 'rmc':
                    html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
                    break;

                case 'aggregate':
                    html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
                    break;

                case 'cement':
                    html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
                    break;

                case 'others':
                    html = `
            <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
                    break;

                default:
                    html = ` <div class="row align-items-center mt-5">
                <div class="col-lg-2"><label class="fw-bold">Unit</label></div>
                <div class="col-md-4">
                    <select class="form-select" name="unit" id="unit">${unitOptions}</select>
                </div>
            </div>`;
            }

            $("#dynamic-fields").html(html);
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
