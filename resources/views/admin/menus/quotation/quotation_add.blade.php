@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10">
            <h3 class="text-center pb-4 mt-3">Generate Quotation</h3>
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

                <form id="quotationForm" action="{{ route('quotation.add') }}" method="POST" class="container">
                    @csrf

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Name</div>
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="Customer Name">
                            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Mobile No</div>
                        <div class="col-md-4">
                            <input type="text" name="mobile_no" class="form-control" placeholder="Mobile Number">
                            @error('mobile_no') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Email</div>
                        <div class="col-md-4">
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email Address">
                            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Date</div>
                        <div class="col-md-4">
                            <input type="date" name="date" class="form-control">
                            @error('date') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Subject</div>
                        <div class="col-md-6">
                            <input type="text" name="subject" class="form-control" placeholder="Subject of Quotation">
                            @error('subject') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                     <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Location</div>
                        <div class="col-md-6">
                            <input type="text" name="location" class="form-control" placeholder="Please Enter Location">
                            @error('location') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                     <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Contractor:</div>
                        <div class="col-md-6">
                            <input type="text" name="contractor" class="form-control" placeholder="Please Enter Contractor">
                            @error('contractor') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="mt-5 mb-3">

                    <h5 class="mb-3">Particulars</h5>

                    <div id="particularRows">
                        @php
                            $defaults = [
                                ['Car Park 8 Ft Roof Height', 1700, 2406, 'sqft'],
                                ['1st Floor', 2050, 2406, 'sqft'],
                                ['2nd Floor', 2050, 2406, 'sqft'],
                                ['3rd Floor', 2050, 2406, 'sqft'],
                                ['Head Room 8 ft/ Lift Room', 1850, 450, 'sqft'],
                                ['Elevation Work', 200000, 1, 'Nos'],
                                ['Sump R C C', 23, 12000, 'Cft'],
                                ['Specktic Tank', 18, 12000, 'Cft'],
                                ['Water Tank R C C', 23, 6000, 'Cft'],
                                ['Water Tank staircase Grill', 15000, 1, 'Nos'],
                                ['E.B. DB Panel', 15000, 10, 'Nos'],
                                ['Weathering Tiles', 160, 2406, 'sqft'],
                                ['Safety Gate', 135000, 1, 'Nos'],
                                ['Lift 6 Passenger', 750000, 1, 'Nos'],
                                ['Compound Gate', 80000, 2, 'Nos'],
                                ['Compound Wall 8 ft', 1800, 209, 'Rft'],
                            ];
                        @endphp

                        @foreach ($defaults as $item)
                            <div class="row mb-2 particular-row">
                                <div class="col-md-2">
                                    <input type="text" name="particular[]" class="form-control" value="{{ $item[0] }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="any" name="rate[]" class="form-control rate" value="{{ $item[1] }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="unit[]" class="form-control unit">
                                        @foreach ($sharedUnits as $unitOption)
                                            <option value="{{ $unitOption }}" {{ $item[3] == $unitOption ? 'selected' : '' }}>{{ $unitOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="any" name="sqFt[]" class="form-control sqft" value="{{ $item[2] }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="any" name="total_cost[]" class="form-control total_cost" value="{{ $item[1] * $item[2] }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-row">X</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-secondary my-3" id="addRow">+ Add Row</button>

                    <div class="row justify-content-end">
                        <div class="col-md-6 text-end">
                            <h5 class="text-primary">Total: ₹ <span id="grandTotal" class="text-dark">0.00</span></h5>
                        </div>
                    </div>

                    <div class="row align-items-center mt-4 g-3 quotation-action-row">
                        <div class="col-md-4 text-start">
                            <button type="button" class="btn btn-primary quotation-action-btn" id="whatsappButton">Send WhatsApp</button>
                        </div>
                        <div class="col-md-4 text-center">
                            <button type="button" class="btn btn-success quotation-action-btn" id="sendMailButton">Send Mail</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-outline-primary quotation-action-btn" id="downloadPdfButton">Download PDF Quotation</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let cachedQuotationLinks = null;
let quotationDirty = true;

document.addEventListener('DOMContentLoaded', function () {
    const successAlert = document.querySelector(".alert-success");
    if (successAlert) {
        setTimeout(() => {
            successAlert.classList.remove("show");
            successAlert.classList.add("fade");
        }, 300);
    }

    updateGrandTotal();
});

$(document).ready(function () {
    function markQuotationDirty() {
        quotationDirty = true;
        cachedQuotationLinks = null;
    }

    function triggerPdfDownload(pdfUrl) {
        const downloadLink = document.createElement('a');
        downloadLink.href = pdfUrl;
        downloadLink.setAttribute('download', '');
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
    }

    function runQuotationAction(action, response) {
        if (action === 'whatsapp' && response.whatsapp_url) {
            window.open(response.whatsapp_url, '_blank');
        }

        if (action === 'download' && response.pdf_url) {
            triggerPdfDownload(response.pdf_url);
        }

        if (action === 'mail' && response.mail_sent) {
            alert('Quotation emailed successfully.');
        }
    }

    function submitQuotation(action) {
        if (action === 'mail' && !$('#email').val().trim()) {
            alert('Please enter an email address before sending the mail.');
            return;
        }

        if (action !== 'mail' && !quotationDirty && cachedQuotationLinks) {
            runQuotationAction(action, cachedQuotationLinks);
            return;
        }

        $('#loadingSpinner').removeClass('d-none');

        const form = $('#quotationForm');
        const formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            success: function (response) {
                $('#loadingSpinner').addClass('d-none');

                if (response.status === 'success') {
                    cachedQuotationLinks = response;
                    quotationDirty = false;
                    runQuotationAction(action, response);
                }
            },
            error: function (xhr) {
                $('#loadingSpinner').addClass('d-none');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let message = Object.values(errors).map(e => e[0]).join("\n");
                    alert("Validation Errors:\n" + message);
                } else {
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : "Something went wrong!";
                    alert("Error: " + errorMsg);
                    console.error(xhr);
                }
            }
        });
    }

    $(document).on('input', '.rate, .sqft', function () {
        const row = $(this).closest('.particular-row');
        const rate = parseFloat(row.find('.rate').val()) || 0;
        const sqft = parseFloat(row.find('.sqft').val()) || 0;
        const total = rate * sqft;
        row.find('.total_cost').val(total.toFixed(2));
        updateGrandTotal();
        markQuotationDirty();
    });

    $(document).on('input change', '#quotationForm input, #quotationForm select, #quotationForm textarea', function () {
        markQuotationDirty();
    });

    $('#addRow').on('click', function () {
        const row = `
<div class="row mb-2 particular-row">
    <div class="col-md-2">
        <input type="text" name="particular[]" class="form-control" placeholder="Particular">
    </div>
    <div class="col-md-2">
        <input type="number" step="any" name="rate[]" class="form-control rate" placeholder="Rate">
    </div>
    <div class="col-md-2">
        <select name="unit[]" class="form-control unit">
            @foreach ($sharedUnits as $unitOption)
                <option value="{{ $unitOption }}">{{ $unitOption }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <input type="number" step="any" name="sqFt[]" class="form-control sqft" placeholder="">
    </div>
    <div class="col-md-2">
        <input type="number" step="any" name="total_cost[]" class="form-control total_cost" placeholder="Total Cost" readonly>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-danger remove-row">X</button>
    </div>
</div>`;
        $('#particularRows').append(row);
        updateGrandTotal();
        markQuotationDirty();
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('.particular-row').remove();
        updateGrandTotal();
        markQuotationDirty();
    });

    $('#whatsappButton').on('click', function () {
        submitQuotation('whatsapp');
    });

    $('#downloadPdfButton').on('click', function () {
        submitQuotation('download');
    });

    $('#sendMailButton').on('click', function () {
        submitQuotation('mail');
    });

    $('#quotationForm').on('submit', function (e) {
        e.preventDefault();
        submitQuotation('whatsapp');
    });
});

function updateGrandTotal() {
    let total = 0;
    $('.total_cost').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#grandTotal').text(total.toFixed(2));
}
</script>

<style>
    .quotation-action-row {
        margin-bottom: 6px;
    }

    .quotation-action-btn {
        min-width: 240px;
    }

    @media (max-width: 767.98px) {
        .quotation-action-row .text-start,
        .quotation-action-row .text-end {
            text-align: center !important;
        }

        .quotation-action-btn {
            width: 100%;
        }
    }
</style>

@endsection
